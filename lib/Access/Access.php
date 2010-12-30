<?php
/**
 * Role Management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

// Register the role related model classes with the class loader.
Core::register_class("Role", "Access/model/Role.php");

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

    public function __construct($access_service,
                                array $service_config,
                                Auth $auth_manager) {

        $this->auth_manager = $auth_manager;
        // include service class
        include_once("Access/Service/$access_service.php");
        if (!class_exists($access_service)) {
            LogManager::error("Access::__construct", "Unable to load access class '$access_service'.");
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
        LogManager::info("Access::addRole", "addRole('$role')");
        $result = $this->access_service->addRole($role);
        return $result;
    }

    /**
     * Remove a role.
     * @param string $role_key The role to remove.
     * @return boolean
     */
    public function removeRole($role_key) {
        LogManager::info("Access::removeRole", "removeRole('$role_key')");
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
        LogManager::debug("Access::getRole", "getRole('$role_key')");
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
        LogManager::debug("Access::hasRole", "hasRole('$username', '$role_key')");
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
?>
