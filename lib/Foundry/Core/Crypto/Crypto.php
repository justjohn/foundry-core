<?php
/**
 * A Cryptography API used for encrypting, decrypting and hashing data.
 * 
 * Currently, the Crypt API has support for all the algorithms supported by the
 * PHP mcrypt extension.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Crypto
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Crypto;

use Foundry\Core\Core;
use Foundry\Core\Model;
use Foundry\Core\Service;

/**
 * Cryptography API.
 * 
 * This class is a container for the {@see Encryption} class that wraps it with
 * the correct syntax for use as a Core component.
 * 
 * It also provides methods for securly salting and hashing passwords using
 * multiple rounds of bcrypt.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Crypto
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Crypto {
    /**
     * The options required to instantiate a cryptography component.
     * @var array
     */
    public static $required_options = array(
        // The encryption key.
        "key",
        
        // The cipher to use.
        //  Should be one of MCRYPT_*
        "cipher",
        
        // The cipher mode.
        //  Should be one of MCRYPT_MODE_*
        "mode",
        
        // Number of rounds to use with the Bcrypt cipher; an increase of 1 here
        //  represents a doubling of the time to hash passwords and is backwards
        //  compatible.
        "hash_rounds" 
    );
    
    /**
     * The encrpytion key.
     * @var string
     */
    private $key;
    /**
     * The encryption class.
     * @var Encryption
     */
    private $encryption;
    /**
     * The number of rounds for bcrypt hashing.
     * @var int
     */
    private $hash_rounds;
    /**
     * The bcrypt class.
     * @var Bcrypt
     */
    private $bcrypt;
    
    function __construct(array $configuration) {
        Service::validate($configuration, self::$required_options);
        $this->key = $configuration["key"];
        $this->hash_rounds = $configuration["hash_rounds"];
        
        $this->bcrypt = new Bcrypt($this->hash_rounds);
        $this->encryption = new Encryption($configuration["cipher"], $configuration["mode"]);
    }
    
    /**
     * Encrypt text.
     * 
     * @param string $plaintext
     * 
     * @return string
     */
    public function encrypt($plaintext) {
        return $this->encryption->encrypt($plaintext, $this->key);
    }
    
    /**
     * Decrypt text.
     * 
     * @param string $ciphertext
     * 
     * @return string
     */
    public function decrypt($ciphertext) {
        return $this->encryption->decrypt($ciphertext, $this->key);
    }
    
    /**
     * Hash text using bcrypt.
     * 
     * @param string $text
     * 
     * @return string
     */
    public function hash($text) {
        return $this->bcrypt->hash($text);
    }
    
    /**
     * Verify a hash.
     *
     * @param type $text The text to verify.
     * @param type $hash The hash to verify.
     * 
     * @return boolean
     */
    public function verify($text, $hash) {
        return $this->bcrypt->verify($text, $hash);
    }
}

/**
 * A class to handle secure encryption and decryption of arbitrary data
 *
 * Note that this is not just straight encryption.  It also has a few other
 *  features in it to make the encrypted data far more secure.  Note that any
 *  other implementations used to decrypt data will have to do the same exact
 *  operations.  
 *
 * Security Benefits:
 *
 * - Uses Key stretching
 * - Hides the Initialization Vector
 * - Does HMAC verification of source data
 * 
 ****************
 * This class comes from a answer on StackOverflow and is available under a 
 * Creative Commons Attribution-ShareAlike 3.0 Unported license.
 * 
 * @link http://stackoverflow.com/questions/5089841/php-2-way-encryption-i-need-to-store-passwords-that-can-be-retrieved
 * @author ircmaxell http://stackoverflow.com/users/338665/ircmaxell
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-ShareAlike 3.0 Unported
 * 
 */
class Encryption {

    /**
     * @var string $cipher The mcrypt cipher to use for this instance
     */
    protected $cipher = '';

    /**
     * @var int $mode The mcrypt cipher mode to use
     */
    protected $mode = '';

    /**
     * Constructor!
     *
     * @param string $cipher The MCRYPT_* cypher to use for this instance
     * @param int    $mode   The MCRYPT_MODE_* mode to use for this instance
     */
    public function __construct($cipher, $mode) {
        $this->cipher = $cipher;
        $this->mode = $mode;
    }

    /**
     * Decrypt the data with the provided key
     *
     * @param string $data The encrypted datat to decrypt
     * @param string $key  The key to use for decryption
     * 
     * @returns string|false The returned string if decryption is successful
     *                           false if it is not
     */
    public function decrypt($data, $key) {
        $key = $this->stretch($key);
        $iv = $this->getIv($data, $key);
        if ($iv === false) {
            return false; //Invalid IV, so we can't continue
        }
        $de = mcrypt_decrypt($this->cipher, $key, $data, $this->mode, $iv);
        if (!$de || strpos($de, ':') === false) return false;

        list ($hmac, $data) = explode(':', $de, 2);
        $data = rtrim($data, "\0");

        if ($hmac != hash_hmac('sha1', $data, $key)) {
            return false;
        }
        return $data;
    }

    /**
     * Encrypt the supplied data using the supplied key
     * 
     * @param string $data The data to encrypt
     * @param string $key  The key to encrypt with
     *
     * @returns string The encrypted data
     */
    public function encrypt($data, $key) {
        $key = $this->stretch($key);
        $data = hash_hmac('sha1', $data, $key) . ':' . $data;

        $iv = $this->generateIv();
        $enc = mcrypt_encrypt($this->cipher, $key, $data, $this->mode, $iv);

        return $this->storeIv($enc, $iv, $key);
    }

    /**
     * Generate an Initialization Vector based upon the class's cypher and mode
     *
     * @returns string The initialization vector
     */
    protected function generateIv() {
        $size = mcrypt_get_iv_size($this->cipher, $this->mode);
        return mcrypt_create_iv($size, MCRYPT_RAND);
    }

