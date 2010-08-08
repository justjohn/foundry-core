<?php
/**
 * A model for log entries.
 *
 * @package DataModel
 */

/**
 * A model class for log entries.
 *
 * @package DataModel
 */
class LogEntry {
    /**
     * The level field.
     * @var string
     */
    public $level;
    /**
     * The action field.
     * @var string
     */
    public $action;
    /**
     * The message field.
     * @var string
     */
    public $message;
    /**
     * The timestamp field.
     * @var integer
     */
    public $timestamp;
    /**
     * The user field.
     * @var string
     */
    public $user;
    /**
     * The id field.
     * @var integer
     */
    public $id;

    /**
     * Set the level field.
     * @param string $level 
     */
    public function setLevel($level) {
        $this->level = $level;
    }
    /**
     * Get the level field.
     * @return string
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Set the action field.
     * @param string $action 
     */
    public function setAction($action) {
        $this->action = $action;
    }
    /**
     * Get the action field.
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set the message field.
     * @param string $message 
     */
    public function setMessage($message) {
        $this->message = $message;
    }
    /**
     * Get the message field.
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Set the timestamp field.
     * @param integer $timestamp 
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }
    /**
     * Get the timestamp field.
     * @return integer
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Set the user field.
     * @param string $user 
     */
    public function setUser($user) {
        $this->user = $user;
    }
    /**
     * Get the user field.
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set the id field.
     * @param integer $id 
     */
    public function setId($id) {
        $this->id = $id;
    }
    /**
     * Get the id field.
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
}
?>