<?php
/**
 * Database API and service loader.
 * 
 * This component contains the database API and code for loading database
 * services from the Database/Services directory.
 * 
 * Currently there are three available services:
 * 1. Mysql: Load data from a MySQL database.
 * 2. Mongo: Load data from a MongoDB database.
 * 3. InMemory: Stores data in memory until the end of script execution.
 *              The reference implementation; primarily for testing other components.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Database
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Database;

use Foundry\Core\Core;
use Foundry\Core\Model;
use Foundry\Core\Service;
use Foundry\Core\Exceptions\ServiceLoadException;
use Foundry\Core\Logging\Log;

Core::requires('\Foundry\Core\Logging\Log');

/**
 * The Database API.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Database
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Database
{
    /**
     * The options required to instantiate a database component.
     * @var array
     */
    public static $required_options = array("service", "service_config");

    /**
     * The database service.
     * @var DatabaseService
     */
    private $database;
    
    /**
     * Create a Database component.
     * 
     * @param array $config The database configuration.
     */
    function __construct(array $config)
    {
        Service::validate($config, self::$required_options);
        $db_service = $config["service"];
        $db_config = $config["service_config"];
        if (!class_exists($db_service)) {
            Log::error("Database::__construct", "Unable to load database class '$db_service'.");
            throw new ServiceLoadException("Unable to load database class '$db_service'.");
        }
        $this->database = new $db_service($db_config);
    }
    
    /**
     * Load objects from a table in the database.
     *
     * @param string $classname  The name of the class type to instantiate and load data into.
     * @param string $db_key  The name of the table in the database.
     * @param string $key        The table column to key the returned array with.
     * @param array  $conditions The conditions for the database query in an array of the format:
     *                              array(
     *                                  field => value  OR
     *                                  field => array(operator, value),
     *                                  'or' => See below
     *                                  ...
     *                              )
     *                            Where operator is '<' ,'>', '!=' or '='. If an operator is not
     *                            provided it is assumed to be '='.
     * 
     *                              array(
     *                                  'or' => array(
     *                                      array( conditions ),
     *                                      array( conditions ),
     *                                      ...
     *                                  )
     *                              )
     *                            If the field is 'or' and value is an array, value will
     *                            be treated as a set of conditinos for the 'or'.
     * 
     * @param array  $sort_rules An array of sorting rules in the form:
     *                             array("field" => "DESC"/"ASC", ...)
     * @param array  $limits     An array with limit conditions either in the form:
     *                              array("count")  or
     *                              array("start", "count")
     * 
     * @return object|boolean An array of $classname instances keyed by the $key field (if set),
     *                        false on failure.
     */
    public function load_objects($classname, $db_key, $key = "", array $conditions = array(), array $sort_rules = array(), array $limits = array())
    {
        return $this->database->load_objects($classname, $db_key, $key, $conditions, $sort_rules, $limits);
    }

    /**
     * Get a count of objects in a table.
     *
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array of the format:
     *                              array(
     *                                  field => value  OR
     *                                  field => array(operator, value)
     *                              )
     * @return integer|boolean The count on success, false on failure.
     */
    public function count_objects($db_key, array $conditions = array())
    {
        return $this->database->count_objects($db_key, $conditions);
    }

    /**
     * Load an object from the database.
     *
     * @param string $classname The name of the class type to instantiate and load data into.
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array of the format:
     *                              array(
     *                                  field => value  OR
     *                                  field => array(operator, value)
     *                              )
     * 
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname, $db_key, array $conditions = array(), array $sort_rules = array())
    {
        return $this->database->load_object($classname, $db_key, $conditions, $sort_rules);
    }

    /**
     * Writes the values from the object into the given database table.
     *
     * @param Model $object The object with the data to write into the database.
     * @param string $db_key The name of the table in the database.
     * 
     * @return boolean Returns true on success, false on failure.
     */
    public function write_object(Model $object, $db_key)
    {
        return $this->database->write_object($object, $db_key);
    }

    /**
     * Update an existing object in the database.
     *
     * @param Model  $object The object data to update with.
     * @param string $db_key The name of the table to update.
     * @param array  $conditions The conditions for the database query in an array of the format:
     *                              array(
     *                                  field => value  OR
     *                                  field => array(operator, value)
     *                              )
     * @param array  $updatefields An array of fields to update in each object.
     * 
     * @return boolean Returns true on success, false on failure.
     */
    public function update_object(Model $object, $db_key, array $conditions, array $updatefields)
    {
        return $this->database->update_object($object, $db_key, $conditions, $updatefields);
    }

    /**
     * Delete object[s] from the database.
     *
     * @param $db_key The database object/table reference (tablename, key, etc...)
     * @param array  $conditions The conditions for the database query in an array of the format:
     *                              array(
     *                                  field => value  OR
     *                                  field => array(operator, value)
     *                              )
     * 
     * @return boolean Returns true on success, false on failure.
     */
    public function delete_object($db_key, array $conditions)
    {
        return $this->database->delete_object($db_key, $conditions);
    }
}

?>
