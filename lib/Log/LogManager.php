<?php
register_class("LogEntry", "Log/model/LogEntry.php");

/**
 * Write log entries.
 *
 * @package modules
 */
class LogManager {
    const DEBUG = "DEBUG";
    const WARN = "WARN";
    const INFO = "INFO";
    const ERROR = "ERROR";

    private static $database;
    private static $auth_manager;

    function __construct($database, $auth_manager) {
        self::$database = $database;
        self::$auth_manager = $auth_manager;
    }

    public static function error($action, $message) {
        self::log(self::ERROR, $action, $message);
    }
    public static function warn($action, $message) {
        self::log(self::WARN, $action, $message);
    }
    public static function info($action, $message) {
        self::log(self::INFO, $action, $message);
    }
    public static function debug($action, $message) {
        self::log(self::DEBUG, $action, $message);
    }

    public static function log($level, $action, $message) {
        // write to log
        $timestamp = time();
        $user = self::$auth_manager->getUsername();
        $log_entry = new LogEntry();
        $log_entry->setLevel($level);
        $log_entry->setAction($action);
        $log_entry->setMessage($message);
        $log_entry->setUser($user);
        $log_entry->setTimestamp($timestamp);
        self::$database->write_object($log_entry, "log");
        //print("<div>$level: $action / $user<br />$message</div>");
    }

    /**
     * Get the system event log. By default this will only return the latest 100 entries.
     *
     * @param array $filter An array of database filters.
     * @param integer $limit_number The number of records to return or 0 for all records.
     * @param integer $limit_start The start record to return from.
     * @return array An array of LogEntry objects.
     */
    public function getLog($filter='', $limit_number=0, $limit_start=0) {
        $limits = '';
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
    public function getLogLength($filter = '') {
        return self::$database->count_objects("log", $filter);
    }
}
?>
