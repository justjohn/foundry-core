<?php
/**
 * Handles log message processing.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Logging
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Logging;

use \Foundry\Core\Core;

/**
 * Write log entries.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Logging
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Log {
    const DEBUG = 1;
    const INFO  = 2;
    const WARN  = 4;
    const ERROR = 8;

    /**
     * The minimum level to log.
     */
    private static $level = self::WARN;
    
    public static $log_function;
    public static $user_function;

    function __construct($level = self::WARN,
                         \Closure $log_function = NULL,
                         \Closure $user_function = NULL ) {
        self::$level = $level;
        if ($log_function !== NULL)
            self::$log_function = $log_function;
        if ($user_function !== NULL)
            self::$user_function = $user_function;
    }

    public static function error($action, $message) {
        if (self::ERROR >= self::$level)
            self::log(self::ERROR, $action, $message, self::$user_function, self::$log_function);
    }
    public static function warn($action, $message) {
        if (self::WARN >= self::$level)
            self::log(self::WARN, $action, $message, self::$user_function, self::$log_function);
    }
    public static function info($action, $message) {
        if (self::INFO >= self::$level)
            self::log(self::INFO, $action, $message, self::$user_function, self::$log_function);
    }
    public static function debug($action, $message) {
        if (self::DEBUG >= self::$level)
            self::log(self::DEBUG, $action, $message, self::$user_function, self::$log_function);
    }

    public static function log($level, $action, $message, \Closure $user_function, \Closure $log_function) {
        // write to log
        $timestamp = time();
        $user = $user_function();

        $log_entry = new LogEntry();
        $log_entry->setLevel(self::getLabel($level));
        $log_entry->setAction($action);
        $log_entry->setMessage($message);
        $log_entry->setUser($user);
        $log_entry->setTimestamp($timestamp);
    
        $log_function($log_entry);
    }
    
    public static function getLabel($error_level) {
        switch ($error_level) {
            case self::DEBUG:
                return "DEBUG";
            case self::INFO:
                return "INFO";
            case self::WARN:
                return "WARN";
            case self::ERROR:
                return "ERROR";
            default:
                return "OTHER";
        }
    }
}

Log::$log_function = function(LogEntry $log_entry) {
    error_log($log_entry);
};
Log::$user_function = function() {
    return "";
};

?>
