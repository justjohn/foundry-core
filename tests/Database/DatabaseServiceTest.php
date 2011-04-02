<?php
namespace Foundry\Core\Database;
use \Foundry\Core\Model;
use \Foundry\Core\BaseModel;

abstract class DatabaseServiceTest extends \PHPUnit_Framework_TestCase {
    /**
     * Access to the database service.
     * @var Database
     */
    protected $db_service;
    
    protected $key = "data";

    public function __construct($db_service) {
        $this->db_service = $db_service;
    }
    
    private $data_values = array('z','y','x','n','m','l','k','c','b','a');
    /**
     * Add some test data to work with.
     */
    public function setUp() {
        $active = true;
        $users = array("john", "tom", "bob");
        for ($i=0;$i<10;$i++) {
            $object = $this->getObject($i, $this->data_values[$i], ($i%2==0), $users);
            $result = $this->db_service->write_object($object, $this->key);
            $this->assertTrue($result);
        }
    }
    
    /**
     * Remove the test data.
     */
    public function tearDown() {
        $this->db_service->delete_object($this->key, array());
    }
    
    public function testLoadObjectsWithLimit() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array(), array(), array(1));
        $this->assertEquals(1, count($data));
        $object = array_shift($data);
        $this->assertEquals($object->getId(), 0);
        
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array(), array(), array(2, 1));
        $this->assertEquals(1, count($data));
        $object = array_shift($data);
        $this->assertEquals($object->getId(), 2);
        
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array(), array(), array(6, 100));
        $this->assertEquals(4, count($data));
        $object = array_shift($data);
        $this->assertEquals($object->getId(), 6);
        
        // Some invalid data
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array(), array(), array(-100));
        $this->assertEquals(0, count($data));
        // Some invalid data
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array(), array(), array(10000, 10000000));
        $this->assertEquals(0, count($data));
    }
    
    public function testLoadObjectsWithSort() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, '', array('id' => array('<', 3)), array('value'=>'ASC'));
        $this->assertEquals(3, count($data)); // 0,1,2
        $data_0 = array_shift($data); // value = $data_values[2]
        $this->assertEquals($data_0->getValue(), $this->data_values[2]);
        $data_1 = array_shift($data); // value = $data_values[1]
        $this->assertEquals($data_1->getValue(), $this->data_values[1]);
        $data_2 = array_shift($data); // value = $data_values[0]
        $this->assertEquals($data_2->getValue(), $this->data_values[0]);
    }
    
    public function testLoadObjectsWithConditions() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id', array('id' => 2));
        $this->assertEquals(1, count($data));
        $this->assertNotNull($data[2]);
        $data_2 = $data[2];
        $this->assertTrue($data_2 instanceof Data);
        $this->assertEquals(2, $data_2->getId());
        $this->assertEquals($this->data_values[2], $data_2->getValue());
        $this->assertEquals(array("john", "tom", "bob"), $data_2->getUsers());
        $this->assertTrue($data_2->getActive());
        
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id', array('id' => array('<', 3)));
        $this->assertEquals(3, count($data)); // 0,1,2
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id', array('id' => array('<=', 3)));
        $this->assertEquals(4, count($data)); // 0,1,2,3
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id', array('id' => array('>', 5)));
        $this->assertEquals(4, count($data)); // 6,7,8,9
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id', array('id' => array('>=', 4)));
        $this->assertEquals(6, count($data)); // 4,5,6,7,8,9
    }
    
    public function testLoadObjectsWithKey() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, 'id');
        $this->assertEquals(10, count($data));
        $data_0 = $data[0];
        $this->assertTrue($data_0 instanceof Data);
        $this->assertEquals(0, $data_0->getId());
        $this->assertEquals($this->data_values[0], $data_0->getValue());
        $this->assertEquals(array("john", "tom", "bob"), $data_0->getUsers());
        $this->assertTrue($data_0->getActive());
    }
    
    public function testLoadObjectsWithNoKey() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key);
        $this->assertEquals(10, count($data));
    }
    
    /**
     * @expectedException Foundry\Core\Exceptions\FieldDoesNotExistException
     */
    public function testLoadObjectsWithInvalidKey() {
        $data = $this->db_service->load_objects('\Foundry\Core\Database\Data', $this->key, "zzzz");
    }
    
    public function getObject($id=1, $value="test", $active=true, $users=array("john", "tom", "bob")) {
        $object = new Data();
        $object->setValue($value);
        $object->setActive($active);
        $object->setUsers($users);
        $object->setId($id);
        return $object;
    }
}

/**
 * A sample data object.
 */
class Data extends BaseModel {

    private $fields = array("value"=>Model::STR,
                            "active"=>Model::BOOL,
                            "users"=>Model::LST,
                            "id"=>Model::INT);
    private $key_field = "id";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
    
    function toString() {
        $string = "{ value: " . $this->getValue()
                . ", active: " . $this->getActive()
                . ", users [" . implode(", ", $this->getAction()) . "]"
                . ", id: " . $this->getId() . "}";
        return $string;
    }
}

?>
