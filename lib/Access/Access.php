<?php
/**
 * Access/Role API and service loader.
 * 
 * This module contains access/role managment functionality. The access service
 * stores role definitions.
 * 
 * A Role is made up of:
 * <pre>Role {
 *     key:         A short name for the role (e.g. administrators.)
 *     description: A description of the role.
 *     groups:      A list of groupnames associated with the role.
 * }</pre>
 * 
 * Currently there is only one role service:
 * InMemory: Stores roles in memory until the end of script execution.
 * 
 */

namespace foundry\core\access;

use foundry\core\Core;
use foundry\core\Service;
use foundry\core\exceptions\ServiceLoadException;
use foundry\core\logging\Log;

Core::requires('\foundry\core\auth\Auth');
Core::requires('\foundry\core\logging\Log');

/**
 * Role Management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

// Register the role related model classes with the class loader.
Core::register_class("foundry\core\access\Role", "Access/model/Role.php");

/**
 * Load the AccessService interface.
 */
require_once("Access/AccessService.php");

/**
 * Role Management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */
class Access {
    /**
     * The configuration options required to initialize an Auth service.
     */
    public static $required_options = array("service", "service_config");
    /**
     * Admin role.
     */
    const ADMIN = "admin";
    /**
     * Authenticated role.
     */
    const AUTHENTICATED = "authenticated";
    /**
     * The anonymouse role.
     */
    const ANONYMOUS = "anonymous";
    /**
     * The all role.
     */
    const ALL = "all";

    /**
     * The current access service.
     * @var AccessService
     */
    private $access_service;

    /**
     * The authentication manager.
     * @var Auth
     */
    private $auth_manager;

    public function __construct() {
        $config = Core::getConfig('\foundry\core\access\Access');
        Service::validate($config, self::$required_options);
        $access_service = $config["service"];
        $service_config = $config["service_config"];
        $this->auth_manager = Core::get('\foundry\core\auth\Auth');
        
        // include service class
        include_once("Access/Service/$access_service.php");
        $access_service = 'foundry\core\access\\'.$access_service;
        if (!class_exists($access_service)) {
            Log::error("Access::__construct", "Unable to load access class '$access_service'.");
            throw new ServiceLoadException("Unable to load access class '$access_service'.");
        } else {
            $this->access_service = new $access_service($service_config);
            if (!$this->access_service instanceof AccessService) {
                throw new ServiceLoadException("Access class invalid - '$access_service' does not implement AccessService.");
            }
        }
        
        // Special pre-defined roles
        $this->addRole(new Role(Access::ADMIN, "Admin Role", array($this->auth_manager->getAdminGroup())));
        $this->addRole(new Role(Access::AUTHENTICATED, "Authenticated Role"));
        $this->addRole(new Role(Access::ANONYMOUS, "Anonymous Role (non-authenticated only)"));
        $this->addRole(new Role(Access::ALL, "Anonymous + Authenticated Role (allow access to everyone)"));
    }

    /**
     * Add a role definition.
     * @param Role $role The role.
     * @return boolean
     */
    public function addRole(Role $role) {
        Log::info("Access::addRole", "addRole('$role')");
        $result = $this->access_service->addRole($role);
        return $result;
    }

    /**
     * Remove a role.
     * @param string $role_key The role to remove.
     * @return boolean
     */
    public function removeRole($role_key) {
        Log::info("Access::removeRole", "removeRole('$role_key')");
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        $result = $this->access_service->removeRole($role_key);
        return $result;
    }

    /**
     * Get a role.
     * @param string $role_key The role to get.
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key) {
        Log::debug("Access::getRole", "getRole('$role_key')");
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        $result = $this->access_service->getRole($role_key);
        return $result;
    }

    /**
     * Check is a user has a role.
     * @param string $username The user to check.
     * @param string $role_key The role to check.
     * @return boolean
     */
    public function hasRole($role_key, $username = '') {
        Log::debug("Access::hasRole", "hasRole('$username', '$role_key')");
        $username = trim($username);
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        if ($role_key == Access::ALL) return true;
        if (empty($username)) {
            $username = $this->auth_manager->getUsername();
        }
        if ($role_key == Access::AUTHENTICATED && $this->auth_manager->isAuthenticated()) {
            return true;
        }
        if ($role_key == Access::ANONYMOUS && !$this->auth_manager->isAuthenticated()) {
            return true;
        }
        $role = $this->getRole($role_key);
        if ($role === false) {
            return false;
        }
        $role_groups = $role->getGroups();
        $user_groups = $this->auth_manager->getUserGroups($username);
        $intersect = array_intersect($user_groups, $role_groups);
        return !empty($intersect);
    }
}

return new Access();
?>