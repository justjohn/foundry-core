<?php
/**
 * Authentication Service Subgroup Support Interface
 *
 * @package   foundry\core\auth
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 */
namespace foundry\core\auth;


/**
 * Authentication Service Subgroup Support Interface
 *
 * @package   foundry\core\auth
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
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
