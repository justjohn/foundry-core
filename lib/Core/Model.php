<?php
/**
 * A database model class.
 */
interface Model {
    const STR  = "string";
    const INT  = "int";
    const LST  = "list";
    const BOOL = "boolean";

    /**
     * Get all the fields in the database.
     * 
     * @return array An array of field names.
     */
    function getFields();

    /**
     * Get the type of a field.
     * See the list of consts in the Model class.
     *
     * @param string $fieldname
     * @return mixed false if the field doesn't exist, the field type as a string if it does exist.
     */
    function getFieldType($fieldname);

    /**
     * Get the key field.
     *
     * @return string The key field.
     */
    function getKeyField();
}

/**
 * A base implementation of the Model that overrides _call to provide dynamic
 * access to set/get methods.
 */
class BaseModel implements Model {
    /**
     * The key field.
     * @var string
     */
    private $key_field;
    /**
     * The data model field names.
     * @var array
     */
    private $fields = array();
    /**
     * The data stored in this model.
     * @var array
     */
    private $data = array();

    function __construct(array $fields, $key_field) {
        $this->fields = $fields;
        $this->key_field = $key_field;

        if (!empty($fields)) {
            foreach ($fields as $field=>$type) {
                switch ($type) {
                    case Model::BOOL:
                        $init = false;
                        break;
                    case Model::INT:
                        $init = 0;
                        break;
                    case Model::LST:
                        $init = array();
                        break;
                    case Model::STR:
                    default:
                        $init = "";
                }
                $this->data[$field] = $init;
            }
        }
    }

    function __call($name, $arguments) {

        $set_pattern = "/set([A-Z].*)/";
        $get_pattern = "/get([A-Z].*)/";

        if (preg_match($set_pattern, $name, $matches) > 0) {
            // set call
            $set = true;
        } else if (preg_match($get_pattern, $name, $matches) > 0) {
            // get call
            $set = false;
        } else {
            throw new MethodDoesNotExistException("Method $name does not exist.");
        }

        $field = $matches[1];
        $field = strtolower(substr($field, 0, 1)) . substr($field, 1);

        // check field name
        if (!isset($this->fields[$field])) {
            throw new MethodDoesNotExistException("Field $field does not exist.");
        }

        if ($set) {
            $data = "";
            if (isset($arguments[0])) {
                $data = $arguments[0];
            }
            $this->set($field, $data);
            
        } else {
            return $this->data[$field];
        }
    }

    public function __toString() {
        $string = get_called_class() . " {\n";
        if (!empty($this->fields)) {
            foreach ($this->fields as $field=>$type) {
                $string .= "\t$field ($type) = " . $this->data[$field] . "\n";
            }
        }
        $string .= "}\n";
        return $string;
    }


    private function set($field, $data) {
        // Cast data to the appropriate type
        switch ($this->fields[$field]) {
            case Model::BOOL:
                $data = (boolean)$data;
            case Model::INT:
                $data = (int)$data;
            case Model::LST:
                $data = (array)$data;
            case Model::STR:
            default:
        }
        $this->data[$field] = $data;
    }

    /**
     * Get all the fields in the database.
     *
     * @return array An array of field names.
     */
    function getFields() {
        return $this->fields;
    }

    /**
     * Get the type of a field.
     * See the list of consts in the Model class.
     *
     * @param string $fieldname
     * @return mixed false if the field doesn't exist, the field type as a string if it does exist.
     */
    function getFieldType($fieldname) {
        if (isset($this->fields[$fieldname])) {
            return $this->fields[$fieldname];
        } else {
            return false;
        }
    }

    /**
     * Get the key field.
     *
     * @return string The key field.
     */
    function getKeyField() {
        return $this->key_field;
    }
}

?>
