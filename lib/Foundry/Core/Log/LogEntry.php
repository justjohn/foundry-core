<?php
/**
 * Log messages.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Logging
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Logging;

use \Foundry\Core\Model;
use \Foundry\Core\BaseModel;

/**
 * Log messages.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Logging
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
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