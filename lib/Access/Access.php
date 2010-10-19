<?php
/**
 * Role Management.
 *
 * @package   Role
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */

// Register the role related model classes with the class loader.
Core::register_class("Role", "Roles/model/Role.php");

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
     * The current access service.
     * @var AccessService
     */
    private $access_service;

    /**
     * The authentication manager.
     * @var Auth
     */
    private $auth_manager;

    public function __construct(Auth $auth_manager,
                                $access_service,
                                array $service_config = array()) {

        $this->auth_manager = $auth_manager;
        // include service class
        include_once("Roles/Service/$access_service.php");
        if (!class_exists($access_service)) {
            LogManager::error("Access::__construct", "Unable to load access class '$access_service'.");
            throw new ServiceLoadException("Unable to load access class '$access_service'.");
        } else {
            $this->access_service = new $access_service($service_config);
            if (!$this->access_service instanceof AccessService) {
                throw new ServiceLoadException("Access class invalid - '$access_service' does not implement AccessService.");
            }
        }
    }

    /**
     * Add a role definition.
     * @param string $role_key The name of the role.
     * @param string $role_description The description of the role.
     * @param array $role_groups The groups assigned to the role.
     * @return boolean
     */
    public function addRole($role_key, $role_description, array $role_groups) {
        $role_key = trim($role_key);
        if (empty($role_key) || empty($role_groups)) return false;
        LogManager::info("Access::addRole", "addRole('$role_key', '$role_description', '".get_a($role_groups)."')");
        $result = $this->access_service->addRole($role_key, $role_description, $role_groups);
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
    public function hasRole($username, $role_key) {
        LogManager::debug("Access::hasRole", "hasRole('$username', '$role_key')");
        $username = trim($username);
        $role_key = trim($role_key);
        if (empty($username) || empty($role_key)) return false;
        $user_groups = $this->auth_manager->getUserGroups($username);
        $role = $this->getRole($role_key);
        $role_groups = $role->getGroups();
        $intersect = array_intersect($user_groups, $role_groups);
        return !empty($intersect);
    }
}
?>
