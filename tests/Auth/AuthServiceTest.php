<?php
require("common.php");
require("Auth/AuthService.php");
require("Auth/Service/CrowdAuthService.php");

class AuthServiceTest extends PHPUnit_Framework_TestCase
{
    protected static $crowd_options = array("app_name"=>"hubtest",
                                     "app_credential"=>"v13sda0%T",
                                     "service_url"=>"http://crowd.moqul.net/services/SecurityServer?wsdl");
    /**
     * The authentication manager.
     * @var Auth
     */
    protected static $auth_service;

    // Data Providers
    public function userPasswords()
    {
        return array(
            array("test", "test"),
            array("user", "password")
        );
    }
    public function changePasswords()
    {
        return array(
            array("test", "newpwd"),
            array("user", "l33tp4ss"),
            array("test", "test"),
            array("user", "password")
        );
    }
    public function users()
    {
        return array(
            array("test"),
            array("user")
        );
    }

    public static function setUpBeforeClass()
    {
        register_class("User", "Auth/model/User.php");
        register_class("Group", "Auth/model/Group.php");
        register_class("ResetToken", "Auth/model/ResetToken.php");
        self::$auth_service = new CrowdAuthService(self::$crowd_options);
    }

    public static function tearDownAfterClass()
    {
        self::$auth_service = null;
    }

    public function setUp() {

    }

    // User methods

    /**
     * Test authenticate($username, $password)
     *
     * @dataProvider userPasswords
     */
    public function testAuthenticate($username, $password)
    {
        $result = self::$auth_service->authenticate($username, $password);
        $this->assertTrue($result);
    }

    /**
     * Test changePassword($username, $password)
     *
     * @dataProvider changePasswords
     */
    public function testChangePassword($username, $password)
    {
        $result = self::$auth_service->changePassword($username, $password);
        $this->assertTrue($result);
        $result = self::$auth_service->authenticate($username, $password);
        $this->assertTrue($result);
    }

    /**
     * Test userExists($username)
     *
     * @dataProvider users
     */
    public function testUserExists($username)
    {
        $result = self::$auth_service->userExists($username);
        $this->assertTrue($result);
    }

    /**
     * Test getUsers()
     */
    public function testGetUsers()
    {

    }

    /**
     * Test getUser($username)
     */
    public function testGetUser()
    {

    }

    /**
     * Test getUserGroups($user)
     */
    public function testGetUserGroups()
    {

    }

    /**
     * Test addUser($user, $password)
     */
    public function testAddUser()
    {

    }

    /**
     * Test deleteUser($username)
     */
    public function testDeleteUser()
    {

    }

    // Group Methods

    /**
     * Test getGroups()
     */
    public function testGetGroups()
    {

    }

    /**
     * Test getGroup($groupname)
     */
    public function testGetGroup()
    {

    }

    /**
     * Test addGroup($group)
     */
    public function testAddGroup()
    {

    }

    /**
     * Test deleteGroup($groupname)
     */
    public function testDeleteGroup()
    {

    }

    /**
     * Test addUserToGroup($username, $groupname)
     */
    public function testAddUserToGroup()
    {

    }

    /**
     * Test addSubgroupToGroup($subgroupname, $groupname)
     */
    //public function testAddSubgroupToGroup()

    /**
     * Test removeUserFromGroup($username, $groupname)
     */
    public function testRemoveUserFromGroup()
    {

    }

    /**
     * Test removeSubgroupFromGroup($subgroupname, $groupname)
     */
    //public function testRemoveSubgroupFromGroup()
}

?>