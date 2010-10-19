<?php
/**
 * A model for access roles.
 *
 * @package DataModel
 */

/**
 * A model class for access roles.
 *
 * @package DataModel
 */
class Role {
    /**
     * The key field.
     * @var string
     */
    private $key;
    /**
     * The description field.
     * @var string
     */
    private $description;
    /**
     * The groups field.
     * @var array
     */
    private $groups;

    /**
     * Set the key field.
     * @param string $key
     */
    public function setKey($key) {
        $this->key = $key;
    }
    /**
     * Get the key field.
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Set the description field.
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    /**
     * Get the description field.
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the groups field.
     * @param array $groups
     */
    public function setGroups($groups) {
        $this->groups = $groups;
    }
    /**
     * Get the groups field.
     * @return array
     */
    public function getGroups() {
        return $this->groups;
    }
}
?>