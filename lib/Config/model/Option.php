<?php
namespace foundry\core\config;
use \foundry\core\Model;

/**
 * A model for config options.
 *
 * @package DataModel
 */

/**
 * A model class for config options.
 *
 * @package DataModel
 */
class Option extends \foundry\core\BaseModel {

    private $fields = array("name"=>Model::STR, "value"=>Model::STR, "id"=>Model::INT);
    private $key_field = "id";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>