    /**
     * Extract a stored initialization vector from an encrypted string
     *
     * This will shorten the $data pramater by the removed vector length.
     * 
     * @see Encryption::storeIv()
     *
     * @param string &$data The encrypted string to process.
     * @param string $key   The supplied key to extract the IV with
     *
     * @returns string The initialization vector that was stored
     */
    protected function getIv(&$data, $key) {
        $size = mcrypt_get_iv_size($this->cipher, $this->mode);
        $iv = '';
        for ($i = $size - 1; $i >= 0; $i--) {
            $pos = hexdec($key[$i]);
            $iv = substr($data, $pos, 1) . $iv;
            $data = substr_replace($data, '', $pos, 1);
        }
        if (strlen($iv) != $size) {
            return false;
        }
        return $iv;
    }

    /**
     * Store the Initialization Vector inside the encrypted string.
     *
     * We will need the IV later to decrypt the data, so we need to
     * make it available.  We don't want to just append it, since that
     * could open MITM style attacks on the data.  So we'll hide it 
     * using the key to determine exactly how to hide it.  That way,
     * without knowing the key, it should be impossible to get the IV.
     *
     * @param string $data The data to hide the IV within
     * @param string $iv   The IV to hide
     * @param string $key  The key to use to hide the IV with
     *
     * @returns string The $data parameter with the hidden IV
     */
    protected function storeIv($data, $iv, $key) {
        for ($i = 0; $i < strlen($iv); $i++) {
            $offset = hexdec($key[$i]);
            $data = substr_replace($data, $iv[$i], $offset, 0);
        }
        return $data;
    }

    /**
     * Stretch the key using a simple hmac based stretching algorythm
     *
     * We want to use sha1 here over something stronger since Blowfish
     * expects a key between 4 and 56 bytes.  Sha1 produces a 40 byte
     * hash, so it should be good for these purposes.  This also allows
     * an arbitrary key of any length to be used for encryption.
     *
     * Another benefit of streching the kye is that it actually slows
     * down any potential brute force attacks. 
     *
     * We use 5000 runs for the stretching since it's a good balance
     * between brute force protection and system load.  We could increase
     * this if we were paranoid, but it shouldn't be necessary.
     *
     * @see http://en.wikipedia.org/wiki/Key_stretching
     *
     * @param string $key The key to stretch
     *
     * @returns string A 40 character hex string with the stretched key
     */
    protected function stretch($key) {
        $hash = sha1($key);
        $runs = 0;
        do {
            $hash = hash_hmac('sha1', $hash, $key);
        } while ($runs++ < 5000);
        return $hash;
    }

}


/**
 * A class to handle password hashing using Bcrypt.
 * 
 ****************
 * This class comes from a answer on StackOverflow and is available under a 
 * Creative Commons Attribution-ShareAlike 3.0 Unported license.
 * 
 * @link http://stackoverflow.com/questions/4795385/how-do-you-use-bcrypt-for-hashing-passwords-in-php
 * @author Andrew Moore http://stackoverflow.com/users/26210/andrew-moore
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-ShareAlike 3.0 Unported
 * 
 */
class Bcrypt {
  private $rounds;
  public function __construct($rounds = 12) {
    if(CRYPT_BLOWFISH != 1) {
      throw new Exception("bcrypt not supported in this installation. See http://php.net/crypt");
    }

    $this->rounds = $rounds;
  }

  public function hash($input) {
    $hash = crypt($input, $this->getSalt());

    if(strlen($hash) > 13)
      return $hash;

    return false;
  }

  public function verify($input, $existingHash) {
    $hash = crypt($input, $existingHash);

    return $hash === $existingHash;
  }

  private function getSalt() {
    $salt = sprintf('$2a$%02d$', $this->rounds);

    $bytes = $this->getRandomBytes(16);

    $salt .= $this->encodeBytes($bytes);

    return $salt;
  }

  private $randomState;
  private function getRandomBytes($count) {
    $bytes = '';

    if(function_exists('openssl_random_pseudo_bytes') &&
        (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL slow on Win
      $bytes = openssl_random_pseudo_bytes($count);
    }

    if($bytes === '' && is_readable('/dev/urandom') &&
       ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
      $bytes = fread($hRand, $count);
      fclose($hRand);
    }

    if(strlen($bytes) < $count) {
      $bytes = '';

      if($this->randomState === null) {
        $this->randomState = microtime();
        if(function_exists('getmypid')) {
          $this->randomState .= getmypid();
        }
      }

      for($i = 0; $i < $count; $i += 16) {
        $this->randomState = md5(microtime() . $this->randomState);

        if (PHP_VERSION >= '5') {
          $bytes .= md5($this->randomState, true);
        } else {
          $bytes .= pack('H*', md5($this->randomState));
        }
      }

      $bytes = substr($bytes, 0, $count);
    }

    return $bytes;
  }

  private function encodeBytes($input) {
    // The following is code from the PHP Password Hashing Framework
    $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $output = '';
    $i = 0;
    do {
      $c1 = ord($input[$i++]);
      $output .= $itoa64[$c1 >> 2];
      $c1 = ($c1 & 0x03) << 4;
      if ($i >= 16) {
        $output .= $itoa64[$c1];
        break;
      }

      $c2 = ord($input[$i++]);
      $c1 |= $c2 >> 4;
      $output .= $itoa64[$c1];
      $c1 = ($c2 & 0x0f) << 2;

      $c2 = ord($input[$i++]);
      $c1 |= $c2 >> 6;
      $output .= $itoa64[$c1];
      $output .= $itoa64[$c2 & 0x3f];
    } while (1);

    return $output;
  }
}

?>
