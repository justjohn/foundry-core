<?php
namespace Foundry\Core\Auth;
use \Foundry\Core\Core as Core;

require_once("AuthServiceTest.php");

Core::configure('\Foundry\Core\Auth\Auth', array(
    "admin_group" => "svn_administrators",
    "service" => 'Foundry\Core\Auth\Service\LDAP',
    "service_config" => array(
        "connectionString"      => "ldap://webfoundri.es",
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

Core::requires('\Foundry\Core\Auth\Auth');

class LDAPAuthServiceTest extends AuthServiceTest
{
    public function  __construct() {
        $auth_service = Core::get('\Foundry\Core\Auth\Auth');
        parent::__construct($auth_service);
    }
}
?>
