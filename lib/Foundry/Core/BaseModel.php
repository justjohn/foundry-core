<?php
/**
 * The base implementation of the data model interface.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core;

use Foundry\Core\Exceptions\MethodDoesNotExistException;
use Foundry\Core\Exceptions\FieldDoesNotExistException;
use Foundry\Core\Exceptions\FieldNameNotValidException;

/**
 * A base implementation of the Model that overrides _call to provide dynamic
 * access to set/get methods.
 */
class BaseModel implements Model {
    /** Fields reserved because methods get... exist in the Model class. */
    public static $reserved_fields = array("fields", "fieldtype", "keyfield");
    
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
    /**
     * Does this model allow custom fields.
     * @var boolean
     */
    private $expandable = false;

    function __construct(array $fields, $key_field, $expandable=false) {
        $this->key_field = strtolower($key_field);
        $this->expandable = $expandable;

        if (!empty($fields)) {
            foreach ($fields as $field_orig=>$type) {
                // get cannonical field name (lowercase)
                $field = strtolower($field_orig);
                if (in_array($field, self::$reserved_fields))
                        throw new FieldNameNotValidException("Field name $field is a reserved name.");
                
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
     * @throws MethodDoesNotExistException If the field being
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
        if (!isset($this->fields[$field]) && !$this->expandable) {
            throw new MethodDoesNotExistException("Field $field does not exist.");
        }

        if ($set) {
            $data = "";
            if (isset($arguments[0])) {
                $data = $arguments[0];
            }
            $this->set($field, $data);
            
        } else {
            if (!isset($this->data[$field])) {
                $this->data[$field] = "";
            }
            return $this->data[$field];
        }
    }

    public function __toString() {
        $string = get_class($this) . " {\n";  //get_called_class()
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
     * @throws FieldDoesNotExistException
     */
    public function set($field, $data) {
        $field = strtolower($field);
        // check field name
        if (!isset($this->fields[$field]) && !$this->expandable) {
            throw new FieldDoesNotExistException("Field $field does not exist.");
        }
        if (!isset($this->fields[$field])) $this->fields[$field] = Model::STR;
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
        if (!isset($this->fields[$field]) && !$this->expandable) {
            throw new \Foundry\Core\Exceptions\FieldDoesNotExistException("Field $field does not exist.");
        }
        
        if (!isset($this->data[$field])) {
            $this->data[$field] = '';
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
     * Update values in this Model from a JSON export of the same Model type.
     * 
     * @param string $json The exported JSON.
     */
    public function fromJSON($json) {
        $array = json_decode($result, true);
        $this->fromArray($array);
    }
    /**
     * Update values in this Model from a XML export of the same Model type.
     * 
     * @param string $xml The exported XML.
     */
    public function fromXML($xml) {
        
    }
    /**
     * Update values in this Model from an Array export of the same Model type.
     * 
     * @param array $array The exported array.
     */
    public function fromArray(array $array) {
        if (count($array) > 0) {
            foreach ($array as $key=>$value) {
                $this->set($key, $value);
            }
        }
    }
    
    /**
     * Get all the fields in the object except (optinally) the index field.
     *
     * @param boolean $include_index Whether to include the model index
     *                               field in the returned fields.
     * 
     * @return array An array of field types keyed by field name.
     */
    public function getFields($include_index = true) {
        if ($include_index) {
            return $this->fields;
        } else {
            return array_diff_key(
                $this->fields,
                array("id" => "id")
            );
        }
    }

    /**
     * Get the type of a field.
     * See the list of consts in the Model class.
     *
     * @param string $fieldname
     * 
     * @return mixed false if the field doesn't exist, the field type
     *               as a string if it does exist.
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
    
    public function isExpandable() {
        return $this->expandable;
    }
}

?>
