<?php
/**
 * Authentication API and service loader.
 * 
 * This file contains the authentication API and code for loading authentication services.
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

// Register the authentication related model classes with the class loader.
Core::register_class("User", "Auth/model/User.php");
Core::register_class("Group", "Auth/model/Group.php");

/**
 * Load the AuthService interface.
 */
require_once("Auth/AuthService.php");
require_once("Auth/AuthServiceSSO.php");
require_once("Auth/AuthServiceSubgroups.php");

/**
 * Authentication API and service loader.
 * 
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */
class Auth {
    /**
     * The authentication service.
     * @var AuthService
     */
    private $auth_service;
    /**
     * Is there a current user authenticated.
     * @var boolean
     */
    private $is_authenticated = false;
    /**
     * The site admin group.
     * @var string
     */
    private $admin_group = "";
    /**
     * The name of the current user (if any).
     * @var string
     */
    private $user;

    /**
     * Cache authentication results locally.
     * @var array
     */
    private $auth_cache = array(
        // cache of Group objects
        "group" => array(),
        // cache of User objects
        "user"  => array(),
        // cache of User group lists
        "user_groups"  => array()
    );

    /**
     * Setup the auth manager.
     *
     * @param String     $auth_service The name of the auth service class to load.
     * @param array      $auth_config  An array of configuration options to pass to the auth service.
     * @param string     $admin_group  The name of the site admin group.
     */
    function __construct($auth_service,
                         array $auth_config,
                         $admin_group) {

        // Load the authentication cache.
        /* if (isset($_SESSION["auth_cache"])) {
            $this->auth_cache = $_SESSION["auth_cache"];
        } */

        // include auth class
        include_once("Auth/Service/$auth_service.php");
        if (!class_exists($auth_service)) {
            LogManager::error("Auth::__construct", "Unable to load auth class '$auth_service'.");
            throw new ServiceLoadException("Unable to load auth class '$auth_service'.");
        } else {
            $this->auth_service = new $auth_service($auth_config);
        }
        $this->admin_group = $admin_group;
    }

    /**
     * Save the authentication cache.
     */
    public function __destruct() {
        // $_SESSION["auth_cache"] = $this->auth_cache;
    }

    /**
     * Check for SSO support.
     * @return boolean
     */
    public function SSOSupport() {
        $implements = class_implements($this->auth_service);
        return isset($implements["AuthServiceSSO"]);
    }

    /**
     * Check for subgroup support.
     * @return boolean
     */
    public function subgroupSupport() {
        $implements = class_implements($this->auth_service);
        return isset($implements["AuthServiceSubgroups"]);
    }

    /**
     * Authenticate a user.
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function authenticate($username, $password) {
        $this->is_authenticated = false;
        if ($this->auth_service->authenticate($username, $password)) {
            $this->is_authenticated = true;
            $this->user = $username;
        } else {
            LogManager::warn("Auth::authenticate", "Failed authentication for authenticate('$username', '********')");
        }
        return $this->is_authenticated;
    }

    /**
     * Mark a user as authenticated.
     * @param string $username The user to mark as authenticated.
     */
    public function upauth($username) {
        $this->is_authenticated = true;
        $this->user = $username;
    }

    /**
     * Verify a username and password.
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function verify($username, $password) {
        return $this->auth_service->authenticate($username, $password);
    }

    /**
     * Is there a user currently authenticated.
     * @return boolean
     */
    public function isAuthenticated() {
        return $this->is_authenticated;
    }

    /**
     * Get the currently logged in user's username or false when no one is logged in.
     * @return string|boolean
     */
    public function getUsername() {
        return $this->isAuthenticated()?$this->user:false;
    }

    /**
     * Add a user.
     * @param User $user The details of the user to add.
     * @param string $password The user's password.
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password) {
        LogManager::info("Auth::addUser", "addUser('$user', '$password')");
        $result = $this->auth_service->addUser($user, $password);
        if ($result) {
            $username = $user->getUsername();
            $this->auth_cache["users"][$username] = $user;
            $this->auth_cache["user"][$username] = $user;
        }
        return $result;
    }

    /**
     * Add a user.
     * @param User $user The parameters of the user to update.
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser($user) {
        LogManager::info("Auth::updateUser", "updateUser('$user)");
        $result = $this->auth_service->updateUser($user);
        if ($result) {
            $username = $user->getUsername();
            $this->auth_cache["users"][$username] = $user;
            $this->auth_cache["user"][$username] = $user;
        }
        return $result;
    }

    /**
     * Delete a user.
     * @param string $username The username to delete.
     * @return boolean true on sucess, false on failure.
     */
    public function deleteUser($username) {
        LogManager::info("Auth::deleteUser", "deleteUser('$username')");
        if (empty($username)) return false;
        $result = $this->auth_service->deleteUser($username);
        if ($result) {
            unset($this->auth_cache["users"][$username]);
            unset($this->auth_cache["user_groups"][$username]);
            unset($this->auth_cache["user"][$username]);
        }
        return $result;
    }

