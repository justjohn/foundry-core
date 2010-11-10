<?php
// Include library-wide functions
require_once("Core/Exceptions.php");
require_once("Core/Service.php");
require_once("Core/Model.php");
require_once("Log/LogManager.php");

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
}

/**
 * Autoload classes from the models directory.
 * @param string $class_name
 */
function __autoload($class_name) {
    if (isset(Core::$class_registry[$class_name])) {
        require_once(Core::$class_registry[$class_name]);
        return true;
    }
    return false;
}

// Register autoloader
spl_autoload_register('__autoload');
?>
