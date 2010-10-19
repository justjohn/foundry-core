<?php
/**
 * Interface for Role management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

/**
 * Interface for Role management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */
interface AccessService {
    /**
     * Add a role definition.
     * @param string $role_key The name of the role.
     * @param string $role_description The description of the role.
     * @param array $role_groups The groups assigned to the role.
     * @return boolean
     */
    public function addRole($role_key, $role_description, array $role_groups);

    /**
     * Remove a role.
     * @param string $role_key The role to remove.
     * @return boolean
     */
    public function removeRole($role_key);

    /**
     * Get a role.
     * @param string $role_key The role to get.
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key);
}
?>
