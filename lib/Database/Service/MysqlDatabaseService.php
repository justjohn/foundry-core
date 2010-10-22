<?php

class MysqlDatabaseService extends mysqli implements DatabaseService {
    public static $required_options = array("host",
                                            "username",
                                            "password",
                                            "db");

    public function __construct($options) {
        Service::validate($options, self::$required_options);
        @parent::__construct($options["host"],
                             $options["username"],
                             $options["password"],
                             $options["db"]);

        if (mysqli_connect_error()) {
            throw new ServiceConnectionException("Unable to connect to the MySQL database.");
        }
    }

    private function get_fields($tablename, &$fields, &$fieldtype) {
        // Get table field types
        $query = "SHOW COLUMNS FROM $tablename";
        $result = parent::query($query);

        $types = Array("int"=>"i", "double"=>"d", "default"=>"s");

        if ($result !== false) {
            while ($row = $result->fetch_assoc()) {
                $field = $row["Field"];

                if (strpos($row["Type"], "int") !== false) {
                    $row["Type"] = "int";
                }
                if (isset($types[$row["Type"]])) {
                    $fieldtype[$field] = $types[$row["Type"]];
                } else {
                    $fieldtype[$field] = $types["default"];
                }
                $fields[] = $field;
            }
        }
    }

    private function create_sort($sort_rules, &$sort) {
        if (is_array($sort_rules) && count($sort_rules) > 0) {
            // Build where caluse
            $sort = Array();
            foreach($sort_rules as $key=>$value) {
                $sort[] = " `$key` $value ";
            }
            $sort = "ORDER BY " . implode(", ", $sort);
        } else {
            $sort = '';
        }
    }

    private function create_limit($limits, &$limit) {
        if (is_array($limits) && count($limits) == 1) {
            $limit = "LIMIT " . intval($limits[0]);
        } else if (count($limits) == 2) {
            $limit = "LIMIT " . intval($limits[0]).", ".intval($limits[1]);
        } else {
            $limit = "";
        }
    }

    private function create_conditions($conditions, &$condition) {
        if (is_array($conditions) && count($conditions) > 0) {
            // Build where caluse
            $condition = Array();
            foreach($conditions as $key=>$value) {
                $op = "=";
                if (is_array($value)) {
                    $op = $value[0];
                    $value = $value[1];
                }
                $condition[] = " `$key` $op ? ";
            }
            $condition = "WHERE " . implode(" AND ", $condition);
        } else {
            $condition = "";
        }
    }
    private function bind_conditions($conditions, $fieldtype, &$stmt) {
        if (is_array($conditions) && count($conditions) > 0) {
            // Build binding statement
            $bindvalues = "";
            $bindingtype = "";
            foreach($conditions as $field=>$value) {
                if (is_array($value)) {
                    $value = $value[1];
                }
                $setval = "get_cond_$field";
                $$setval = $value;
            }
            $this->add_condition_bind($conditions, $fieldtype, $bindingtype, $bindvalues);
            $bindingstmt = "\$stmt->bind_param(\"$bindingtype\"$bindvalues);";
            //print($bindingstmt . "<br />");
            eval($bindingstmt);
        }
    }
    private function add_condition_bind($conditions, $fieldtype, &$bindingtype, &$bindvalues) {
        if (is_array($conditions) && count($conditions) > 0) {
            // Build binding statement
            foreach($conditions as $field=>$value) {
                $bindingtype .= $fieldtype[$field];
                $bindvalues .= ", \$get_cond_$field";
            }
        }
    }

    // Load a class with the fields from a db table
    public function load_objects($classname, $tablename, $key = "", array $conditions = array(), array $sort_rules = array(), array $limits = array()) {
        if (!class_exists($classname)) {
            return false;
        }

        // Get table field types
        $fields = Array();
        $fieldtype = Array();

        $this->get_fields($tablename, $fields, $fieldtype);
        // Create where clause and binding statement
        $bindingstmt = "";
        $condition = "";

        $this->create_conditions($conditions, $condition);

        $sort = '';
        $this->create_sort($sort_rules, $sort);

        $limit = "";
        $this->create_limit($limits, $limit);

        $query = "SELECT * FROM $tablename $condition $sort $limit";
        //print("Query: $query<br />");
        $stmt = parent::prepare($query);

        $this->bind_conditions($conditions, $fieldtype, $stmt);

        $stmt->execute();

        $allfields = Array();
        foreach ($fields as $fld) {
            $allfields[] = "\$field_$fld";
        }
        $resultbinding = "\$stmt->bind_result(" . implode(",", $allfields) . ");";
        //print("$resultbinding<br />");
        eval($resultbinding);

        $objectarray = Array();
        while($stmt->fetch()) {
            $object = new $classname();
            foreach($fields as $field) {
                $fieldName = strtoupper(substr($field, 0, 1)).substr($field, 1);
                $method = "set".$fieldName;
                $methodcall = "\$object->$method(\$field_$field);";
                //print($methodcall."<br />");
                eval($methodcall);
            }
            if ($key != "") {
                $keyval = "";
                $fieldName = "\$keyval = \$object->get".strtoupper(substr($key, 0, 1)).substr($key, 1) . "();";
                //print($fieldName."<br />");
                eval($fieldName);
                $objectarray[$keyval] = $object;
            } else {
                $objectarray[] = $object;
            }
        }
        $stmt->close();
        return $objectarray;
    }

