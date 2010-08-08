<?php
require_once("lib/Auth/AuthServiceTest.php");
require_once("Auth/Service/LDAPAuthService.php");

class LDAPAuthServiceTest extends AuthServiceTest
{
    protected static $ldap_options = array(
        "connectionString"      => "ldap://localhost",
        "baseDN"                => "dc=moqul,dc=net",
        "managerDN"             => "cn=admin",
        "managerPassword"       => "flashspeeder",
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
    );

    public function  __construct() {
        $auth_service = new LDAPAuthService(self::$ldap_options);
        parent::__construct($auth_service);
    }
}
?>
