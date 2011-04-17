<?php
/**
 * Authentication Service Interface
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Auth;

/**
 * Authentication Service Interface
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
*/
interface AuthService
{

    // User methods

    /**
     * Authenticate a user.
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password);

    /**
     * Change a user password.
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password);

    /**
     * Check to see if a user exists.
     * @param string $username The username to check.
     * @return boolean
     */
    public function userExists($username);

    /**
     * Returns an array of all the users or arrays keyed by username.
     * @return array|boolean
     */
    public function getUsers();

    /**
     * Get a user's information as an array.
     * @param string $username
     * @return User
     */
    public function getUser($username);

    /**
     * Get an array of groups the given user is a member of.
     * @param string $username
     * @return array
     */
    public function getUserGroups($username);

    /**
     * Add a user.
     * @param User $user The attributes of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password);

    /**
     * Update a user.
     * @param User $user The attributes of the user to update.
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser($user);

    /**
     * Delete a user.
     * @param string $username The username to delete.
     * @return boolean true on success, false on failure
     */
    public function deleteUser($username);
    
    // Group Methods

    /**
     * Check to see if a group exists.
     * @param string $groupname The group name to check.
     * @return boolean
     */
    public function groupExists($groupname);

    /**
     * Returns an array of all the groups keyed by group name.
     *
     * This call should be avoided if at all possible since it may require the
     * auth service to make a seperate call for each group. For example, if you
     * have 100 groups in a Crowd directory; the only way to get complete
     * information on each group is a seperate getGroup() call to the API. This
     * results in 101 SOAP calls.
     *
     * @return array|boolean
     */
    public function getGroups();


    /**
     * Returns an array of Group names keyed by group name.
     * @return array an group names (strings) keyed by group names.
     */
    public function getGroupNames();

    /**
     * Get a group's members.
     * @param array $groupname
     * @return array
     */
    public function getGroup($groupname);

    /**
     * Add a group.
     * @param Group $group The group to add.
     * @return boolean true on sucess, false on failure.
     */
    public function addGroup($group);

    /**
     * Delete a group.
     * @param string $groupname The name of the group to delete.
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname);

    /**
     * Add a user to a group.
     * @param string $username The username to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    public function addUserToGroup($username, $groupname);

    /**
     * Remove a user from a group.
     * @param string $username The username to remove from the group.
     * @param string $groupname The name of the group to remove the user from.
     */
    public function removeUserFromGroup($username, $groupname);

}
?>
