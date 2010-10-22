<?php
/**
 * A model for access roles.
 *
 * @package DataModel
 */

/**
 * A model class for access roles.
 *
 * @package DataModel
 */
class Role extends BaseModel {

    private $fields = array("key"=>Model::STR, "description"=>Model::STR, "groups"=>Model::LST);
    private $key_field = "key";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>