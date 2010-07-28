<?php
/**
 * Authentication Service Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

/**
 * Authentication Service Interface
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
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
     * @return array
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
     * @param User $user The details of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password);

    /**
     * Delete a user.
     * @param string $username The username to delete.
     * @return boolean true on success, false on failure
     */
    public function deleteUser($username);
    
    // Group Methods

    /**
     * Returns an array of all the groups keyed by group name.
     * @return array|boolean
     */
    public function getGroups();

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
     * Add a subgroup to a group.
     * @param string $subgroupname The name of the subgroup to add to the group.
     * @param string $groupname The name of the group to add the user to.
     */
    //public function addSubgroupToGroup($subgroupname, $groupname);

    /**
     * Remove a user from a group.
     * @param string $username The username to remove from the group.
     * @param string $groupname The name of the group to remove the user from.
     */
    public function removeUserFromGroup($username, $groupname);

    /**
     * Remove a subgroup from a group.
     * @param string $subgroupname The name of the subgroup to remove from the group.
     * @param string $groupname The name of the group to remove the subgroup from.
     */
    //public function removeSubgroupFromGroup($subgroupname, $groupname);

    /**
     * Check for a single sign on token.
     * @return boolean True if logged into a SSO, false if not.
     */
    public function checkSSO();

    /**
     * Logout of the SSO system.
     */
    public function logoutSSO();
}
?>
