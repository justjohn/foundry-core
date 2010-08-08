<?php
/**
 * A model for config options.
 *
 * @package DataModel
 */

/**
 * A model class for config options.
 *
 * @package DataModel
 */
class Option {
    /**
     * The name field.
     * @var string
     */
    public $name;
    /**
     * The value field.
     * @var string
     */
    public $value;
    /**
     * The id field.
     * @var integer
     */
    public $id;

    /**
     * Set the name field.
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }
    /**
     * Get the name field.
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value field.
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }
    /**
     * Get the value field.
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Set the id field.
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    /**
     * Get the id field.
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
}
?>
