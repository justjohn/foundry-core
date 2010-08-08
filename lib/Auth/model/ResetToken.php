<?php
/**
 * A model for password reset tokens.
 *
 * @package DataModel
 */

/**
 * A model class for password reset tokens.
 *
 * @package DataModel
 */
class ResetToken {
    /**
     * The token field.
     * @var string
     */
    public $token;
    /**
     * The username field.
     * @var string
     */
    public $username;
    /**
     * The expiration field.
     * @var integer
     */
    public $expiration;
    /**
     * The id field.
     * @var integer
     */
    public $id;

    /**
     * Set the token field.
     * @param string $token 
     */
    public function setToken($token) {
        $this->token = $token;
    }
    /**
     * Get the token field.
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

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
     * Set the expiration field.
     * @param integer $expiration 
     */
    public function setExpiration($expiration) {
        $this->expiration = $expiration;
    }
    /**
     * Get the expiration field.
     * @return integer
     */
    public function getExpiration() {
        return $this->expiration;
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
