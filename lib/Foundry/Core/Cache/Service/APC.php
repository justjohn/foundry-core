<?php
namespace Foundry\Core\Cache\Service;

use Foundry\Core\Cache\CacheService;

/**
 * APC CacheService implementation.
 */
class APC implements CacheService {
    public function delete($key) {
        return apc_delete($key);
    }
    public function get($key) {
        $serialized_value = apc_fetch($key);
        $value = unserialize($serialized_value);
        return $value;
    }
    public function put($key, $value, $ttl=0) {
        $serialized_value = serialize($value);
        return apc_store($key, $serialized_value, $ttl);
    }
}

?>
