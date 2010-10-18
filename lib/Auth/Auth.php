<?php
/**
 * Authentication API and service loader.
 * 
 * This file contains the authentication API and code for loading authentication services.
 *
 * @package   Auth
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */

// Register the authentication related model classes with the class loader.
register_class("User", "Auth/model/User.php");
register_class("Group", "Auth/model/Group.php");
register_class("ResetToken", "Auth/model/ResetToken.php");

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
     * The database access layer.
     * @var Database $database
     */
    private $database;
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
     * The URL mapper.
     * @var URL
     */
    private $url_manager;
    /**
     * Salt for generating the password reset tokens.
     * @var string
     */
    private $salt;
    /**
     * The password reset time in seconds.
     * @var integer
     */
    private $token_timeout = 86400;  // 24 hours

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
     * @param URL        $url_manager  The URL manager.
     * @param Database   $database     The data layer.
     * @param string     $salt         Salt for generating password reset tokens.
     */
    function __construct($auth_service,
                         array $auth_config,
                         $admin_group,
                         URL $url_manager,
                         Database $database,
                         $salt) {

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
        $this->url_manager = $url_manager;
        $this->database = $database;
        $this->salt = $salt;
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
    public function SubgroupSupport() {
        $implements = class_implements($this->auth_service);
        return isset($implements["AuthServiceSubgroup"]);
    }

    /**
     * Authenticate a user.
     *
     * @param string $user
     * @param string $password
     * @return boolean
     */
    public function authenticate($user, $password) {
        $this->is_authenticated = false;
        if ($this->auth_service->authenticate($user, $password)) {
            $this->is_authenticated = true;
            $this->user = $user;
        } else {
            LogManager::warn("Auth::authenticate", "Failed authentication for authenticate('$user', '$password')");
        }
        return $this->is_authenticated;
    }

    /**
     * Verify a username and password.
     *
     * @param string $user
     * @param string $password
     * @return boolean
     */
    public function verify($user, $password) {
        return $this->auth_service->authenticate($user, $password);
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
        LogManager::info("Auth::addUser", "addUser('".get_a($user)."', '$password')");
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
        LogManager::info("Auth::updateUser", "updateUser('".get_a($user).")");
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
     * Get a group's membership (including subgroups)
     * @param Group $group The group to get members from.
     * @param array $members The array of members to add group members to.
     * @return array An array of usernames keyed by username.
     */
    public function getGroupMembership($group, &$members = array()) {
        $users = $group->getUsers();
        if (!empty($users))
            foreach ($users as $username => $user)
                $members[$username] = $username;

        $subgroups = $group->getSubgroups();
        if (!empty($subgroups))
            foreach ($subgroups as $subgroup)
                $this->getGroupMembership($this->getGroups($subgroup), $members);

        return $members;
    }

    /**
     * Get a list of all the groups.
     * @param boolean $flatten Include users from subgroups in group membership. Defaults to false.
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

        if ($flatten && !empty($groups)) {
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
        LogManager::info("Auth::addGroup", "addGroup('".get_a($group)."')");
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
     * Build an authentication page.
     *
     * @param  string  $redirect_url Where to redirect to after authentication.
     * @param  boolean $always_redirect Whether to redirect on failed authentication.
     * @param  string  $username The username to pre-fill the field with.
     * @param  string  $error Any errors to display on the page.
     * @return string The authentication page.
     */
    public function buildAuthenticationPage($redirect_url,
                                            $always_redirect=false,
                                            $username='',
                                            $error='') {
        global $twig;
        $login_url = $this->url_manager->getLoginURL($redirect_url);

        $message = '';
        if ($error == '') {
            $message = 'Please login to continue.';
        }

        $template = $twig->loadTemplate("login/loginForm.html");
        $model = array("always_redir" => $always_redirect,
                       "username" => $username,
                       "message" => $message,
                       "error" => $error,
                       "url_manager" => $this->url_manager,
                       "login_url" => $login_url);
        return $template->render($model);
    }

    /**
     * Get a new password reset token for a username. Also clears any previous tokens for the username.
     *
     * @return string a reset token
     */
    public function createResetToken($username) {
        LogManager::info("Auth::createResetToken", "createResetToken('$username')");
       $this->clearResetTokens($username); 
       $expiration = time() + $this->token_timeout;
       $token = md5(time() . $this->salt);
       $token_obj = new ResetToken();
       $token_obj->setExpiration($expiration);
       $token_obj->setToken($token);
       $token_obj->setUsername($username);
       $this->database->write_object($token_obj, "reset_tokens");
       return $token;
    }

    /**
     * Clear all expired reset tokens or tokens for the given username.
     *
     * @param $username String (Optionaly) the username to clear tokens for.
     */
    public function clearResetTokens($username='') {
        LogManager::info("Auth::clearResetTokens", "clearResetTokens('$username')");
        if ($username == '') {
            $conditions = array("expiration"=>array("<", time()));
        } else {
            $conditions = array("username"=>$username);
        }
        $this->database->delete_object("reset_tokens", $conditions);
    }

    /**
     * Get a reset token.
     *
     * @param string $token_value The token to lookup.
     * @return boolean|ResetToken false if the token doesn't exist, a ResetToken otherwise.
     */
    public function getToken($token_value) {
        // Remove old tokens
        $this->clearResetTokens();
        $token = $this->database->load_object("ResetToken", "reset_tokens", array("token"=>$token_value));
        if ($token === false) {
            return false;
        }
        return $token;
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
        return $this->auth_service->logoutSSO();
    }

    /**
     * Set a SSO token.
     *
     * @param string $auth_token
     * @return boolean
     */
    public function authSSO($auth_token) {
        return $this->auth_service->authSSO($auth_token);
    }
}
?>
