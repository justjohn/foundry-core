<?php
namespace foundry\core\auth;
use \foundry\core\Core as Core;

require_once("AuthServiceTest.php");

Core::configure('\foundry\core\auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'LDAPAuthService',
    "service_config" => array(
        "connectionString"      => "ldap://localhost",
        "baseDN"                => "dc=phpfoundry,dc=com",
        "managerDN"             => "cn=admin",
        "managerPassword"       => "r-vq398rEuj",
        "userDN"                => "ou=people",
        "usernameAttr"          => "cn",
        "userFirstNameAttr"     => "givenName",
        "userSurnameAttr"       => "sn",
        "userDisplayNameAttr"   => "displayName",
        "userEmailAttr"         => "mail",
        "userGroupAttr"         => "memberOf",
        "userPasswordAttr"      => "userPassword",
        "userObjectClass"       => "inetOrgPerson",
        "groupDN"               => "ou=groups",
        "groupObjectClass"      => "groupOfUniqueNames",
        "groupNameAttr"         => "cn",
        "groupDescAttr"         => "description",
        "groupMemberAttr"       => "uniqueMember",
        "roleDN"                => "ou=roles",
        "roleObjectClass"       => "groupOfUniqueNames",
        "roleNameAttr"          => "cn",
        "roleDescAttr"          => "description",
        "roleMemberAttr"        => "uniqueMember"
    )
));

Core::requires('\foundry\core\auth\Auth');

class LDAPAuthServiceTest extends AuthServiceTest
{
    public function  __construct() {
        $auth_service = Core::get('\foundry\core\auth\Auth');
        parent::__construct($auth_service);
    }
}
?>
