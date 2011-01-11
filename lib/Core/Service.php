<?php
namespace foundry\core;

/**
 * The Service class provides basic configuration option validation during
 * service instantiation.
 *
 * Basic Usage:
 * <code>
 * class SomeService {
 *     public static $required_options = array("hostname",
 *                                             "username",
 *                                             "password");
 *     __constructor($options) {
 *         // Validate that all the required options are present
 *         $valid = Service::validate($options, self::$required_options);
 *         if (!$valid) {
               registerError("Unable to load SomeService: configuration options not set.");
 *         }
 *     }
 * }
 * </code>
 */
class Service {
    /**
     * Validate all required optoins are present in options.
     * @param array $options
     * @param array $required_options
     * @throws ServiceValidationException All required options are not present.
     */
    public static function validate($options, $required_options) {
        if (!is_array($options) || !is_array($required_options)) return false;
        if (empty($options) && !empty($required_options)) return false;
        $option_keys = array_keys($options);
        /**
         * If all the options are present the intersection will
         * be the same size as the required options array.
         */
        $used_options = array_intersect($option_keys, $required_options);
        if(count($used_options) != count($required_options)) {
            throw new ServiceValidationException("Not all required options are present");
        }
    }
}
?>
