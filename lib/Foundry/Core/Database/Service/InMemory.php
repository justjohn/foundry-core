<?php
/**
 * An In-Memory imlementation of the Database interface.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Database\Service
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Database\Service;

use Foundry\Core\Model;
use Foundry\Core\Database\DatabaseService;
use Foundry\Core\Exceptions\FieldDoesNotExistException;
use Foundry\Core\Exceptions\ClassDoesNotExistException;

/**
 * The database interface.
 *
 * This interface treats all databases as object-based. For a table-based database
 * this is done by mapping object fields to table fields.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Database\Service
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class InMemory implements DatabaseService {
    /**
     * The data stored in this in-memory database.
     * The format is:
     *      $data[key] => collection of objects
     * 
     * where the collection look like:
     *      $collection[$unique_key] => Model instance
     * 
     * @var array
     */
    private $data = array();
    
    /**
     * Load objects from a table in the database.
     *
     * @param string $classname  The name of the class type to instantiate and load data into.
     * @param string $db_key     The name of the table or document key in the database.
     * @param string $keyfield   The table column to use as the array key for the returned data.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @param array  $sort_rules An array of sorting rules in the form:
     *                             array("field" => "DESC"/"ASC", ...)
     * @param array  $limits     An array with limit conditions either in the form:
     *                              array("count")  or
     *                              array("start", "count")
     * @return object|boolean An array of $classname instances keyed by the $key field (if set),
     *                        false on failure.
     * @throws FieldDoesNotExistException if the key field does not exists in the model class.
     * @throws ClassDoesNotExistException if $classname is not a valid class or does
     *                                    not implement \Foundry\Core\Model.
     */
    public function load_objects($classname, $db_key, $keyfield = "",
                                 array $conditions = array(),
                                 array $sort_rules = array(),
                                 array $limits = array()) {
        if (!class_exists($classname)) {
            return false;
        }
        $data =  $this->data[$db_key];
        $output = $this->match_conditions($data, $conditions, $classname, $keyfield);
        $output = $this->sort_objects($output, $sort_rules);
        $output = $this->limit_objects($output, $limits);
        return $output;
    }
    
    /**
     * Converts a Model implementation to an instance of $classname.
     * 
     * @param Model $model The model to get data from.
     * @param string $classname The classname to instantiate.
     * @return An instance of $classname with the data from $model that matches the
     *         fields in the instance.
     * @throws ClassDoesNotExistException if $classname is not a valid class or does
     *                                    not implement \Foundry\Core\Model.
     */
    private function convertToObject(Model $model, $classname) {
        $object = $this->validateModelClass($classname);
        $fields = $object->getFields();
        if (count($fields) > 0) {
            foreach ($fields as $field=>$type) {
                try {
                    $value = $model->get($field);
                    $object->set($field, $value);
                } catch (FieldDoesNotExistException $ex) {
                    // Do nothing, only copy data over if the field exists.
                }
            }
        }
        return $object;
    }
    
    /**
     * Validates that the given classname is a real class and that it implements
     * \Foundry\Core\Model.
     *
     * @param string $classname The name of the class to check for validity.
     * @return Model A new instance of $classname.
     * @throws ClassDoesNotExistException if $classname is not a valid class or does
     *                                    not implement \Foundry\Core\Model.
     * @see Model
     */
    private function validateModelClass($classname) {
        if (!class_exists($classname)) {
            throw new ClassDoesNotExistException("Unable to convert model to class '$classname': '$classname' does not exists.");
        }
        $object = new $classname();
        if (!($object instanceof Model)) {
            throw new ClassDoesNotExistException("Unable to convert model to class '$classname': '$classname' does not inherit from \Foundry\Core\Model");
        }
        return $object;
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
        return count($this->data[$db_key]);
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
    public function load_object($classname, $db_key,
                                array $conditions = array()) {
        if (!class_exists($classname)) {
            return false;
        }
        $objects = $this->load_objects($classname, $db_key, "", $conditions);
        if (count($objects) > 0) {
            $output = array_shift($objects);
            return $output;
        } else {
            return false;
        }
    }

    /**
     * Writes the values from the object into the database.
     *
     * @param object $object The object with the data to write into the database.
     * @param string $db_key The name of the table in the database.
     * @return boolean true on success, false on failure.
     */
    public function write_object(Model $object, $db_key) {
        $new_id = $this->create_id();
        $this->data[$db_key][$new_id] = $object;
        return true;
    }

    /**
     * Update an object in the database.
     *
     * @param $object The object to write to the database.
     * @param $db_key The database object/table reference.
     * @param $conditions The conditions to match to updated the database
     * @param $updatefields The fields to update.
     * @return boolean true on success, false on failure.
     */
    public function update_object(Model $object, $db_key,
                                  array $conditions, array $updatefields) {
        $objects = $this->data[$db_key];
        if (count($objects) > 0) {
            foreach ($objects as $obj) {
                
            }
        }
    }
    
    /**
     * Delete object[s] from the database.
     *
     * @param $db_key The database object/table reference (tablename, key, etc...)
     * @param $conditions The delete conditions.
     * @return boolean true on success, false on failure.
     */
    public function delete_object($db_key, array $conditions) {
        $data = $this->data[$db_key];
        $to_delete = array();
        if (count($data) > 0) {
            foreach($data as $key=>$model) {
                if ($this->matches_condition($model, $conditions)) {
                    unset($this->data[$db_key][$key]);
                }
            }
        }
    }
    
    
    ////////////////////////////////////////////////////////////////////////////////
    // Functions for handling model data
    
    private function limit_objects(array $array, array $limit) {
        if (count($limit) == 0) return $array;
        
        if (count($limit) >= 2) {
            $start = array_shift($limit);
            $count = array_shift($limit);
        } else {
            $start = 0;
            $count = array_shift($limit);
        }
        $output = array();
        if (count($array) > 0) {
            $i = 0;
            foreach ($array as $key=>$obj) {
                if ($i >= $start && $i < $start+$count) {
                    $output[$key] = $obj;
                }
                $i++;
            }
        }
        return $output;
    }
    
    private function match_conditions(array $data, array $conditions, $classname, $keyfield) {
        $output = array();
        if (count($data) > 0) {
            foreach ($data as $object) {
                $model = $this->convertToObject($object, $classname);
                if ($this->matches_condition($model, $conditions)) {
                    if ($keyfield !== "") {
                        try {
                            $key = $model->get($keyfield);
                            if ($key !== "") {
                                $output[$key] = $model;
                            } else {
                                $output[] = $model;
                            }
                        } catch (FieldDoesNotExistException $ex) {
                            throw new FieldDoesNotExistException(
                                    "Unable to find and key on the '$keyfield' field.");
                        }
                    } else {
                        $output[] = $object;
                    }
                }
            }
        }
        return $output;
    }
    
    private function matches_condition(Model $model, array $conditions) {
        if (count($conditions) > 0) {
            foreach($conditions as $key=>$value) {
                $key = strtolower($key);
                $op = "";
                if (is_array($value)) {
                    $op = $value[0];
                    $value = $value[1];
                }
                $type = $model->getFieldType($key);
                if ($type == Model::INT) $value = intval($value);
                
                $model_value = $model->get($key);
                
                if ($op == '=' || empty($op)) {
                    if ($model_value !== $value) return false;
                } else if ($op == '<') {
                    if (!($model_value < $value)) return false;
                } else if ($op == '>') {
                    if (!($model_value > $value)) return false;
                } else if ($op == '<=') {
                    if (!($model_value <= $value)) return false;
                } else if ($op == '>=') {
                    if (!($model_value >= $value)) return false;
                } else {
                    // Unknown operation
                    return false;
                }
            }
        }
        return true;
    }
    function sort_objects(array $models, array $sort_coniditions) {
        if (count($sort_coniditions) == 0) return $models;
        $keys = array_keys($sort_coniditions);
        $field = array_shift($keys);
        $condition = array_shift($sort_coniditions);
        $output = array();
        if (count($models) > 0) {
            foreach ($models as $key=>$model) {
                $value = $model->get($field);
                if (empty($value)) {
                    $output["___BLANK"][$key] = $model;
                } else {
                    $output[$value][$key] = $model;
                }
            }
        }
        if (strtolower($condition) == 'desc') {
            krsort($output);
        } else {
            ksort($output);
        }
        $sorted_output = array();
        foreach ($output as $arr) {
            $arr_sorted = $this->sort_objects($arr, $sort_coniditions);
            foreach ($arr_sorted as $key=>$obj) {
                $sorted_output[$key] = $obj;
            }
        }
        return $sorted_output;
    }
    
    /**
     * An initialization vector for creating unique ids.
     * @var int
     */
    private $id_counter = 1;
    
    /**
     * Create a unique id for keying data with.
     * @return int
     */
    private function create_id() {
        $id = md5($this->id_counter . microtime());
        $this->id_counter++;
        return $id;
    }
}

?>
