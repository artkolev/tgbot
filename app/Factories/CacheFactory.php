<?php

declare(strict_types=1);

namespace TGBot\Factories;

use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use Memcached;

class CacheFactory
{
    /**
     * @return SimpleCacheBridge
     */
    public static function build(): SimpleCacheBridge
    {
        $client = new Memcached();
        $client->addServer(getenv('MEMCACHE_SERVER') ?? 'localhost', getenv('MEMCACHE_PORT') ?? 11211);
        $pool = new MemcachedCachePool($client);
        $simpleCache = new SimpleCacheBridge($pool);

        return $simpleCache;
    }
}