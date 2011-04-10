<?php
/**
 * Common functions for core libraries.
 * 
 * @category  foundry-core
 * @package   
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

/**
 * Print a textual representation of an array or object.
 *
 * @param object $object The object or array to print output for.
 * @param boolean $html_format Should the output be HTML formatted (surrounded
 *        with <pre> tags and escaped). Defaults to true if not set.
 */
function print_a($object, $html_format=true)
{
    print(get_a($object, $html_format));
}

/**
 * Get a textual representation of an array or object.
 *
 * @param object|array $object The object or array to get output for.
 * @param boolean $html_format Should the output be HTML formatted (surrounded
 *        with <pre> tags). Defaults to true if not set.
 * 
 * @return string The value of an object from var_dump or an array from print_r
 *         that is optionally (if $html_format is true) surrounded with &lt;pre&gt;
 *         tags and escaped.
 */
function get_a($object, $html_format = true)
{
    if (is_array($object)) {
        $content = print_r($object, true);
    } else {
        ob_start();
        var_dump($object);
        $content = ob_get_contents();
        ob_end_clean();
    }
    if ($html_format) {
        $content = "<pre>".htmlentities($content)."</pre>";
    }
    return $content;
}

/**
 * A case-insensitive array sort.
 *
 * @param array $array The array to sort.
 * 
 * @return a sorted array.
 */
function cksort(&$array)
{
    return uksort($array, "cmp");
}

function cmp($a, $b)
{
    return strcasecmp($a, $b);
}

// Store system errors
$system_errors = array();
/**
 * Register a global system error.
 * 
 * @param $error The error to register.
 */
function registerError($error)
{
   global $system_errors;
   $system_errors[] = $error;
}
/**
 * Get an array of all the global errors that have occured.
 * 
 * @return An array of global errors.
 */
function getGlobalErrors()
{
    global $system_errors;
    return $system_errors;
}

?>
