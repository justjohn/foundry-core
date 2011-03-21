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
 *     __construct($options) {
 *         // Validate that all the required options are present
 *         $valid = \foundry\core\Service::validate($options, self::$required_options);
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
        if (!is_array($options) || !is_array($required_options)) {
            throw new \foundry\core\exceptions\ServiceValidationException("Passed options are not in expected format (array) got " . get_a($options, false) .
                                                                          ", check that the options have been set");
        }
        if (empty($options) && !empty($required_options)) {
            throw new \foundry\core\exceptions\ServiceValidationException("No options set, required: " . get_a($required_options, false));
        }
        
        $option_keys = array_keys($options);
        /**
         * If all the options are present the intersection will
         * be the same size as the required options array.
         */
        $used_options = array_intersect($option_keys, $required_options);
        if(count($used_options) != count($required_options)) {
            throw new \foundry\core\exceptions\ServiceValidationException("Not all required options are present, found " . get_a($option_keys, false)
                                                                        . " required: " . get_a($required_options, false));
        }
    }
}
?>
