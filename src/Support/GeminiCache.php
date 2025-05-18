<?php

namespace Sanjarani\Gemini\Support;

use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;

class GeminiCache implements GeminiCacheInterface
{
    /**
     * The cache repository instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected CacheRepository $cache;

    /**
     * Whether caching is enabled.
     *
     * @var bool
     */
    protected bool $enabled;

    /**
     * The default cache TTL in seconds.
     *
     * @var int
     */
    protected int $ttl;

    /**
     * The cache key prefix.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Create a new cache instance.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param bool $enabled
     * @param int $ttl
     * @param string $prefix
     */
    public function __construct(
        CacheRepository $cache,
        bool $enabled = false,
        int $ttl = 3600,
        string $prefix = 'gemini_cache_'
    ) {
        $this->cache = $cache;
        $this->enabled = $enabled;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    /**
     * Remember a response for a given key and payload.
     *
     * @param string $key
     * @param array $payload
     * @param \Closure $callback
     * @param int|null $ttl
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function remember(string $key, array $payload, Closure $callback, ?int $ttl = null): GeminiResponseInterface
    {
        if (!$this->enabled) {
            return $callback();
        }

        $ttl = $ttl ?? $this->ttl;
        $cacheKey = $this->prefix . $key;

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $response = $callback();

        $this->cache->put($cacheKey, $response, $ttl);

        return $response;
    }

    /**
     * Forget a cached response.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return $this->cache->forget($this->prefix . $key);
    }

    /**
     * Check if a response is cached.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->cache->has($this->prefix . $key);
    }
}
