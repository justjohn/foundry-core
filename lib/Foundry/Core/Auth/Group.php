<?php
/**
 * Authentication service user group.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Auth;
use \Foundry\Core\Model;

/**
 * Authentication service user group.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Group extends \Foundry\Core\BaseModel {

    private $fields = array("name"=>Model::STR, "description"=>Model::STR, "users"=>Model::LST, "subgroups"=>Model::LST);
    private $key_field = "name";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>