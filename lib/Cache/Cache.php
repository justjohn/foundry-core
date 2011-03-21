<?php
/**
 * Caching API and service loader.
 * 
 * This file contains the cache API and code for loading cache services.
 *
 * @package   Cache
 * @author    John Roepke <john@justjohn.us>
 * @copyright &copy; 2010 John Roepke
 */
 
namespace foundry\core\cache;

use foundry\core\Core;
use foundry\core\Service;
use foundry\core\exceptions\ServiceLoadException;
use foundry\core\logging\Log;

Core::requires('\foundry\core\logging\Log');

/**
 * Load the CacheService interface.
 */
require_once("Cache/CacheService.php");


/**
 * Cache API and service loader.
 * 
 * @package   Cache
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010 John Roepke
 */
class Cache {
    /**
     * The configuration options required to initialize an Cache service.
     */
    public static $required_options = array("service", "service_config");
    /**
     * The cache service.
     * @var CacheService
     */
    private $cache_service;
    
    /**
     * Setup the cache manager.
     */
    function __construct() {
        $config = Core::getConfig('\foundry\core\cache\Cache');
        Service::validate($config, self::$required_options);
        $service = $config["service"];
        $service_config = $config["service_config"];

        // include auth class
        include_once("Cache/Service/$service.php");
        $auth_service = 'foundry\core\cache\\'.$service;
        if (!class_exists($service)) {
            Log::error("Cache::__construct", "Unable to load cache service class '$service'.");
            throw new ServiceLoadException("Unable to load cache service class '$service'.");
        } else {
            $this->auth_service = new $service($service_config);
        }
    }
}

?>