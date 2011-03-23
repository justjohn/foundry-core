<?php
namespace foundry\core\logging;
use \foundry\core\Model;

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
class LogEntry extends \foundry\core\BaseModel {

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
    
    function toString() {
        $string = $this->getTimestamp() . " / " . Log::getLabel($this->getLevel()) . ": " . $this->getAction() . "\n" . 
                  $this->getMessage() . "\n";
        return $string;
    }
    function toLimitedString() {
        $string = $this->getTimestamp() . " / " . Log::getLabel($this->getLevel()) . ": " . $this->getAction() . "\n";
        return $string;
    }
}
?>
