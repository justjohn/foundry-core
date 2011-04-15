<?php
/**
 * The data model interface used for defining objects in the Core Library.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core;

/**
 * A database model class.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since 1.0.0
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
     * 
     * @return mixed false if the field doesn't exist, the field type as a string if it does exist.
     */
    function getFieldType($fieldname);

    /**
     * Get the key field.
     *
     * @return string The key field.
     */
    function getKeyField();
    
    /**
     * Set a field in the model.
     * 
     * @param string $field The field to set.
     * @param object $data The data to set in the field.
     * 
     * @throws \Foundry\Core\Exceptions\FieldDoesNotExistException
     */
    function set($field, $data);
    
    /**
     * Get the value of a single field.
     * 
     * @param string $field The name of the field to get.
     * 
     * @throws \Foundry\Core\Exceptions\FieldDoesNotExistException
     */
    public function get($field);
    
    /**
     * Get the model as an array.
     */
    public function asArray();
    
    /**
     * Get the model as JSON.
     */
    public function asJSON();

    /**
     * Get the model as XML.
     */
    public function asXML();
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
        $this->key_field = strtolower($key_field);

        if (!empty($fields)) {
            foreach ($fields as $field_orig=>$type) {
                // get cannonical field name (lowercase)
                $field = strtolower($field_orig);
                $this->fields[$field] = $type;
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
                // initialize data with the correct data type
                $this->data[$field] = $init;
            }
        }
    }

    /**
     * Handle undefined functions (primarily set/get calls).
     * 
     * @param string $name The name of the function to handle.
     * @param array $arguments The function arguments.
     * 
     * @throws \Foundry\Core\Exceptions\MethodDoesNotExistException If the field being
     *         referenced by a set/get function does not exist.
     */
    function __call($name, $arguments) {

        $set_pattern = "/set([A-Z].*)/";
        $get_pattern = "/get([a-zA-Z].*)/";

        $set = false;
        if (preg_match($set_pattern, $name, $matches) > 0) {
            // set call
            $set = true;
        } else if (preg_match($get_pattern, $name, $matches) > 0) {
        } else {
            $matches[1] = $name;
        }

        $field = strtolower($matches[1]);

        // check field name
        if (!isset($this->fields[$field])) {
            throw new \Foundry\Core\Exceptions\MethodDoesNotExistException("Field $field does not exist.");
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
        $string = "{\n";  //get_called_class()
        if (!empty($this->fields)) {
            foreach ($this->fields as $field=>$type) {
                $string .= "\t$field : \"" . str_replace('"', '\\"', $this->data[$field]) . "\"\n";
            }
        }
        $string .= "}\n";
        return $string;
    }


    /**
     * Set a field in the model.
     * 
     * @param string $field The field to set.
     * @param object $data The data to set in the field.
     * 
     * @throws \Foundry\Core\Exceptions\FieldDoesNotExistException
     */
    public function set($field, $data) {
        $field = strtolower($field);
        // check field name
        if (!isset($this->fields[$field])) {
            throw new \Foundry\Core\Exceptions\FieldDoesNotExistException("Field $field does not exist.");
        }
        // Cast data to the appropriate type
        switch ($this->fields[$field]) {
            case Model::BOOL:
                $data = (boolean)$data;
                break;
            case Model::INT:
                $data = (int)$data;
                break;
            case Model::LST:
                $data = (array)$data;
                break;
            case Model::STR:
            default:
                $data = (string)$data;
        }
        $this->data[$field] = $data;
    }
    
    /**
     * Get the value of a single field.
     * 
     * @param string $field The name of the field to get.
     * 
     * @throws \Foundry\Core\Exceptions\FieldDoesNotExistException
     */
    public function get($field) {
        $field = strtolower($field);
        // check field name
        if (!isset($this->fields[$field])) {
            throw new \Foundry\Core\Exceptions\FieldDoesNotExistException("Field $field does not exist.");
        }

        return $this->data[$field];
    } 
    
    /**
     * Get the model as an array.
     */
    public function asArray() {
        return $this->data;
    }
    
    /**
     * Get the model as JSON.
     */
    public function asJSON() {
        Core::requires('\Foundry\Core\Utilities\Renderer');
        
        return \Foundry\Core\Utilities\Renderer::asJSON($this);
    }

    /**
     * Get the model as XML.
     */
    public function asXML() {
        Core::requires('\Foundry\Core\Utilities\Renderer');
        
        return \Foundry\Core\Utilities\Renderer::asXML($this);
    }

    /**
     * Get all the fields in the object.
     *
     * @return array An array of field names.
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Get the type of a field.
     * See the list of consts in the Model class.
     *
     * @param string $fieldname
     * 
     * @return mixed false if the field doesn't exist, the field type as a string if it does exist.
     */
    public function getFieldType($fieldname) {
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
    public function getKeyField() {
        return $this->key_field;
    }
}

?>
