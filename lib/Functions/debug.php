<?php
/**
 * Debug functions.
 * @package functions
 */

include("krumo/class.krumo.php");

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
    krumo($arr);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

?>
