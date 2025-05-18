<?php

namespace Sanjarani\Gemini\Contracts;

interface GeminiCacheInterface
{
    /**
     * Remember a response for a given key and payload.
     *
     * @param string $key The cache key
     * @param array $payload The request payload
     * @param \Closure $callback The callback to execute if the key is not found
     * @param int|null $ttl Time to live in seconds
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function remember(string $key, array $payload, \Closure $callback, ?int $ttl = null): GeminiResponseInterface;
    
    /**
     * Forget a cached response.
     *
     * @param string $key The cache key
     * @return bool
     */
    public function forget(string $key): bool;
    
    /**
     * Check if a response is cached.
     *
     * @param string $key The cache key
     * @return bool
     */
    public function has(string $key): bool;
}
