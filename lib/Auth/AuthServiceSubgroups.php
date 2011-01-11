<?php
namespace foundry\core\auth;

/**
 * Authentication Service Subgroup Support Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

/**
 * Authentication Service Subgroup Support Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
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
