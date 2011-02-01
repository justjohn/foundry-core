<?php
namespace foundry\core;

Core::provides('\foundry\core\Core');

// Required for all core modules
Core::provides('\foundry\core\Exceptions', 'Core/Exceptions.php', true);
Core::provides('\foundry\core\Service',    'Core/Service.php',    true);
Core::provides('\foundry\core\Model',      'Core/Model.php',      true);

// Optional functionality  
Core::provides('\foundry\core\access\Access',     'Access/Access.php');
Core::provides('\foundry\core\auth\Auth',         'Auth/Auth.php');
Core::provides('\foundry\core\config\Config',     'Config/Config.php');
Core::provides('\foundry\core\database\Database', 'Database/Database.php');
Core::provides('\foundry\core\email\Email',       'Email/Email.php');
Core::provides('\foundry\core\logging\Log',       'Log/Log.php');

// Load common functions for debugging
require_once("Functions/common.php");

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
    static function requires($module) {
        if (isset(self::$included_modules[$module])) return;
        
        if (isset(self::$provided_modules[$module])) {
            $result = @include_once(self::$provided_modules[$module]);
            self::$included_modules[$module] = true;
            if ($result === false) {
                die("Unable to load module '$module': Check that '" . self::$provided_modules[$module] . "' is on the path.\n" . get_a(debug_backtrace()));
            }
        } else {
            die("Unable to load module '$module'.<br />" . get_a(debug_backtrace()));
        }
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
