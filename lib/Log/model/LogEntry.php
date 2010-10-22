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
class LogEntry extends BaseModel {

    private $fields = array("level"=>Model::STR,
                            "action"=>Model::STR,
                            "message"=>Model::STR,
                            "timestamp"=>Model::INT,
                            "user"=>Model::STR,
                            "id"=>Model::INT);
    private $key_field = "id";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>