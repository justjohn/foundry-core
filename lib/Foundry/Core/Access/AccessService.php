<?php
/**
 * Interface for Role management.
 *
 * PHP version 5.3.0
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Access;

/**
 * Interface for Role management.
 *
 * @category  foundry-core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
interface AccessService
{
    /**
     * Add a role definition.
     * 
     * @param string $role The role.
     * 
     * @return Role
     */
    public function addRole(Role $role);

    /**
     * Remove a role.
     * 
     * @param string $role_key The role to remove.
     * 
     * @return boolean
     */
    public function removeRole($role_key);

    /**
     * Get a role.
     * 
     * @param string $role_key The role to get.
     * 
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key);
}
?>
