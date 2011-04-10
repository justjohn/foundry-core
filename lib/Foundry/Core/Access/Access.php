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
 * Currently there are two role services:
 *  InMemory: Stores roles in memory until the end of script execution.
 *  Database: Stores Roles in a database.
 * 
 * PHP version 5.3.0
 * 
 * @category  foundry-core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Access;

use Foundry\Core\Core;
use Foundry\Core\Service;
use Foundry\Core\Exceptions\ServiceLoadException;
use Foundry\Core\Logging\Log;

Core::requires('\Foundry\Core\Auth\Auth');
Core::requires('\Foundry\Core\Logging\Log');

/**
 * Role Management API.
 *
 * @category  foundry-core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Access
{
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
    private $_access_service;

    /**
     * The authentication manager.
     * @var Auth
     */
    private $_auth_manager;

    /**
     * Create a Access Service.
     * 
     * @param array $config The access configuration.
     */
    public function __construct(array $config)
    {
        Service::validate($config, self::$required_options);
        $access_service = $config["service"];
        $service_config = $config["service_config"];
        $this->_auth_manager = Core::get('\Foundry\Core\Auth\Auth');
        
        // include service class
        include_once("Foundry/Core/Access/Service/$access_service.php");
        $access_service = 'Foundry\Core\Access\\'.$access_service;
        if (!class_exists($access_service)) {
            Log::error("Access::__construct", "Unable to load access class '$access_service'.");
            throw new ServiceLoadException("Unable to load access class '$access_service'.");
        } else {
            $this->_access_service = new $access_service($service_config);
            if (!$this->_access_service instanceof AccessService) {
                throw new ServiceLoadException("Access class invalid - '$access_service' does not implement AccessService.");
            }
        }
        
        // Special pre-defined roles
        $this->addRole(new Role(Access::ADMIN, "Admin Role", array($this->_auth_manager->getAdminGroup())));
        $this->addRole(new Role(Access::AUTHENTICATED, "Authenticated Role"));
        $this->addRole(new Role(Access::ANONYMOUS, "Anonymous Role (non-authenticated only)"));
        $this->addRole(new Role(Access::ALL, "Anonymous + Authenticated Role (allow access to everyone)"));
    }

    /**
     * Add a role definition.
     * 
     * @param Role $role The role.
     * 
     * @return boolean true on success, false on failure.
     */
    public function addRole(Role $role)
    {
        Log::info("Access::addRole", "addRole('$role')");
        $result = $this->_access_service->addRole($role);
        return $result;
    }

    /**
     * Remove a role.
     * 
     * @param string $role_key The role to remove.
     * 
     * @return boolean true on success, false on failure.
     */
    public function removeRole($role_key)
    {
        Log::info("Access::removeRole", "removeRole('$role_key')");
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        $result = $this->_access_service->removeRole($role_key);
        return $result;
    }

    /**
     * Get a role.
     * 
     * @param string $role_key The role to get.
     * 
     * @return Role|boolean The role if found, false otherwise.
     */
    public function getRole($role_key)
    {
        Log::debug("Access::getRole", "getRole('$role_key')");
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        $result = $this->_access_service->getRole($role_key);
        return $result;
    }

    /**
     * Check if a user has a role.
     * 
     * @param string $role_key The role to check.
     * @param string $username The user to check.
     * 
     * @return boolean true on success, false on failure.
     */
    public function hasRole($role_key, $username = '')
    {
        Log::debug("Access::hasRole", "hasRole('$username', '$role_key')");
        $username = trim($username);
        $role_key = trim($role_key);
        if (empty($role_key)) return false;
        if ($role_key == Access::ALL) return true;
        if (empty($username)) {
            $username = $this->_auth_manager->getUsername();
        }
        if ($role_key == Access::AUTHENTICATED &&
                $this->_auth_manager->isAuthenticated()) {
            return true;
        }
        if ($role_key == Access::ANONYMOUS &&
                !$this->_auth_manager->isAuthenticated()) {
            return true;
        }
        $role = $this->getRole($role_key);
        if ($role === false) {
            return false;
        }
        $role_groups = $role->getGroups();
        $user_groups = $this->_auth_manager->getUserGroups($username);
        $intersect = array_intersect($user_groups, $role_groups);
        return !empty($intersect);
    }
}

?>