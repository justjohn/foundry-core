<?php
/**
 * A data model interface used for storing and revrieving objects from a data store.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Model
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core;

/**
 * A database model access class.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Model
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
 */
abstract class ModelAccessAbstraction {
    /**
     * Connection to the database.
     * @var Database
     */
    private $database;
    /**
     * The database table name.
     * @var string
     */
    private $table;
    /**
     * The classname to load data into.
     * @var string
     */
    private $model_class;
    /**
     * The ID field for generating IDs for database objects.
     * @var string
     */
    private $id_field;
    /**
     * The maximum increment for an id.
     * @var int
     */
    private $id_inc_max;
    /**
     * The minimum increment for an id.
     * @var int
     */
    private $id_inc_min;

    /**
     * Setup an object access layer.
     */
    function __construct($table_name,
                         $model_class,
                         $id_field,
                         $id_inc_max=100, 
                         $id_inc_min=5) {
        $this->database = Core::get('\Foundry\Core\Database\Database');
        $this->table = $table_name;
        $this->model_class = $model_class;
        $this->id_field = $id_field;
        $this->id_inc_max = $id_inc_max;
        $this->id_inc_min = $id_inc_min;
    }
   
    /**
     * Generate a unique new id for an object.
     * 
     * @return int A new ID.
     */
    public function generateId() {
        // Get the maximum id in the table.
        $object = $this->database->load_object(
                $this->model_class,
                $this->table, array(),
                array($this->id_field=>"DESC")
        );
        if ($object !== false) {
            $id = $object->getId();
        } else {
            $id = 0;
        }
        // Increment the ID by a random amount.
        return $id + rand($this->id_inc_min, $this->id_inc_max);
    }
 
    /**
     * Add an object to the datastore.
     * 
     * @param Model $object The object to add.
     * 
     * @return int|boolean The id of the new object if it was added, false if unable to add.
     */
    public function add(Model $object) {
        Log::info("ModelAccessAbstraction::add", "add($object)");
        $object->setId($this->generateId());
        $object->setPassword($this->encodePassword($password));
        $result = $this->database->write_object($object, $this->table);
        if ($result === false) {
            return false;
        } else {
            return $object->getId();
        }
    }
    
    /**
     * Update an existing object.
     * 
     * @param Model $object The updated project object.
     * @param int $project_id The project id to update.
     */
    public function update(Model $object) {
        Log::info("ModelAccessAbstraction::update", "update($object)");
        $id = $object->getId();
        $result = $this->database->update_object(
            $object, $this->table,
            array($this->id_field => $id),
            array_keys($object->getFields())
        );
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Delete an object from the data store.
     * 
     * @param integer $id The ID of the object to delete.
     * 
     * @return boolean true of the delete was successfull. 
     */
    public function delete($id) {
        Log::info("ModelAccessAbstraction::delete", "delete($id)");

        $result = $this->database->delete_object(
                $this->table,
                array($this->id_field => $id)
        );
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Get an object from the datastore.
     * 
     * @param int $id The id of the object to retrieve.
     * @param array $sort
     * 
     * @return Model|boolean The object if one exists, false if not.
     */
    public function get($id) {
        $objects = $this->database->load_objects(
                $this->model_class,
                $this->table,
                $this->id_field,
                array($this->id_field => $id)
        );
        if (count($objects) == 0) return false;
        return array_shift($objects);
    }

    /**
     * Get all the objects from the datastore.
     *
     * @param array $sort The sort parameters for the data retrieval.
     * 
     * @return array An array of Model objects indexed by the id field.
     */
    public function getAll(array $sort) {
        // Load information from DB
        $users  = $this->database->load_objects(
                $this->model_class,
                $this->table,
                $this->id_field,
                array(),
                $sort
        );

        return $users;
    }
}

?>
