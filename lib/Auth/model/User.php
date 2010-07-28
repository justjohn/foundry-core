<?php
/**
 * A model for users.
 *
 * @package DataModel
 */

/**
 * A model class for users.
 *
 * @package DataModel
 */
class User {
    /**
     * The username field.
     * @var string
     */
    private $username;
    /**
     * The displayName field.
     * @var string
     */
    private $displayName;
    /**
     * The email field.
     * @var string
     */
    private $email;
    /**
     * The firstName field.
     * @var string
     */
    private $firstName;
    /**
     * The surname field.
     * @var string
     */
    private $surname;

    /**
     * Set the username field.
     * @param string $username 
     */
    public function setUsername($username) {
        $this->username = $username;
    }
    /**
     * Get the username field.
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set the displayName field.
     * @param string $displayName 
     */
    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }
    /**
     * Get the displayName field.
     * @return string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Set the email field.
     * @param string $email 
     */
    public function setEmail($email) {
        $this->email = $email;
    }
    /**
     * Get the email field.
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the firstName field.
     * @param string $firstName 
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }
    /**
     * Get the firstName field.
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set the surname field.
     * @param string $surname 
     */
    public function setSurname($surname) {
        $this->surname = $surname;
    }
    /**
     * Get the surname field.
     * @return string
     */
    public function getSurname() {
        return $this->surname;
    }
}
?>