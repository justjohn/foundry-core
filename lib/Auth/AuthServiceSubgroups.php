<?php
/**
 * Authentication Service Subgroup Support Interface
 *
 * @category  foundry-core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Auth;


/**
 * Authentication Service Subgroup Support Interface
 *
 * @category  foundry-core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
*/
interface AuthServiceSubgroups
{
    /**
     * Add a subgroup to a group.
     * @param string $subgroupname The name of the subgroup to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    public function addSubgroupToGroup($subgroupname, $groupname);

    /**
     * Remove a subgroup from a group.
     * @param string $subgroupname The name of the subgroup to remove from the group.
     * @param string $groupname The name of the group to remove the subgroup from.
     */
    public function removeSubgroupFromGroup($subgroupname, $groupname);
}
?>
