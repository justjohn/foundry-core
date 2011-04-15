<?php
/**
 * A database backed AccessService implementation.
 * 
 * PHP version 5.3.0
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core\Access;

use Foundry\Core\Core;
use Foundry\Core\Service;
use Foundry\Core\Database\Database;

Core::requires('\Foundry\Core\Database\Database');

/**
 * Database implementation of the access service.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Database implements AccessService
{
    /**
     * The database access.
     * @var \Foundry\Core\Database\Database
     */
    private $_database;
    /**
     * Options required to instantiate a InMemoryRolesService.
     * @var array
     */
    public static $required_options = array();
    /**
     * The service options.
     * @var array
     */
    private $_options;
    /**
     * The name of the database table.
     * @var string
     */
    private $_table_name = "access_roles";
    /**
     * The role cache.
     */
    private $_roles = array();
    /**
     * Are all the roles in the cache.
     */
    private $_cache_all = false;

    /**
     * Create an in-memory access service.
     * 
     * @param array $options service options.
     */
    public function __construct(array $options)
    {
        Service::validate($options, self::$required_options);
        $this->_database = Core::get('\Foundry\Core\Database\Database');
        $this->_options = $options;
    }

    /**
     * Tear down the class.
     */
    public function __destruct()
    {
    }

    /**
     * Add a role definition.
     * 
     * @param string $role The role to add.
     * @return boolean true on success, false on failure.
     */
    public function addRole(Role $role)
    {
        $role_key = $role->getKey();
        $role_groups = $role->getGroups();
        if (empty($role_key) || empty($role_groups) ||
                isset($this->_roles[$role_key])) return false;

        $result = $this->_database->write_object($role, $this->_table_name);
        if ($result) {
            // Add to cache
            $this->_roles[$role_key] = $role;
        }
        
        return $result;
    }

    /**
     * Remove a role.
     * 
     * @param string $role_key The role to remove.
     */
    public function removeRole($role_key)
    {
        if (empty($role_key)) return false;
        $result = $this->_database->delete_object($this->_table_name, array("key"=>$role_key));
        if ($result && isset($this->_roles[$role_key])) {
            unset($this->_roles[$role_key]);
        }
        return $result;
    }

    /**
     * Get a role.
     * 
     * @param string $role_key The role to get.
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key)
    {
        if (isset($this->_roles[$role_key])) {
            return $this->_roles[$role_key];
        }
        $role = $this->_database->load_object('\Foundry\Core\Access\Role', $this->_table_name, array("key"=>$role_key));
        if ($role !== false) {
            $this->_roles[$role_key] = $role;
        } 
        return $role;
    }
    
    /**
     * Get all the roles.
     * 
     * @return array An array of Role objects keyed by "key"
     */
    public function getRoles()
    {
        if ($this->_cache_all) {
            return $this->_roles;
        }
        $roles = $this->_database->load_objects('\Foundry\Core\Access\Role', $this->_table_name, 'key');
        if ($roles !== false) {
            $this->_roles = $roles;
            $this->_cache_all = true;
        }
        return $roles;
    }
}
?>