    /**
     * Get a user's details
     * @param string $username The user to lookup, defaults to the current user if blank.
     * @return User|boolean
     */
    public function getUser($username = '') {
        $username = trim($username);
        if (empty($username)) $username = $this->user;
        if (empty($username)) return false;

        // Check local cache
        if (isset($this->auth_cache["user"][$username]))
                return $this->auth_cache["user"][$username];

        $user = $this->auth_service->getUser($username);
        $this->auth_cache["user"][$username] = $user;
        return $user;
    }

    /**
     * Check to see if a user exists.
     * @param string $username The username to check.
     * @return boolean
     */
    public function userExists($username) {
        if (empty($username)) return false;
        if (isset($this->auth_cache["user"][$username])) {
            return true;
        }
        $result = $this->auth_service->userExists($username);
        return $result;
    }

    /**
     * Get a list of all the users.
     *
     * @return array An array of User objects.
     */
    public function getUsers() {
        if (isset($this->auth_cache["users"])) {
            return $this->auth_cache["users"];
        }
        $users = $this->auth_service->getUsers();
        $this->auth_cache["users"] = $users;
        $this->auth_cache["user"] = $users;
        return $users;
    }

    /**
     * Get a list of groups the current user is a member of.
     * @param string $user The user to get groups for; if blank the current user is used.
     * @return array
     */
    public function getUserGroups($user='') {
        if ($user == '') $user = $this->user;
        if ($user == '') return array();
        if (isset($this->auth_cache["user_groups"][$user]))
                return $this->auth_cache["user_groups"][$user];

        if ($this->is_authenticated) {
            $groups = $this->auth_service->getUserGroups($user);
            $this->auth_cache["user_groups"][$user] = $groups;
            return $groups;
        } else {
            return array();
        }
    }

    /**
     * Get a group's information.
     * @param string $groupname The name of the group.
     * @return Group|boolean
     */
    public function getGroup($groupname) {
        // Check local cache
        if (isset($this->auth_cache["group"][$groupname]))
                return $this->auth_cache["group"][$groupname];

        $group = $this->auth_service->getGroup($groupname);
        $this->auth_cache["group"][$groupname] = $group;
        return $group;
    }


    /**
     * Get a group's membership (including subgroups if supported)
     * @param Group $group The group to get members from.
     * @param array $members The array of members to add group members to.
     * @param array $groups The groups already checked (to prevent infinite recursion)
     * @return array An array of usernames keyed by username.
     */
    public function getGroupMembership($group, &$members = array(), &$groups = array()) {
        $groupname = $group->getName();
        $groups[$groupname] = $groupname;
        $users = $group->getUsers();
        if (!empty($users))
            foreach ($users as $username => $user)
                $members[$username] = $username;

        if ($this->subgroupSupport()) {
            $subgroups = $group->getSubgroups();
            if (!empty($subgroups)) {
                foreach ($subgroups as $subgroup) {
                    $subgroupname = $subgroup->getName();
                    if (!isset($groups[$subgroupname])) {
                        $this->getGroupMembership($subgroup, $members, $groups);
                    }
                }
            }
        }
        return $members;
    }
    
    /**
     * Check if a user is a member of the group or any of it's subgroups.
     * @param string $username The username to check.
     * @param string $groupname The name of the group to check.
     * @return boolean True if the user is in the group, false if not.
     */
    public function userInGroup($username, $groupname) {
        if ($this->getUser($username) === false) return false;
        $group = $this->getGroup($groupname);
        if ($group === false) return false;
        $users = $this->getGroupMembership($group);
        return isset($users[$username]);
    }
    
    /**
     * Get a list of all the groups.
     * @param boolean $flatten Include users from subgroups in group membership (if supported). Defaults to false.
     * @return array
     */
    public function getGroups($flatten = false) {
        if (isset($this->auth_cache["groups"][$flatten])) {
            return $this->auth_cache["groups"][$flatten];
        } else {
            $groups = $this->auth_service->getGroups();
        }
        // Don't cache with nested info
        if (!$flatten) $this->auth_cache["group"] = $groups;

        if ($flatten && !empty($groups) && $this->subgroupSupport()) {
            foreach ($groups as &$group) {
                $members = $this->getGroupMembership($group);
                $group->setUsers($members);
            }
            unset($group);
        }
        $this->auth_cache["groups"][$flatten] = $groups;
        return $groups;
    }

    /**
     * Get a list of all the groups.
     * @return array
     */
    public function getGroupNames() {
        if (isset($this->auth_cache["groupnames"]))
                return $this->auth_cache["groupnames"];

        $groups = $this->auth_service->getGroupNames();
        $this->auth_cache["groupnames"] = $groups;
        return $groups;
    }

