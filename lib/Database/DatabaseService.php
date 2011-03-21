<?php
namespace foundry\core\database;

use foundry\core\Model;

/**
 * The database interface.
 *
 * This interface treats all databases as object-based. For a table-based database
 * this is done by mapping object fields to table fields.
 */
interface DatabaseService {
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
     */
    public function load_objects($classname,
                                 $db_key,
                                 $keyfield = "",
                                 array $conditions = array(),
                                 array $sort_rules = array(),
                                 array $limits = array());

    /**
     * Get a count of objects in a table.
     *
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return integer|boolean The count on success, false on failure.
     */
    public function count_objects($db_key,
                                  array $conditions = array());

    /**
     * Load an object from the database.
     *
     * @param string $classname The name of the class type to instantiate and load data into.
     * @param string $db_key The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname,
                                $db_key,
                                array $conditions = array()) ;

    /**
     * Writes the values from the object into the database.
     *
     * @param object $object The object with the data to write into the database.
     * @param string $db_key The name of the table in the database.
     * @return boolean true on success, false on failure.
     */
    public function write_object(Model $object,
                                 $db_key);

    /**
     * Update an object in the database.
     *
     * @param $object The object to write to the database.
     * @param $db_key The database object/table reference.
     * @param $conditions The conditions to match to updated the database
     * @param $updatefields The fields to update.
     * @return boolean true on success, false on failure.
     */
    public function update_object(Model $object,
                                  $db_key,
                                  array $conditions,
                                  array $updatefields);

    /**
     * Delete object[s] from the database.
     *
     * @param $db_key The database object/table reference (tablename, key, etc...)
     * @param $conditions The delete conditions.
     * @return boolean true on success, false on failure.
     */
    public function delete_object($db_key,
                                  array $conditions);

}

?>
