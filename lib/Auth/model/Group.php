<?php
/**
 * A model for user groups.
 *
 * @package DataModel
 */

/**
 * A model class for user groups.
 *
 * @package DataModel
 */
class Group {
    /**
     * The name field.
     * @var string
     */
    private $name;
    /**
     * The description field.
     * @var string
     */
    private $description;
    /**
     * The users field.
     * @var array
     */
    private $users;
    /**
     * The subgroups field.
     * @var array
     */
    private $subgroups;

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
     * Set the users field.
     * @param array $users 
     */
    public function setUsers($users) {
        $this->users = $users;
    }
    /**
     * Get the users field.
     * @return array
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Set the subgroups field.
     * @param array $subgroups 
     */
    public function setSubgroups($subgroups) {
        $this->subgroups = $subgroups;
    }
    /**
     * Get the subgroups field.
     * @return array
     */
    public function getSubgroups() {
        return $this->subgroups;
    }

    /**
     * Add a user to the group.
     * @param string $user
     */
    public function addUser($user) {
        $this->users[$user] = $user;
    }
}
?>