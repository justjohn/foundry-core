<?php
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
class Option extends BaseModel {

    private $fields = array("name"=>Model::STR, "value"=>Model::STR, "id"=>Model::INT);
    private $key_field = "id";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>
