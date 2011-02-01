<?php
/**
 * Common functions.
 * @package functions
 */

/**
 * Print a textual representation of an array or object.
 * @param object $arr
 */
function print_a($arr) {
    print(get_a($arr));
}

/**
 * Get a textual representation of an array or object.
 * @param object $arr
 * @return string
 */
function get_a($arr) {
    ob_start();
    print("<pre>");
    var_dump($arr);
    print("</pre>");
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}


// Store system errors
$system_errors = array();
/**
 * Register a global system error.
 * @param $error The error to register.
 */
function registerError($error) {
   global $system_errors;
   $system_errors[] = $error;
}
/**
 * Get an array of all the global errors that have occured.
 * @return An array of global errors.
 */
function getGlobalErrors() {
    global $system_errors;
    return $system_errors;
}

?>