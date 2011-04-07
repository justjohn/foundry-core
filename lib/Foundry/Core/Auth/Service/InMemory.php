<?php
/**
 * In-memory Authentication Service Implementation
 *
 * This file contains the logic required to authenticate against an
 * in-memory (temporary or session limited) service. Mainly used for
 * testing other classes that depend on an auth service.
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
 * In-memory Authentication Service
 *
 * @category  foundry-core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class InMemory {

    /**
     * The user database keyed by username.
     * @var array
     */
    private $users = array();

    /**
     * User passwords keyed by array.
     * @var array
     */
    private $user_passwords = array();

    /**
     * Groups keyed bu group name.
     * @var array
     */
    private $groups = array();

    /**
     * Authenticate a user.
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password) {
        if (empty($username) || !isset($this->users[$username])) return false;
        return $this->user_passwords[$username] === $password;
    }

    /**
     * Change a user password.
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password) {
        if (empty($username) || !isset($this->users[$username])) return false;
        $this->user_passwords[$username] = $password;
        return true;
    }

    /**
     * Check to see if a user exists.
     * @param string $username The username to check.
     * @return boolean
     */
    public function userExists($username) {
        return isset($this->users[$username]);
    }

    /**
     * Returns an array of all the users or arrays keyed by username.
     * @return array|boolean
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Get a user's information as an array.
     * @param string $username
     * @return User
     */
    public function getUser($username) {
        if (!isset($this->users[$username])) return false;
        return $this->users[$username];
    }

    /**
     * Add a user.
     * @param User $user The attributes of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password) {
        $username = $user->getUsername();
        if (empty($username) || isset($this->users[$username])) return false;
        $this->users[$username] = $user;
        $this->user_passwords[$username] = $password;
        return true;
    }

    /**
     * Update a user.
     * @param User $user The attributes of the user to update.
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser($user) {
        $username = $user->getUsername();
        if (!isset($this->users[$username])) return false;
        $this->users[$username] = $user;
        return true;
    }

    /**
     * Delete a user.
     * @param string $username The username to delete.
     * @return boolean true on success, false on failure
     */
    public function deleteUser($username) {
        if (empty($username) || !isset($this->users[$username])) return false;
        unset($this->users[$username]);
        unset($this->user_passwords[$username]);
        return true;
    }

    // Group Methods

    /**
     * Check to see if a group exists.
     * @param string $groupname The group name to check.
     * @return boolean
     */
    public function groupExists($groupname) {
        return isset($this->groups[$groupname]);
    }

    /**
     * Returns an array of all the groups keyed by group name.
     *
     * @return array
     */
    public function getGroups() {
        return $this->groups;
    }


    /**
     * Returns an array of Group names keyed by group name.
     * @return array an group names (strings) keyed by group names.
     */
    public function getGroupNames() {
        $group_names = array();
        if (!empty($this->groups)) {
            foreach (array_keys($this->groups) as $groupname) {
                $group_names[$groupname] = $groupname;
            }
        }
        return $group_names;
    }

    /**
     * Get a group's members.
     * @param array $groupname
     * @return Group
     */
    public function getGroup($groupname) {
        if (empty($groupname) || !isset($this->groups[$groupname])) return false;
        return $this->groups[$groupname];
    }

    /**
     * Add a group.
     * @param Group $group The group to add.
     * @return boolean true on sucess, false on failure.
     */
    public function addGroup($group) {
        $groupname = $group->getName();
        if (empty($groupname) || isset($this->groups[$groupname])) return false;
        $this->groups[$groupname] = $group;
        return true;
    }

    /**
     * Delete a group.
     * @param string $groupname The name of the group to delete.
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname) {
        if (empty($groupname) || !isset($this->groups[$groupname])) return false;
        unset($this->groups[$groupname]);
        return true;
    }

    /**
     * Get an array of groups the given user is a member of.
     * @param string $username
     * @return array
     */
    public function getUserGroups($username) {
        if (empty($username) || !isset($this->users[$username])) return false;
        $user_groups = array();
        foreach ($this->groups as $groupname=>$group) {
            $users = $group->getUsers();
            if (isset($users[$username])) {
                $user_groups[$groupname] = $groupname;
            }
        }
        return $user_groups;
    }

    /**
     * Add a user to a group.
     * @param string $username The username to add to the group.
     * @param string $groupname The name of the group to add the user to.
     * @return boolean
     */
    public function addUserToGroup($username, $groupname) {
        if (empty($username) || empty($groupname)
                || !isset($this->groups[$groupname])
                || !isset($this->users[$username])) return false;

        $group = $this->groups[$groupname];
        $users = $group->getUsers();
        $users[$username] = $username;
        $group->setUsers($users);
        $this->groups[$groupname] = $group;
        return true;
    }

    /**
     * Remove a user from a group.
     * @param string $username The username to remove from the group.
     * @param string $groupname The name of the group to remove the user from.
     * @return boolean 
     */
    public function removeUserFromGroup($username, $groupname) {
        if (empty($username) || empty($groupname)
                || !isset($this->groups[$groupname])
                || !isset($this->users[$username])) return false;

        $group = $this->groups[$groupname];
        $users = $group->getUsers();
        if (!isset($users[$username])) return false; // User is not in group
        unset($users[$username]);
        $group->setUsers($users);
        $this->groups[$groupname] = $group;
        return true;
    }
}
?>
