<?php
namespace Foundry\Core\Access;

abstract class AccessServiceTest extends \PHPUnit_Framework_TestCase {

    /**
     * The access service to test.
     * @var AccessService
     */
    private $access_service;

    private $roles = array(
        "role_1" => array("desc"=>"test role", "groups"=>"group,test,etc"),
        "role2"  => array("desc"=>"", "groups"=>"group,misc,admin"),
        "role_admin" => array("desc"=>"Admins", "groups"=>"admin")
    );

    public function __construct($access_service) {
        $this->access_service = $access_service;
    }

    /**
     * Add test data.
     */
    public function setUp() {
        foreach ($this->roles as $role_key => $role_info) {
            $role_groups = explode(",", $role_info["groups"]);
            $role_description = $role_info["desc"];
            $role = new Role();
            $role->setKey($role_key);
            $role->setDescription($role_description);
            $role->setGroups($role_groups);
            $this->access_service->addRole($role);
        }
    }

    /**
     * Remove test data.
     */
    public function tearDown() {
        foreach ($this->roles as $role_key => $role_info) {
            $this->access_service->removeRole($role_key);
        }
    }

    public function testGetRole() {
        foreach ($this->roles as $role_key => $role_info) {
            $role_groups = explode(",", $role_info["groups"]);
            $role_description = $role_info["desc"];
            $role = $this->access_service->getRole($role_key);
            $this->assertEquals($role_groups, $role->getGroups());
            $this->assertEquals($role_description, $role->getDescription());
            $this->assertEquals($role_key, $role->getKey());
        }
    }

    public function testAddRemoveRoles() {
        // Add role
        $role_key = "test";
        $role_description = "a test role";
        $role_groups = array("test", "other");
        $role = new Role($role_key, $role_description, $role_groups);
        // Add a role
        $result = $this->access_service->addRole($role);
        $this->assertTrue($result);
        // Try adding again
        $result = $this->access_service->addRole($role);
        $this->assertFalse($result);

        // Check blank cases
        $result = $this->access_service->addRole(new Role("", $role_description, $role_groups));
        $this->assertFalse($result);
        $result = $this->access_service->addRole(new Role("blah", "", array()));
        $this->assertFalse($result);

        // Remove role
        $result = $this->access_service->removeRole($role_key);
        $this->assertTrue($result);
        // Remove again
        $result = $this->access_service->removeRole($role_key);
        $this->assertFalse($result);

        // Check blank cases
        $result = $this->access_service->removeRole("");
        $this->assertFalse($result);

    }
}
?>