    /**
     * Load an object from the database.
     *
     * @param <type> $classname The name of the class type to instantiate and load data into.
     * @param <type> $tablename The name of the table in the database.
     * @param <type> $conditions The conditions for the databse query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname, $tablename, array $conditions = array()) {
        $objects = $this->load_objects($classname, $tablename, "", $conditions);
        if (count($objects) > 0) {
            return array_pop($objects);
        }
        return false;
    }

    public function count_objects($tablename, $conditions = array()) {
        // Get table field types
        $fields = Array();
        $fieldtype = Array();

        $this->get_fields($tablename, $fields, $fieldtype);

        // Create where clause and binding statement
        $bindingstmt = "";
        $condition = "";

        $this->create_conditions($conditions, $condition);

        $query = "SELECT * FROM $tablename $condition";
        //print("Query: $query<br />");
        $stmt = parent::prepare($query);

        $this->bind_conditions($conditions, $fieldtype, $stmt);

        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->free_result();
        $stmt->close();

        return $num_rows;
    }

    /**
     * Writes the values from the object into the given database table.
     *
     * @param object $object The object with the data to write into the database.
     * @param string $tablename The name of the table in the database.
     * @return boolean true on success, false on failure. 
     */
    public function write_object(Model $object, $tablename) {

        // Get table field types
        $fields = Array();
        $fieldtype = Array();
        $this->get_fields($tablename, $fields, $fieldtype);

        if (count($fields) > 0) {
            $bindingtype = "";
            $bindvalues = "";
            $q = Array();
            foreach ($fields as $field) {
                $method = "get".strtoupper(substr($field, 0, 1)).substr($field, 1);
                $methodcall = "\$set_$field = \$object->$method();";
                //print("$methodcall<br />");
                eval($methodcall);
                $bindingtype .= $fieldtype[$field];
                $bindvalues .= ", \$set_$field";
                $q[] = "?";
            }
            // Set up query
            $query = "INSERT INTO $tablename (`".implode("`, `", $fields)."`) VALUES (".implode(", ", $q).");";
            $stmt = parent::prepare($query);
            //print_a($stmt);
            //print($query . "<br />");

            // Bind values to the query
            $bindingstmt = "\$stmt->bind_param(\"$bindingtype\"$bindvalues);";
            //print($bindingstmt . "<br /><br />");
            eval($bindingstmt);

            // Execure Query
            if ($stmt->execute() === false) {
                print("Error executing query: " . mysqli_stmt_error($stmt) . "<br />");
                return false;
            }
            return true;
        } else {
            // Error
            return false;
        }

    }
    public function update_object(Model $object, $tablename, array $conditions, array $updatefields) {

        // Get table field types
        $fields = Array();
        $fieldtype = Array();
        $this->get_fields($tablename, $fields, $fieldtype);

        // Create where clause and binding statement
        $bindingstmt = "";
        $condition = "";

        $this->create_conditions($conditions, $condition);

        $updates = "";
        $bindingtype = "";
        $bindvalues = "";
        if (count($updatefields) > 0) {
            foreach ($updatefields as $field) {
                $method = "get".strtoupper(substr($field, 0, 1)).substr($field, 1);
                $values[] = "$field=?";
                $methodcall = "\$set_$field = \$object->$method();";
                eval($methodcall);
                $bindingtype .= $fieldtype[$field];
                $bindvalues .= ", \$set_$field";
            }
            $updates = implode(", ", $values);
        }
        if (count($conditions) > 0) {
            foreach($conditions as $field=>$value) {
                $setval = "get_cond_$field";
                $$setval = $value;
            }
        }
        $this->add_condition_bind($conditions, $fieldtype, $bindingtype, $bindvalues);

        $bindingstmt = "\$stmt->bind_param(\"$bindingtype\"$bindvalues);";

        $query = "UPDATE $tablename SET $updates $condition;";
        //print($query."<br />");
        $stmt = parent::prepare($query);

        eval($bindingstmt);
        //print($bindingstmt."<br />");

        if ($stmt->execute() === false) {
            //print("Error executing query: " . mysqli_stmt_error($stmt) . "<br />");
            return false;
        }
        return true;
    }

    public function delete_object($tablename, array $conditions) {

        // Get table field types
        $fields = Array();
        $fieldtype = Array();
        $this->get_fields($tablename, $fields, $fieldtype);

        // Set conditions
        $condition = "";
        if (count($conditions) > 0) {
            $condition = Array();
            foreach($conditions as $key=>$value) {
                $op = "=";
                if (is_array($value)) {
                    $op = $value[0];
                    $value = $value[1];
                }
                $condition[] = " $key $op ? ";
            }
            $condition = "WHERE " . implode(" AND ", $condition);
        }

        // Build query
        $query = "DELETE FROM $tablename $condition;";

        $stmt = parent::prepare($query);

        // Bind values to query
        if (count($conditions) > 0) {
            $bindvalues = "";
            $bindingtype = "";
            foreach($conditions as $field=>$value) {
                if (is_array($value)) {
                    $value = $value[1];
                }
                $bindingtype .= $fieldtype[$field];
                $bindvalues .= ", \$set_$field";
                $setval = "set_$field";
                $$setval = $value;
            }
            $bindingstmt = "\$stmt->bind_param(\"$bindingtype\"$bindvalues);";
            eval($bindingstmt);
        }

        if ($stmt->execute() === false) {
            //print("Error executing query: " . mysqli_stmt_error($stmt) . "<br />");
            return false;
        }
        return true;
    }

}

?>
