<?php
/**
 * An in-memory implementation of AccessService.
 * 
 * This implementation does not persist data across sessions and must be
 * re-initialized at the start of every session. It's useful for testing and a static
 * set of roles.
 * 
 * @package   foundry\core\access
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 */
namespace foundry\core\access;

use foundry\core\Service;

/**
 * In-memory implementation of the role service.
 *
 * @package   foundry\core\access
 * @category  foundry-core
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 */
class InMemory implements AccessService {
    /**
     * The role definitions.
     * @var array
     */
    private $roles = array();
    /**
     * Options required to instantiate a InMemoryRolesService.
     * @var array
     */
    public static $required_options = array("cache");
    /**
     * The service options.
     * @var array
     */
    private $options;

    /**
     * Create an in-memory access service.
     * @param array $options service options.
     */
    public function __construct($options) {
        Service::validate($options, self::$required_options);
        $this->options = $options;
        if ($this->options["cache"] == true) {
            // Load from session cache
            if (!isset($_SESSION["role_cache"])) {
                $_SESSION["role_cache"] = array();
            }
            $this->roles = $_SESSION["role_cache"];
        }
    }

    /**
     * Tear down the class.
     */
    public function __destruct() {
        // Cache roles
        if ($this->options["cache"] == true) {
            $_SESSION["role_cache"] = $this->roles;
        }
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
        $this->roles[$role_key] = $role;
        return true;
    }

    /**
     * Remove a role.
     * @param string $role_key The role to remove.
     */
    public function removeRole($role_key) {
        if (!isset($this->roles[$role_key])) return false;
        unset($this->roles[$role_key]);
        return true;
    }

    /**
     * Get a role.
     * @param string $role_key The role to get.
     * @return Role The role if found, false otherwise.
     */
    public function getRole($role_key) {
        if (!isset($this->roles[$role_key])) return false;
        return $this->roles[$role_key];
    }
}
?>
