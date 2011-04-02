<?php
namespace Foundry\Core\Auth;
use \Foundry\Core\Model;

/**
 * A model for users.
 *
 * @package DataModel
 */

/**
 * A model class for users.
 *
 * @package DataModel
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
