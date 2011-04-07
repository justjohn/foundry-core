<?php
/**
 * Authentication service user.
 *
 * @category  foundry-core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Auth;
use \Foundry\Core\Model;

/**
 * Authentication service user.
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class User extends \Foundry\Core\BaseModel {

    private $fields = array("username"=>Model::STR,
                            "displayName"=>Model::STR,
                            "email"=>Model::STR,
                            "firstName"=>Model::STR,
                            "surname"=>Model::STR);

    private $key_field = "username";

    function __construct($username='', $email='', $displayname='', $fisrtname='', $surname='') {
        parent::__construct($this->fields, $this->key_field);
        parent::setUsername($username);
        parent::setEmail($email);
        parent::setDisplayName($displayname);
        parent::setFirstName($fisrtname);
        parent::setSurname($surname);
    }
}
?>
