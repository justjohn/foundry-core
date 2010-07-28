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

    private $database;
    private $auth_manager;

    function __construct($database, $auth_manager) {
        $this->database = $database;
        $this->auth_manager = $auth_manager;
    }

    public function error($action, $message) {
        $this->log(self::ERROR, $action, $message);
    }
    public function warn($action, $message) {
        $this->log(self::WARN, $action, $message);
    }
    public function info($action, $message) {
        $this->log(self::INFO, $action, $message);
    }
    public function debug($action, $message) {
        $this->log(self::DEBUG, $action, $message);
    }

    public function log($level, $action, $message) {
        // write to log
        $timestamp = time();
        $user = $this->auth_manager->getUsername();
        $log_entry = new LogEntry();
        $log_entry->setLevel($level);
        $log_entry->setAction($action);
        $log_entry->setMessage($message);
        $log_entry->setUser($user);
        $log_entry->setTimestamp($timestamp);
        $this->database->write_object($log_entry, "log");
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
        return $this->database->load_objects("LogEntry", "log", 'id', $filter, array("timestamp"=>"DESC"), $limits);
    }

    /**
     * Get the number of log entries.
     *
     * @param array $filter An array of database filters.
     * @return array
     */
    public function getLogLength($filter = '') {
        return $this->database->count_objects("log", $filter);
    }
}
?>
