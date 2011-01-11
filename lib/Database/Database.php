<?php
namespace foundry\core\database;
use \foundry\core\Model as Model;
use \foundry\core\logging\Log as Log;

/**
 * Load the AuthService interface.
 */
require_once("Database/DatabaseService.php");

class Database {

    const KEY_FIELD = "id";

    private $database;
    
    function __construct($db_service,
                         array $db_config) {
        
        // include auth class
        include_once("Database/Service/$db_service.php");
        $db_service = 'foundry\core\database\\'.$db_service;
        if (!class_exists($db_service)) {
            Log::error("Database::__construct", "Unable to load database class '$db_service'.");
            throw new \foundry\core\exceptions\ServiceLoadException("Unable to load database class '$db_service'.");
        }
        $this->database = new $db_service($db_config);
    }
    
    /**
     * Load objects from a table in the database.
     *
     * @param string $classname  The name of the class type to instantiate and load data into.
     * @param string $db_key  The name of the table in the database.
     * @param string $key        The table column to key the returned array with.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @param array  $sort_rules An array of sorting rules in the form:
     *                             array("field" => "DESC"/"ASC", ...)
     * @param array  $limits     An array with limit conditions either in the form:
     *                              array("count")  or
     *                              array("start", "count")
     * @return object|boolean An array of $classname instances keyed by the $key field (if set),
     *                        false on failure.
     */
    public function load_objects($classname, $db_key, $key = "", array $conditions = array(), array $sort_rules = array(), array $limits = array()) {
        return $this->database->load_objects($classname, $db_key, $key, $conditions, $sort_rules, $limits);
    }

    /**
     * Get a count of objects in a table.
     *
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return integer|boolean The count on success, false on failure.
     */
    public function count_objects($db_key, array $conditions = array()) {
        return $this->database->count_objects($db_key, $conditions);
    }

    /**
     * Load an object from the database.
     *
     * @param string $classname The name of the class type to instantiate and load data into.
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname, $db_key, array $conditions = array()) {
        return $this->database->load_object($classname, $db_key, $conditions);
    }

    /**
     * Writes the values from the object into the given database table.
     *
     * @param Model $object The object with the data to write into the database.
     * @param string $db_key The name of the table in the database.
     * @return boolean true on success, false on failure.
     */
    public function write_object(Model $object, $db_key) {
        return $this->database->write_object($object, $db_key);
    }

    /**
     * Update an existing object in the database.
     *
     * @param Model  $object The object data to update with.
     * @param string $db_key The name of the table to update.
     * @param array  $conditions The conditions for the database objects to update in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @param array  $updatefields An array of fields to update in each object.
     */
    public function update_object(Model $object, $db_key, array $conditions, array $updatefields) {
        return $this->database->update_object($object, $db_key, $conditions, $updatefields);
    }

    /**
     * Delete object[s] from the database.
     *
     * @param $db_key The database object/table reference (tablename, key, etc...)
     * @param $conditions The delete conditions.
     * @return boolean true on success, false on failure.
     */
    public function delete_object($db_key, array $conditions) {
        return $this->database->delete_object($db_key, $conditions);
    }
}

?>
