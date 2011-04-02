<?php
/**
 * Interface for Role management.
 *
 * @package   foundry\core\access
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 */

namespace foundry\core\access;

/**
 * Interface for Role management.
 *
 * @package   foundry\core\access
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
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
