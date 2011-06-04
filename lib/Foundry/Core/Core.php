<?php
/**
 * Handles module loading and requiring of Core Library components.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
namespace Foundry\Core;

use \Foundry\Core\Exceptions\ServiceLoadException;

Core::provides('\Foundry\Core\Core');

/**
 * Manages loading and retrieving Core Library components.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Core {
    public static $class_registry = array();

    /**
     * Register the location of a class for the autoloader.
     * 
     * @param string $class_name The name of the class.
     * @param string $class_file The location of the file containing the class.
     */
    static function register_class($class_name, $class_file) {
        self::$class_registry[$class_name] = $class_file;
    }

    /**
     * Register the component autoloader.
     */
    static function registerAutoloader() {
        // Register autoloader
        spl_autoload_register('\Foundry\Core\__autoload');
    }

    public static $included_components = array();
    public static $provided_components = array();
    public static $required_components = array();
    public static $should_load_instance = array();
    
    /**
     * Register a component and provide it's location on disk.
     * 
     * @param string $component_name The component class name (including namespace).
     * @param string $file_location The location on disk of the component.
     * @param boolean $instantiate_on_load Should an instance of the class with the
     *        same name as the component be created when the component is loaded.
     * @param boolean $load_immediately Immediatly load the component without a
     *        Core::requires(...) first.
     */
    static function provides($component_name,
                             $file_location = false,
                             $instantiate_on_load = true,
                             $load_immediately = false) {
        self::$provided_components[$component_name] = $file_location;
        self::$should_load_instance[$component_name] = $instantiate_on_load;
        if ($file_location === false) {
            // Used to register a provided class after it's already been included
            self::$included_components[$component_name] = true;
        }
        if ($load_immediately) {
            Core::requires($component_name);
        }
    }
    
    /**
     * Mark a module as required and load it (if it isn.'t already loaded)
     * 
     * @param string $component_name The module name.
     * 
     * @return mixed A loaded instance of the component if it is configured to
     *               instantiate on load, true if not.
     * 
     * @throws ServiceLoadException if the component doesn't exists or can't be loaded.
     */
    static function requires($component_name) {
        if (isset(self::$included_components[$component_name])) {
            return self::$component_instance[$component_name];
        }
        if (isset(self::$provided_components[$component_name])) {
            $result = include_once(self::$provided_components[$component_name]);
            self::$included_components[$component_name] = true;
            if ($result === false) {
                throw new ServiceLoadException(
                        "Unable to load module '$component_name': Check that '" .
                        self::$provided_components[$component_name] .
                        "' is on the path.\n");
            } else {
                if (self::$should_load_instance[$component_name]) {
                    // Load configuration and pass to instance
                    $config = self::getConfig($component_name);
                    $instance = new $component_name($config);
                    self::$component_instance[$component_name] = $instance;
                    return $instance;
                } else {
                    // Don't need to load an instance.
                    self::$component_instance[$component_name] = 1;
                    return true;
                }
            }
        } else {
            throw new ServiceLoadException(
                "Unable to load module '$component_name' since it hasn't been" .
                "registered with the classloader");
        }
    }
    
    public static $component_config = array();
    
    /**
     * Provide configuration information for a component.
     * 
     * @param string $component_name The name of the component to store configuration options for.
     * @param array $configuration The configuration options to store.
     */
    static function configure($component_name, $configuration) {
        if (empty($component_name) || empty($configuration)) return;
        self::$component_config[$component_name] = $configuration;
    }
    
    /**
     * Get the previously set configuration parameters for a component.
     *
     * @param string $component_name The name of the component to get configuration for.
     * 
     * @return array|boolean The array of configuration parameters or false if none
     *         have been provided.
     */
    static function getConfig($component_name) {
        if (isset(self::$component_config[$component_name])) {
            return self::$component_config[$component_name];
        } else {
            return false;
        }
    }
    
    public static $component_instance = array();
    
    /**
     * Get a previously loaded instance of a component.
     *
     * @param string $component_name The name of the component to get a previously
     *        loaded instance of.
     * 
     * @return mixed The previously loded instance of the class or false if the
     *         component doesn't exist or hasn't been required yet.
     */
    static function get($component_name) {
        if (isset(self::$component_instance[$component_name])) {
            return self::$component_instance[$component_name]; 
        }
        return false;
    }
}

/**
 * Autoload classes first from the models directory, then if that fails follow the
 * psr-0 autoloader guidelines.
 * 
 * @param string $class_name
 * @since 1.0.0
 * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
 */
function __autoload($class_name) {
    if (isset(Core::$class_registry[$class_name])) {
        require_once(Core::$class_registry[$class_name]);
        return true;
    }
    $class_name = ltrim($class_name, '\\');
    $file_name  = '';
    $namespace = '';
    if ($lastNsPos = strripos($class_name, '\\')) {
        $namespace = substr($class_name, 0, $lastNsPos);
        $class_name = substr($class_name, $lastNsPos + 1);
        $file_name  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $file_name .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

    $result = @include $file_name;
    return $result !== false;
}

Core::registerAutoloader();
?>