    /**
     * Create a new group.
     *
     * @param Group $group The group to add.
     * @return boolean
     */
    public function addGroup($group) {
        LogManager::info("Auth::addGroup", "addGroup('$group')");
        $result = $this->auth_service->addGroup($group);
        // Invalidate groups cache
        if ($result) {
            $groupname = $group->getName();
            $this->auth_cache["groups"][$groupname] = $group;
            $this->auth_cache["group"][$groupname] = $group;
        }
        return $result;
    }

    /**
     * Delete a group.
     *
     * @param string $groupname The name of the group to delete.
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname) {
        LogManager::info("Auth::deleteGroup", "deleteGroup('$groupname')");
        $result = $this->auth_service->deleteGroup($groupname);
        // Invalidate groups cache
        if ($result) {
            $this->auth_cache["user_groups"] = array();
            unset($this->auth_cache["groups"]);
        }
        return $result;
    }

    /**
     * Add a user to a group.
     *
     * @param string $username The name of the user to add.
     * @param string $groupname The name of the group.
     * @return boolean true on success, false on failure.
     */
    public function addUserToGroup($username, $groupname) {
        LogManager::info("Auth::addUserToGroup", "addUserToGroup('$username', '$groupname')");
        $result = $this->auth_service->addUserToGroup($username, $groupname);
        if ($result) {
            unset($this->auth_cache["groups"]);
            unset($this->auth_cache["group"][$groupname]);
            unset($this->auth_cache["user_groups"][$username]);
        }
        return $result;
    }

    /**
     * Add a subgroup to a group.
     *
     * @param string $subgroupname The name of the sub-group to add.
     * @param string $groupname The name of the group.
     * @return boolean true on success, false on failure.
     */
    public function addSubgroupToGroup($subgroupname, $groupname) {
        LogManager::info("Auth::addSubgroupToGroup", "addSubgroupToGroup('$subgroupname', '$groupname')");
        $result = $this->auth_service->addSubgroupToGroup($subgroupname, $groupname);
        if ($result) {
            unset($this->auth_cache["groups"]);
            unset($this->auth_cache["group"][$groupname]);
            unset($this->auth_cache["user_groups"][$username]);
        }
        return $result;
    }

    /**
     * Remove a user from a group.
     *
     * @param string $username The name of the user to remove.
     * @param string $groupname The name of the group.
     * @return boolean true on success, false on failure.
     */
    public function removeUserFromGroup($username, $groupname) {
        LogManager::info("Auth::removeUserFromGroup", "removeUserFromGroup('$username', '$groupname')");
        $result = $this->auth_service->removeUserFromGroup($username, $groupname);
        if ($result) {
            unset($this->auth_cache["groups"]);
            unset($this->auth_cache["group"][$groupname]);
            unset($this->auth_cache["user_groups"][$username]);
        }
        return $result;
    }

    /**
     * Remove a subgroup from a group.
     *
     * @param string $subgroupname The name of the sub-group to remove.
     * @param string $groupname The name of the group.
     * @return boolean true on success, false on failure.
     */
    public function removeSubgroupFromGroup($subgroupname, $groupname) {
        LogManager::info("Auth::removeSubgroupFromGroup", "removeSubgroupFromGroup('$subgroupname', '$groupname')");
        $result = $this->auth_service->removeSubgroupFromGroup($subgroupname, $groupname);
        if ($result) {
            unset($this->auth_cache["groups"]);
            unset($this->auth_cache["group"][$groupname]);
            unset($this->auth_cache["user_groups"][$username]);
        }
        return $result;
    }

     ////////////////////////////////////////////////////////////////////////
    // Current User operations, etc...

    /**
     * Is the current user a site admin.
     * @return boolean
     */
    public function isAdmin() {
        if ($this->admin_group == "") {
            return false;
        }
        $groups = $this->getUserGroups();
        return array_key_exists($this->admin_group, $groups);
    }

    /**
     * Get the site admin group.
     * @return string
     */
    public function getAdminGroup() {
        return $this->admin_group;
    }

    /**
     * Change a user password.
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password) {
        LogManager::info("Auth::changePassword", "changePassword('$username', '********')");
        return $this->auth_service->changePassword($username, $password);
    }

    /**
     * Check for a single sign on token.
     *
     * @return boolean True if logged into a SSO, false if not.
     */
    public function checkSSO() {
        $user = $this->auth_service->checkSSO();
        if ($user === false) {
            return false;
        } else {
            $this->user = $user;
            $this->is_authenticated = true;
            return true;
        }
    }

    /**
     * Logout of the SSO system.
     */
    public function logoutSSO() {
        if ($this->SSOSupport()) {
            return $this->auth_service->logoutSSO();
        }
    }

    /**
     * Set a SSO token.
     *
     * @param string $auth_token
     * @return boolean
     */
    public function authSSO($auth_token) {
        if ($this->SSOSupport()) {
            return $this->auth_service->authSSO($auth_token);
        } else {
            return true;
        }
    }
}
?>
