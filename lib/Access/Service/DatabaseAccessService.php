<?php
namespace foundry\core\access;

/**
 * Database implementation of the access service.
 *
 * @package   Access
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */
class DatabaseAccessService implements AccessService {
    /**
     * The database access.
     * @var \foundry\core\database\Database
     */
    private $database;
    /**
     * Options required to instantiate a InMemoryRolesService.
     * @var array
     */
    public static $required_options = array();
    /**
     * The service options.
     * @var array
     */
    private $options;
    /**
     * The name of the database table.
     * @var string
     */
    private $table_name = "access_roles";
    /**
     * The role cache.
     */
    private $roles = array();
    /**
     * Are all the roles in the cache.
     */
    private $cache_all = false;

    /**
     * Create an in-memory access service.
     * @param array $options service options.
     * @param \foundry\core\database\Database The database.
     */
    public function __construct($options, \foundry\core\database\Database $database) {
        \foundry\core\Service::validate($options, self::$required_options);
        $this->database = $database;
        $this->options = $options;
    }

    /**
     * Tear down the class.
     */
    public function __destruct() {
    }

    /**
     * Add a role definition.
     * @param string $role_key The name of the role.
     * @param array $role_groups The groups assigned to the role.
     * @return boolean
     */
    public function addRole(Role $role) {
        $role_key = $role->getKey();
        $role_groups = $role->getGroups();
        if (empty($role_key) || empty($role_groups) ||
                isset($this->roles[$role_key])) return false;

        $result = $this->database->write_object($role, $this->table_name);
        if ($result) {
            // Add to cache
            $this->roles[$role_key] = $role;
        }
        
        return $result;
    }

    /**
     * Remove a role.
     * @param string $role_key The role to remove.
     */
    public function removeRole($role_key) {
        if (empty($role_key)) return false;
        $result = $this->database->delete_object($this->table_name, array("key"=>$role_key));
        if ($result && isset($this->roles[$role_key])) {
            unset($this->roles[$role_key]);
        }
        return $result;
    }

    /**
     * Get a role.
     * @param string $role_key The role to get.
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key) {
        if (isset($this->roles[$role_key])) {
            return $this->roles[$role_key];
        }
        $role = $this->database->load_object('\foundry\core\access\Role', $this->table_name, array("key"=>$role_key));
        if ($role !== false) {
            $this->roles[$role_key] = $role;
        } 
        return $role;
    }
    
    /**
     * Get all the roles.
     * @return array An array of Role objects keyed by "key"
     */
    public function getRoles() {
        if ($this->cache_all) {
            return $this->roles;
        }
        $roles = $this->database->load_objects('\foundry\core\access\Role', $this->table_name, 'key');
        if ($roles !== false) {
            $this->roles = $roles;
            $this->cache_all = true;
        }
        return $roles;
    }
}
?>
