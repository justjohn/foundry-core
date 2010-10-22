<?php
set_include_path(get_include_path()
        . PATH_SEPARATOR . "../lib/");

require_once("Core/Core.php");
require_once("Functions/debug.php");
require_once("Auth/AuthService.php");
require_once("Auth/AuthServiceSSO.php");
require_once("Auth/AuthServiceSubgroups.php");


abstract class AuthServiceTest extends PHPUnit_Framework_TestCase {

    /**
     * The authentication manager.
     * @var Auth
     */
    protected $auth_service;

    protected $users = array("z_test"=>"test",
                             "z_user"=>"password");

    protected $groups = array("z_group"=>"A test group",
                              "z_subgroup"=>"Another test group!");

    protected $added_users = array();
    protected $added_groups = array();

    public function __construct($auth_service) {
        $this->auth_service = $auth_service;
    }

    public static function setUpBeforeClass()
    {
        Core::register_class("User", "Auth/model/User.php");
        Core::register_class("Group", "Auth/model/Group.php");
        Core::register_class("ResetToken", "Auth/model/ResetToken.php");
    }

    public function setUp() {
        foreach ($this->users as $username=>$password) {
            //print("Adding $username/$password\n");
            $user = new User();
            $user->setUsername($username);
            $user->setEmail("$username@test.phpfoundry.com");
            $user->setFirstName("first-$username");
            $user->setSurname("last-$username");
            $user->setDisplayName("first-last-$username");
            $this->added_users[$username] = $user;

            $result = $this->auth_service->addUser($user, $password);
            if (!$result) {
                print("Unable to add test user $username.\n");
            }
        }
        foreach ($this->groups as $name=>$description) {
            $group = new Group();
            $group->setName($name);
            $group->setDescription($description);
            $this->added_groups[$name] = $group;
            $this->auth_service->addGroup($group);
        }
    }

    public function tearDown() {
        foreach ($this->users as $username=>$password) {
            //print("Removing user $username\n");
            $result = $this->auth_service->deleteUser($username);
            if (!$result) {
                print("Unable to remove test user $username.\n");
            }
        }
        foreach ($this->groups as $name=>$description) {
            $this->auth_service->deleteGroup($name);
        }
    }

    /**
     * Test authenticate($username, $password)
     */
    public function testAuthenticate()
    {
        foreach ($this->users as $username=>$password) {
            //print("Testing $username/$password\n");
            $result = $this->auth_service->authenticate($username, $password);
            $this->assertTrue($result);
        }
        // test invalid users
        $result = $this->auth_service->authenticate("Nobody_user", "pwd");
        $this->assertFalse($result);
        $result = $this->auth_service->authenticate("", "pwd");
        $this->assertFalse($result);
    }
    /**
     * Test changePassword($username, $password)
     */
    public function testChangePassword()
    {
        $pw_change = array(
            "z_test" => "newpwd",
            "z_user" => "l33tp4ss",
            "z_test" => "test",
            "z_user" => "password"
        );
        foreach ($pw_change as $username=>$password) {
            $result = $this->auth_service->changePassword($username, $password);
            $this->assertTrue($result);
            $result = $this->auth_service->authenticate($username, $password);
            $this->assertTrue($result);
        }

        // test invalid users
        $result = $this->auth_service->changePassword("Nobody_user", "pwd");
        $this->assertFalse($result);
        $result = $this->auth_service->changePassword("", "pwd");
        $this->assertFalse($result);
    }
    /**
     * Test userExists($username)
     */
    public function testUserExists()
    {
        foreach ($this->users as $username=>$password) {
            $result = $this->auth_service->userExists($username);
            $this->assertTrue($result);
        }
        // test invalid users
        $result = $this->auth_service->userExists("nobody_here...");
        $this->assertFalse($result);
        $result = $this->auth_service->userExists("");
        $this->assertFalse($result);
    }

    /**
     * Test getUsers()
     */
    public function testGetUsers()
    {
        $users = $this->auth_service->getUsers();
        foreach ($this->users as $username=>$password) {
            $this->assertEquals($users[$username], $this->added_users[$username]);
        }
    }

    /**
     * Test getUser($username)
     */
    public function testGetUser()
    {
        foreach ($this->users as $username=>$password) {
            $user = $this->auth_service->getUser($username);
            $this->assertEquals($user, $this->added_users[$username]);
        }
    }

    public function testGetUserInvalid()
    {
        // test blank user
        $user = $this->auth_service->getUser('');
        $this->assertFalse($user);

        // test non-existant user
        $user = $this->auth_service->getUser('abracadabra');
        $this->assertFalse($user);
    }

    /**
     * Test addUser($user, $password)
     */
    public function testAddCheckRemoveUser()
    {
        // ensure the user is always unique
        $user = new User(md5(microtime()), 'az@test.phpfoundry.com', 'Zam!', 'Ala', 'Cazam');
        $password = "pwd";

        // Add user
        $this->assertTrue($this->auth_service->addUser($user, $password));

        // Try adding again
        $this->assertFalse($this->auth_service->addUser($user, $password));

        // Check user
        $c_user = $this->auth_service->getUser($user->getUsername());
        $this->assertEquals($user, $c_user);

        // Remove User
        $result = $this->auth_service->deleteUser($user->getUsername());
        $this->assertTrue($result);

        // Invalid Remove User
        $result = $this->auth_service->deleteUser($user->getUsername());
        $this->assertFalse($result);

        // Check user
        $c_user = $this->auth_service->getUser($user->getUsername());
        $this->assertFalse($c_user);
    }

