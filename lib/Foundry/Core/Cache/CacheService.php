<?php
/**
 * Cache Service Interface.
 *
 * This file contains the interface for defining a cache service.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Cache
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Cache;

/**
 * The Cache Service Interface.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Database
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
interface CacheService {
    /**
     * Cache a value.
     *
     * @param string $key The cache key.
     * @param mixed  $value The value to cache. Note $value will be serialized before caching.
     * @param int    $ttl The number of seconds the key will remain in the cache before being expunged.
     */
    public function put($key, $value, $ttl);

    /**
     * Get a cached value.
     *
     * @param string The key to get the cached value for.
     *
     * @return mixed The cached value for this key, false if the key doesn't have a cached value.
     */
    public function get($key);

    /**
     * Delete a value from the cache.
     *
     * @param string The key to delete from the cache.
     */
    public function delete($key);
}

?>