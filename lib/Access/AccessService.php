<?php
/**
 * Interface for Role management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

namespace foundry\core\access;

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
     * @param string $role The role.
     * @return Role
     */
    public function addRole(Role $role);

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