    // Group Methods

    /**
     * Test getGroups()
     */
    public function testGetGroups()
    {
        $groups = $this->auth_service->getGroups();
        foreach ($this->groups as $name=>$description) {
            $this->assertEquals($groups[$name], $this->added_groups[$name]);
        }
    }

    /**
     * Test getGroupNames()
     */
    public function testGetGroupNames()
    {
        $groups = $this->auth_service->getGroupNames();
        
        foreach ($this->groups as $name=>$description) {
            $this->assertTrue(isset($groups[$name]));
            $this->assertEquals($groups[$name], $name);
        }
    }

    /**
     * Test testGroupExists()
     */
    public function testGroupExists() {
        foreach ($this->groups as $name=>$description) {
            $this->assertTrue($this->auth_service->groupExists($name));
        }

        $this->assertFalse($this->auth_service->groupExists("fmireoypuwsd"));
        $this->assertFalse($this->auth_service->groupExists(""));
    }

    /**
     * Test getGroup($groupname)
     */
    public function testGetGroup()
    {
        foreach ($this->groups as $name=>$description) {
            $group = $this->auth_service->getGroup($name);
            $this->assertEquals($group, $this->added_groups[$name]);
        }
    }

    public function testGetGroupInvalid()
    {
        // test blank group
        $group = $this->auth_service->getGroup('');
        $this->assertFalse($group);

        // test non-existant group
        $group = $this->auth_service->getUser('abracadabra');
        $this->assertFalse($group);
    }

    /**
     * Test addGroup($group)
     */
    public function testAddCheckRemoveGroup()
    {
        $group = new Group();
        $group->setName(md5(microtime()));
        $group->setDescription("Nothing to see here...");

        // Add group
        $this->assertTrue($this->auth_service->addGroup($group));

        // Add group again
        $this->assertFalse($this->auth_service->addGroup($group));

        // Check group
        $c_group = $this->auth_service->getGroup($group->getName());
        $this->assertEquals($group, $c_group);

        // Remove Group
        $this->assertTrue($this->auth_service->deleteGroup($group->getName()));

        // Remove Group again
        $this->assertFalse($this->auth_service->deleteGroup($group->getName()));
    }

    /**
     * Test addUserToGroup($username, $groupname)
     */
    public function testAddRemoveUserFromGroup()
    {
        $group_keys = array_keys($this->groups);
        $groupname = array_pop($group_keys);

        $user_keys = array_keys($this->users);
        $username = array_pop($user_keys);
        $username2 = array_pop($user_keys);

        $this->assertTrue($this->auth_service->addUserToGroup($username, $groupname));

        // Check group membership
        $group = $this->auth_service->getGroup($groupname);
        $g_users = $group->getUsers();
        $this->assertTrue(isset($g_users[$username]));

        $this->assertTrue($this->auth_service->addUserToGroup($username2, $groupname));
        // Check group membership
        $group = $this->auth_service->getGroup($groupname);
        $g_users = $group->getUsers();
        $this->assertTrue(isset($g_users[$username]));
        $this->assertTrue(isset($g_users[$username2]));

        // Check user groups
        $c_user_groups = $this->auth_service->getUserGroups($username);
        $this->assertTrue(isset($c_user_groups[$groupname]));
        $c_user_groups = $this->auth_service->getUserGroups($username2);
        $this->assertTrue(isset($c_user_groups[$groupname]));

        $this->assertTrue($this->auth_service->removeUserFromGroup($username, $groupname));
        $this->assertTrue($this->auth_service->removeUserFromGroup($username2, $groupname));

        $this->assertFalse($this->auth_service->removeUserFromGroup($username, $groupname));

        $group = $this->auth_service->getGroup($groupname);
        $g_users = $group->getUsers();

        $this->assertFalse(isset($g_users[$username]));
        $this->assertFalse(isset($g_users[$username2]));
    }
    /**
     * Test addUserToGroup($username, $groupname)
     */
    public function testInvalidAddRemoveUserFromGroup() {
        $group_keys = array_keys($this->groups);
        $groupname = array_pop($group_keys);
        $user_keys = array_keys($this->users);
        $username = array_pop($user_keys);

        // test blank fields
        $this->assertFalse($this->auth_service->addUserToGroup("", ""));
        $this->assertFalse($this->auth_service->addUserToGroup("", $groupname));
        $this->assertFalse($this->auth_service->addUserToGroup($username, ""));

        // test invalid fields
        $this->assertFalse($this->auth_service->addUserToGroup("32354rg3es", "hjtrnh23qwre"));
        $this->assertFalse($this->auth_service->addUserToGroup("hb623qbdst", $groupname));
        $this->assertFalse($this->auth_service->addUserToGroup($username, "earwsystr"));
    }

    /**
     * Test removeSubgroupFromGroup($subgroupname, $groupname)
     */
    //public function testRemoveSubgroupFromGroup()
}

?>
