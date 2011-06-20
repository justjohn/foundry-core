<?php
/**
 * Authentication API and service loader.
 * 
 * This file contains the authentication API and code for loading authentication
 * services from the Auth/Services directory.
 * 
 * Currently there are three available services:
 * 1. LDAP: Authenticates against an LDAP directory.
 * 2. Crowd: Authenticates against an Atlassian Crowd service endpoint.
 * 3. InMemory: Stores users and groups in memory until the end of script execution.
 *              Primarily for testing other components.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 *
 * @link http://www.atlassian.com/software/crowd/ Atlassian Crowd
 */
 
namespace Foundry\Core\Auth;

use Foundry\Core\Core;
use Foundry\Core\Logging\Log;
use Foundry\Core\Service;
use Foundry\Core\Exceptions\ServiceLoadException;

Core::requires('\Foundry\Core\Logging\Log');

/**
 * Authentication API and service loader.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Auth
{
    /**
     * The configuration options required to initialize an Auth service.
     */
    public static $required_options = array("service", "service_config", "admin_group");
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
     * The password hashing salt.
     * @var string
     */
    private $salt;
    /**
     * The algorithm to hash password with.
     * @var string
     */
    private $hash_algorithm = "sha256";
    /**
     * The number of times to hash the password.
     * @var string
     */
    private $hash_rounds = 200;

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
     * @param array $config The auth configuration.
     */
    function __construct(array $config)
    {
        Service::validate($config, self::$required_options);
        $auth_service = $config["service"];
        $auth_config = $config["service_config"];
        $this->admin_group = $config["admin_group"];
        if (isset($config["hash_algorithm"])) $this->hash_algorithm = $config["hash_algorithm"];
        if (isset($config["hash_rounds"])) $this->hash_rounds = $config["hash_rounds"];

        // include auth class
        if (!class_exists($auth_service)) {
            Log::error("Auth::__construct", "Unable to load auth class '$auth_service'.");
            throw new ServiceLoadException("Unable to load auth class '$auth_service'.");
        } else {
            $this->auth_service = new $auth_service($auth_config);
        }
    }

    /**
     * Check for SSO support.
     *
     * @return boolean
     */
    public function SSOSupport()
    {
        $implements = class_implements($this->auth_service);
        return isset($implements['Foundry\Core\Auth\AuthServiceSSO']);
    }

    /**
     * Check for subgroup support.
     *
     * @return boolean
     */
    public function subgroupSupport()
    {
        $implements = class_implements($this->auth_service);
        return isset($implements['Foundry\Core\Auth\AuthServiceSubgroups']);
    }

    /**
     * Authenticate a user.
     *
     * @param string $username The username.
     * @param string $password The password.
     *
     * @return boolean True if the username and password are valid, false if not.
     */
    public function authenticate($username, $password)
    {
        $this->is_authenticated = false;
        if ($this->auth_service->authenticate($username, $password)) {
            $this->is_authenticated = true;
            $this->user = $username;
        } else {
            Log::warn("Auth::authenticate", "Failed authentication for authenticate('$username', '********')");
        }
        return $this->is_authenticated;
    }

    /**
     * Mark a user as authenticated.
     *
     * @param string $username The user to mark as authenticated.
     */
    public function upauth($username)
    {
        $this->is_authenticated = true;
        $this->user = $username;
    }

    /**
     * Verify a username and password are correct.
     *
     * @param string $username The username to check.
     * @param string $password The password to verify is correct.
     *
     * @return boolean True if the username/password successfully authenticate,
     *                 false if they do not.
     */
    public function verify($username, $password)
    {
        return $this->auth_service->authenticate($username, $password);
    }

    /**
     * Is there a user currently authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->is_authenticated;
    }

    /**
     * Get the currently logged in user's username or false when no one is logged in.
     *
     * @return string|boolean The username or false if there isn't an authenticated user.
     */
    public function getUsername()
    {
        return $this->isAuthenticated()?$this->user:false;
    }

    /**
     * Add a new user to the configured authentication service.
     *
     * @param User $user The details of the user to add.
     * @param string $password The new user's password.
     *
     * @return boolean true on sucess, false on failure.
     */
    public function addUser($user, $password)
    {
        Log::info("Auth::addUser", "addUser('$user', '$password')");
        $result = $this->auth_service->addUser($user, $password);
        if ($result) {
            $username = $user->getUsername();
            $this->auth_cache["users"][$username] = $user;
            $this->auth_cache["user"][$username] = $user;
        }
        return $result;
    }

    /**
     * Update a user in the configured authentication service.
     *
     * @param User $user The parameters of the user to update.
     *
     * @return boolean true on sucess, false on failure.
     */
    public function updateUser(User $user)
    {
        Log::info("Auth::updateUser", "updateUser('$user)");
        $result = $this->auth_service->updateUser($user);
        if ($result) {
            $username = $user->getUsername();
            $this->auth_cache["users"][$username] = $user;
            $this->auth_cache["user"][$username] = $user;
        }
        return $result;
    }

    /**
     * Delete a user from the configured authentication service.
     *
     * @param string $username The username to delete.
     *
     * @return boolean true on sucess, false on failure.
     */
    public function deleteUser($username)
    {
        Log::info("Auth::deleteUser", "deleteUser('$username')");
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
     * Get a user's information from the authentication service.
     *
     * @param string $username The username to lookup, defaults to the current user
     *                         if left blank.
     *
     * @return User|boolean The user's information if the user exists, false if the
     *                      user can't be found.
     */
    public function getUser($username = '')
    {
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
     * Check to see if a username exists.
     *
     * @param string $username The username to check.
     *
     * @return boolean true if the user exists, false if not.
     */
    public function userExists($username)
    {
        if (empty($username)) return false;
        if (isset($this->auth_cache["user"][$username])) {
            return true;
        }
        $result = $this->auth_service->userExists($username);
        return $result;
    }

    /**
     * Get a list of all the users keyed by username.
     *
     * @return array An array of User objects keyed by username.
     */
    public function getUsers()
    {
        if (isset($this->auth_cache["users"])) {
            return $this->auth_cache["users"];
        }
        $users = $this->auth_service->getUsers();
        ksort($users);
        $this->auth_cache["users"] = $users;
        $this->auth_cache["user"] = $users;
        return $users;
    }

    /**
     * Get a list of groups the current user is a member of.
     *
     * @param string $user The user to get groups for; if blank the current user is used.
     *
     * @return array An array of the groups that the user is a member of. If the user
     *               can't be found or the $user parameter is blank and there is no
     *               logged in user, an empty array is returned.
     */
    public function getUserGroups($user='')
    {
        if ($user == '') $user = $this->user;
        if ($user == '') return array();
        if (isset($this->auth_cache["user_groups"][$user]))
                return $this->auth_cache["user_groups"][$user];

        $groups = $this->auth_service->getUserGroups($user);
        ksort($groups);
        $this->auth_cache["user_groups"][$user] = $groups;
        return $groups;
    }

    /**
     * Get a group's information.
     *
     * @param string $groupname The name of the group.
     *
     * @return Group|boolean
     */
    public function getGroup($groupname)
    {
        // Check local cache
        if (isset($this->auth_cache["group"][$groupname]))
            return $this->auth_cache["group"][$groupname];

        $group = $this->auth_service->getGroup($groupname);
        $this->auth_cache["group"][$groupname] = $group;
        return $group;
    }

    /**
     * Check if a group exists.
     *
     * @param string $groupname The name of the group.
     *
     * @return boolean
     */
    public function groupExists($groupname)
    {
        return ($this->getGroup($groupname) !== false);
    }

    /**
     * Get a group's membership (including subgroups if supported)
     *
     * @param Group $group The group to get members from.
     * @param array $members The array of members to add group members to.
     * @param array $groups The groups already checked (to prevent infinite recursion)
     *
     * @return array An array of usernames keyed by username.
     */
    public function getGroupMembership($group, &$members = array(), &$groups = array())
    {
        $groupname = $group->getName();
        $groups[$groupname] = $groupname;
        $users = $group->getUsers();
        if (!empty($users)) {
            foreach ($users as $username => $user) {
                $members[$username] = $username;
            }
        }
        if ($this->subgroupSupport()) {
            $subgroups = $group->getSubgroups();
            if (!empty($subgroups)) {
                foreach ($subgroups as $subgroupname) {
                    $subgroup = $this->getGroup($subgroupname);
                    if (!isset($groups[$subgroupname])) {
                        $this->getGroupMembership($subgroup, $members, $groups);
                    }
                }
            }
        }
        uksort($members, "strcasecmp");
        return $members;
    }
    
    /**
     * Check if a user is a member of the group or any of it's subgroups.
     *
     * @param string $username The username to check.
     * @param string $groupname The name of the group to check.
     *
     * @return boolean True if the user is in the group, false if not.
     */
    public function userInGroup($username, $groupname)
    {
        if ($this->getUser($username) === false) return false;
        $group = $this->getGroup($groupname);
        if ($group === false) return false;
        $users = $this->getGroupMembership($group);
        return isset($users[$username]);
    }
    
    /**
     * Get a list of all the groups.
     *
     * @param boolean $flatten Include users from subgroups in group membership
     *                         (if supported). Defaults to false.
     *
     * @return array
     */
    public function getGroups($flatten = false)
    {
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
        uksort($groups, "strcasecmp");
        $this->auth_cache["groups"][$flatten] = $groups;
        return $groups;
    }

    /**
     * Get a list of all the groups.
     *
     * @return array
     */
    public function getGroupNames()
    {
        if (isset($this->auth_cache["groupnames"]))
                return $this->auth_cache["groupnames"];

        $groups = $this->auth_service->getGroupNames();
        uksort($groups, "strcasecmp");
        $this->auth_cache["groupnames"] = $groups;
        return $groups;
    }

    /**
     * Create a new group.
     *
     * @param Group $group The group to add.
     *
     * @return boolean
     */
    public function addGroup($group)
    {
        Log::info("Auth::addGroup", "addGroup('$group')");
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
     *
     * @return boolean true on success, false on failure.
     */
    public function deleteGroup($groupname)
    {
        Log::info("Auth::deleteGroup", "deleteGroup('$groupname')");
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
     *
     * @return boolean true on success, false on failure.
     */
    public function addUserToGroup($username, $groupname)
    {
        Log::info("Auth::addUserToGroup", "addUserToGroup('$username', '$groupname')");
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
     *
     * @return boolean true on success, false on failure.
     */
    public function addSubgroupToGroup($subgroupname, $groupname)
    {
        Log::info("Auth::addSubgroupToGroup", "addSubgroupToGroup('$subgroupname', '$groupname')");
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
     *
     * @return boolean true on success, false on failure.
     */
    public function removeUserFromGroup($username, $groupname)
    {
        Log::info("Auth::removeUserFromGroup", "removeUserFromGroup('$username', '$groupname')");
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
     *
     * @return boolean true on success, false on failure.
     */
    public function removeSubgroupFromGroup($subgroupname, $groupname)
    {
        Log::info("Auth::removeSubgroupFromGroup", "removeSubgroupFromGroup('$subgroupname', '$groupname')");
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
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if ($this->admin_group == "") {
            return false;
        }
        $groups = $this->getUserGroups();
        return array_key_exists($this->admin_group, $groups);
    }

    /**
     * Get the site admin group.
     *
     * @return string
     */
    public function getAdminGroup()
    {
        return $this->admin_group;
    }

    /**
     * Change a user password.
     * 
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password)
    {
        Log::info("Auth::changePassword", "changePassword('$username', '********')");
        return $this->auth_service->changePassword($username, $password);
    }

    /**
     * Check for a single sign on token.
     *
     * @return boolean True if logged into a SSO, false if not.
     */
    public function checkSSO()
    {
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
    public function logoutSSO()
    {
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
    public function authSSO($auth_token)
    {
        if ($this->SSOSupport()) {
            return $this->auth_service->authSSO($auth_token);
        } else {
            return true;
        }
    }

    /**
     * Get direct access to the underlying authentication service.
     * This SHOULD NOT BE USED except for testing the underlying auth service.
     *
     * @return AuthService
     */
    public function getAuthService()
    {
        return $this->auth_service;
    }

    /**
     * Salt and hash a password with multiple rounds of hashing.
     *
     * @param $password The password string to hash.
     *
     * @return Returns a hashed and salted version of the password.
     */
    public function hashPassword($password)
    {
        $hash = $password;
        for ($i=0;$i<200;$i++) {
            $hash = hash($this->salt . $hash, "sha256");
        }
        return $hash;
    }
}

?>
