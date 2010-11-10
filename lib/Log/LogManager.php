<?php
Core::register_class("LogEntry", "Log/model/LogEntry.php");

/**
 * Write log entries.
 *
 * @package modules
 */
class LogManager {
    const DEBUG = 1;
    const INFO  = 2;
    const WARN  = 4;
    const ERROR = 8;

    private static $level = 4;
    private static $database;
    private static $auth_manager;

    function __construct($database, $auth_manager) {
        self::$database = $database;
        self::$auth_manager = $auth_manager;
    }

    public static function error($action, $message) {
        if (self::ERROR >= self::$level)
            self::log("ERROR", $action, $message);
    }
    public static function warn($action, $message) {
        if (self::WARN >= self::$level)
            self::log("WARN", $action, $message);
    }
    public static function info($action, $message) {
        if (self::INFO >= self::$level)
            self::log("INFO", $action, $message);
    }
    public static function debug($action, $message) {
        if (self::DEBUG >= self::$level)
            self::log("DEBUG", $action, $message);
    }

    public static function log($level, $action, $message) {
        // write to log
        $timestamp = time();
        $user = "Console";
        if (isset(self::$auth_manager))
            $user = self::$auth_manager->getUsername();

        $log_entry = new LogEntry();
        $log_entry->setLevel($level);
        $log_entry->setAction($action);
        $log_entry->setMessage($message);
        $log_entry->setUser($user);
        $log_entry->setTimestamp($timestamp);

        if (isset(self::$database)) {
            self::$database->write_object($log_entry, "log");
        } else{
            print($log_entry);
        }
    }

    /**
     * Get the system event log. By default this will only return the latest 100 entries.
     *
     * @param array $filter An array of database filters.
     * @param integer $limit_number The number of records to return or 0 for all records.
     * @param integer $limit_start The start record to return from.
     * @return array An array of LogEntry objects.
     */
    public function getLog(array $filter=array(), $limit_number=0, $limit_start=0) {
        $limits = array();
        if ($limit_number > 0) {
                $limits = array($limit_start, $limit_number);
        }
        return self::$database->load_objects("LogEntry", "log", 'id', $filter, array("timestamp"=>"DESC"), $limits);
    }

    /**
     * Get the number of log entries.
     *
     * @param array $filter An array of database filters.
     * @return array
     */
    public function getLogLength($filter = array()) {
        return self::$database->count_objects("log", $filter);
    }
}
?>
