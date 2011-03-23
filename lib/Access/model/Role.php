<?php
namespace foundry\core\access;
use \foundry\core\Model;

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
class Role extends \foundry\core\BaseModel {

    private $fields = array("key"=>Model::STR, "description"=>Model::STR, "groups"=>Model::LST);
    private $key_field = "key";

    function __construct($key='', $description='', $groups=array()) {
        parent::__construct($this->fields, $this->key_field);
        parent::setKey($key);
        parent::setDescription($description);
        parent::setGroups($groups);
    }
}
?>
