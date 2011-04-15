<?php
/**
 * Roles.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Access;
use \Foundry\Core\Model;
use \Foundry\Core\BaseModel;

/**
 * Role Object.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Role extends BaseModel {

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
