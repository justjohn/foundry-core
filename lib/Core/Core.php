<?php
namespace foundry\core;

Core::provides('\foundry\core\Core');

class Core {
    public static $class_registry = array();

    /**
     * Register the location of a class for the autoloader.
     * @param string $class_name The name of the class.
     * @param string $class_file The location of the file containing the class.
     */
    static function register_class($class_name, $class_file) {
        self::$class_registry[$class_name] = $class_file;
    }

    /**
     * Register the model autoloader.
     */
    static function registerAutoloader() {
        // Register autoloader
        spl_autoload_register('\foundry\core\__autoload');
    }

    public static $included_modules = array();
    public static $provided_modules = array();
    public static $required_modules = array();
    
    /**
     * Register a module and provide it's location on disk.
     * @param string $module The module name.
     * @param string $location The location on disk of the module.
     * @param boolean $load_now Immediatly load the module without requiring
     *                          it to be required first. (default = false)
     */
    static function provides($module, $location=false, $load_now=false) {
        self::$provided_modules[$module] = $location;
        if ($location === false) {
            // Used to register a provided class after it's already been included
            self::$included_modules[$module] = true;
        }
        if ($load_now) {
            Core::requires($module);
        }
    }
    
    /**
     * Mark a module as required and load it (if it isn.'t already loaded)
     * @param string $module The module name.
     */
    static function requires($module) {
        if (isset(self::$included_modules[$module])) return self::$module_instance[$module];
        
        //try {
            if (isset(self::$provided_modules[$module])) {
                $result = include_once(self::$provided_modules[$module]);
                self::$included_modules[$module] = true;
                if ($result === false) {
                    throw new \foundry\core\exceptions\ServiceLoadException(
                            "Unable to load module '$module': Check that '" .
                            self::$provided_modules[$module] .
                            "' is on the path.\n");
                } else {
                    self::$module_instance[$module] = $result;
                    return $result;
                }
            } else {
                throw new \foundry\core\exceptions\ServiceLoadException(
                    "Unable to load module '$module' since it hasn't been" .
                    "registered with the classloader");
            }
        /* } catch (\foundry\core\exceptions\ServiceConnectionException $exception) {
            die("<b>$module</b>: Unable to connect to service. " .
                "(<i>Exception details follow</i>)<br />\n<br />\n" .
                $exception->getMessage());

        } catch (\foundry\core\exceptions\ServiceValidationException $exception) {
            die("<b>$module</b>: Module configuration does not contain all required options. " .
                "(<i>Exception details follow</i>)<br />\n<br />\n" .
                $exception->getMessage());

        } catch (\foundry\core\exceptions\ServiceLoadException $exception) {
            die("<b>$module</b>: Unable to load service. " .
                "(<i>Exception details follow</i>)<br />\n<br />\n" .
                $exception->getMessage() . "\n\n" . get_a(debug_backtrace()));
        } */
    }
    
    public static $module_config = array();
    
    /**
     * Provide configuration information for a module.
     */
    static function configure($module, $configuration) {
        if (empty($module) || empty($configuration)) return;
        self::$module_config[$module] = $configuration;
    }
    
    static function getConfig($module) {
        if (isset(self::$module_config[$module])) {
            return self::$module_config[$module];
        } else {
            return false;
        }
    }
    
    public static $module_instance = array();
    
    static function get($module) {
        if (isset(self::$module_instance[$module])) {
            return self::$module_instance[$module]; 
        }
        return false;
    }
}

/**
 * Autoload classes from the models directory.
 * @param string $class_name
 */
function __autoload($class_name) {
    //print("autoloading $class_name<br />");
    if (isset(Core::$class_registry[$class_name])) {
        require_once(Core::$class_registry[$class_name]);
        return true;
    }
    return false;
}

Core::registerAutoloader();
?>
