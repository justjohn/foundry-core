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
    
    /**
     * Update values in this Model from a JSON export of the same Model type.
     * 
     * @param string $json The exported JSON.
     */
    public function fromJSON($json);
    /**
     * Update values in this Model from a XML export of the same Model type.
     * 
     * @param string $xml The exported XML.
     */
    public function fromXML($xml);
    /**
     * Update values in this Model from an Array export of the same Model type.
     * 
     * @param array $xml The exported array.
     */
    public function fromArray(array $array);
}

?>
