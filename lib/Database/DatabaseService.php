<?php

interface DatabaseService {
    /**
     * Load objects from a table in the database.
     *
     * @param string $classname  The name of the class type to instantiate and load data into.
     * @param string $tablename  The name of the table in the database.
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
    public function load_objects($classname, $tablename, $key = "", $conditions = array(), $sort_rules = array(), $limits = array());

    /**
     * Get a count of objects in a table.
     *
     * @param string $tablename The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return integer|boolean The count on success, false on failure.
     */
    public function count_objects($tablename, $conditions = array());

    /**
     * Load an object from the database.
     *
     * @param string $classname The name of the class type to instantiate and load data into.
     * @param string $tablename The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname, $tablename, $conditions = array()) ;

    /**
     * Writes the values from the object into the given database table.
     *
     * @param object $object The object with the data to write into the database.
     * @param string $tablename The name of the table in the database.
     * @return boolean true on success, false on failure.
     */
    public function write_object($object, $tablename);

    public function update_object($object, $tablename, $conditions, $updatefields);

    public function delete_object($tablename, $conditions);

}

?>
