<?php
$file_base = "../";

if (!isset($file_base)) {
    $file_base = "./";
}
set_include_path(get_include_path() . PATH_SEPARATOR . $file_base . PATH_SEPARATOR . $file_base ."lib/");

#require("inc/functions.php");
require_once("Core/Exceptions.php");
require_once("Core/Service.php");

$class_registry = array();
/**
 * Register the location of a class for the autoloader.
 * @param string $class_name The name of the class.
 * @param string $class_file The location of the file containing the class.
 */
function register_class($class_name, $class_file) {
    $GLOBALS['class_registry'][$class_name] = $class_file;
}

/**
 * Autoload classes from the models directory.
 * @param string $class_name
 */
function __autoload($class_name) {
    if (isset($GLOBALS['class_registry'][$class_name])) {
        require_once($GLOBALS['class_registry'][$class_name]);
        return true;
    }
    return false;
}

spl_autoload_register('__autoload');

function print_a($arr) {
    print_r($arr);
}
?>
