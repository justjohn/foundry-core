<?php
/**
 * Caching API and service loader.
 * 
 * This file contains the cache API and code for loading cache services.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Cache
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */
 
namespace Foundry\Core\Cache;

use Foundry\Core\Core;
use Foundry\Core\Service;
use Foundry\Core\Exceptions\ServiceLoadException;
use Foundry\Core\Logging\Log;

Core::requires('\Foundry\Core\Logging\Log');

/**
 * Load the CacheService interface.
 */
require_once("Cache/CacheService.php");


/**
 * Cache API and service loader.
 * 
 * @category  Foundry-Core
 * @package   Foundry\Core\Cache
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
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
        $config = Core::getConfig('\Foundry\Core\Cache\Cache');
        Service::validate($config, self::$required_options);
        $service = $config["service"];
        $service_config = $config["service_config"];

        // include auth class
        include_once("Cache/Service/$service.php");
        $auth_service = 'Foundry\Core\Cache\\'.$service;
        if (!class_exists($service)) {
            Log::error("Cache::__construct", "Unable to load cache service class '$service'.");
            throw new ServiceLoadException("Unable to load cache service class '$service'.");
        } else {
            $this->auth_service = new $service($service_config);
        }
    }
}

?>