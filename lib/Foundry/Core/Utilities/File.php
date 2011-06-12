<?php
/**
 * Utilities for handling files.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Utilities
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
 
namespace Foundry\Core\Utilities;

class File {
    /**
     * Check if a file exists in the php include path.
     *
     * @param string $filename
     * 
     * @return boolean
     * 
     * @link http://php.net/manual/en/function.file-exists.php#92619 Based on this comment in the PHP manual.
     */
    static function file_exists($filename) {
        if (@file_exists($filename)) return true;
        $include_path = "";
        if (function_exists("get_include_path")) {
            $include_path = get_include_path();
        } else if (($ip = ini_get("include_path")) !== false) {
            $include_path = $ip;
        }

        if (empty($include_path)) {
            return false;
        } else if (strpos($include_path, PATH_SEPARATOR) !== false) {
            $paths = explode(PATH_SEPARATOR, $include_path);
            foreach ($paths as $path) {
                if (!empty($path) && @file_exists("$path/$filename") !== false) {
                    return true;
                }
            }
            return false;
        } else {
            return @file_exists("$include_path/$filename") !== false;
        }
    } 
}

?>
