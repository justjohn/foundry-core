<?php
namespace foundry\core\email;
use \foundry\core\Core as Core;

Core::requires('\foundry\core\logging\Log');

use \foundry\core\logging\Log as Log;

/**
 * Send emails.
 * @package modules
 */

// Include Pear Mail functions
require_once "Mail.php";
require_once 'Mail/mime.php';

/**
 * Send emails.
 * @package modules
 */
class Email {
    /**
     * The smtp connection.
     * @var Mail
     */
    private $smtp;
    /**
     * The email address notifications come from.
     * @var string $options
     */
    private $email;

    public function  __construct() {
        $options = Core::getConfig('\foundry\core\email\Email');
        $this->email = $options['email'];
        $this->prefix = $options['prefix'];

        $host = $options['host'];
        $port = $options['port'];
        $username = $options['username'];
        $password = $options['password'];

        $mail_options = array('host' => $host, 'port' => $port);
        if ($username != '') {
            $mail_options['auth'] = true;
            $mail_options['username'] = $username;
            $mail_options['password'] = $password;
        } else {
            $mail_options['auth'] = false;
        }

        $this->smtp = \Mail::factory('smtp', $mail_options);
        if (\PEAR::isError($this->smtp)) {
            registerError("Unable to setup mail object, error is " . $this->smtp->message);
        }
    }

    /**
     * Send a notification email.
     *
     * @param string $to Where to send the email.
     * @param string $subject The email's subject line.
     * @param string $body The email's content.
     * @param string $from The optional from address, will default to the system notification address.
     * @return boolean
     */
    public function sendEmail($to, $subject, $body, $textbody = '', $from = '') {
        Log::info('Log::sendEmail', "sendEmail($to, $subject, ".get_a($body).", <pre>$textbody</pre>, $from)");
        
        if (trim($to) == '') {
            return false;
        }
        if ($from == '') {
            $from = $this->email;
        }
        $crlf = "\n";
        $headers = array ('To'           => $to,
                          'From'         => $from,
                          'Return-Path'  => $from,
                          'Subject'      => $this->prefix.' '.$subject);

        $mime = new \Mail_mime($crlf);

        if ($textbody == '') {
            $textbody = $body;
        }
        $mime->setTXTBody($textbody);
        $mime->setHTMLBody($body);

        $body = $mime->get();
        $headers = $mime->headers($headers);

        $mail = $this->smtp->send($to, $headers, $body);

        return !\PEAR::isError($mail);
    }
}

return new Email();
?>
