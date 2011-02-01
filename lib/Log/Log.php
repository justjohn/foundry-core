<?php
namespace foundry\core\logging;

\foundry\core\Core::register_class('foundry\core\logging\LogEntry', "Log/model/LogEntry.php");

/**
 * Write log entries.
 *
 * @package modules
 */
class Log {
    const DEBUG = 1;
    const INFO  = 2;
    const WARN  = 4;
    const ERROR = 8;

    /**
     * The minimum level to log.
     */
    private static $level;
    
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
            self::log("ERROR", $action, $message, self::$user_function, self::$log_function);
    }
    public static function warn($action, $message) {
        if (self::WARN >= self::$level)
            self::log("WARN", $action, $message, self::$user_function, self::$log_function);
    }
    public static function info($action, $message) {
        if (self::INFO >= self::$level)
            self::log("INFO", $action, $message, self::$user_function, self::$log_function);
    }
    public static function debug($action, $message) {
        if (self::DEBUG >= self::$level)
            self::log("DEBUG", $action, $message, self::$user_function, self::$log_function);
    }

    public static function log($level, $action, $message, \Closure $user_function, \Closure $log_function) {
        // write to log
        $timestamp = time();
        $user = $user_function();

        $log_entry = new LogEntry();
        $log_entry->setLevel($level);
        $log_entry->setAction($action);
        $log_entry->setMessage($message);
        $log_entry->setUser($user);
        $log_entry->setTimestamp($timestamp);
    
        $log_function($log_entry);
    }
}

Log::$log_function = function($log_entry) {
    error_log($log_entry);
};
Log::$user_function = function() {
    return "none";
};

?>
