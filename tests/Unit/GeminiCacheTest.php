<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Support\GeminiCache;

class GeminiCacheTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRememberWhenCacheIsDisabled()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        $mockResponse = Mockery::mock(GeminiResponseInterface::class);
        
        // Create cache with disabled flag
        $cache = new GeminiCache($cacheRepo, false);
        
        // Set up callback
        $callback = function () use ($mockResponse) {
            return $mockResponse;
        };
        
        // Cache should not be called when disabled
        $cacheRepo->shouldNotReceive('has');
        $cacheRepo->shouldNotReceive('get');
        $cacheRepo->shouldNotReceive('put');
        
        // Call remember method
        $result = $cache->remember('test_key', ['test' => 'payload'], $callback);
        
        // Assert result is from callback
        $this->assertSame($mockResponse, $result);
    }

    public function testRememberWhenCacheHasKey()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        $mockResponse = Mockery::mock(GeminiResponseInterface::class);
        
        // Create cache with enabled flag
        $cache = new GeminiCache($cacheRepo, true);
        
        // Set up expectations
        $cacheRepo->shouldReceive('has')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn(true);
            
        $cacheRepo->shouldReceive('get')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn($mockResponse);
        
        // Callback should not be called when cache hit
        $callback = function () {
            $this->fail('Callback should not be called when cache hit');
        };
        
        // Call remember method
        $result = $cache->remember('test_key', ['test' => 'payload'], $callback);
        
        // Assert result is from cache
        $this->assertSame($mockResponse, $result);
    }

    public function testRememberWhenCacheMisses()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        $mockResponse = Mockery::mock(GeminiResponseInterface::class);
        
        // Create cache with enabled flag
        $cache = new GeminiCache($cacheRepo, true, 3600, 'gemini_cache_');
        
        // Set up expectations
        $cacheRepo->shouldReceive('has')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn(false);
        
        $cacheRepo->shouldReceive('put')
            ->once()
            ->with('gemini_cache_test_key', $mockResponse, 3600)
            ->andReturn(true);
        
        // Callback should be called when cache misses
        $callback = function () use ($mockResponse) {
            return $mockResponse;
        };
        
        // Call remember method
        $result = $cache->remember('test_key', ['test' => 'payload'], $callback);
        
        // Assert result is from callback
        $this->assertSame($mockResponse, $result);
    }

    public function testRememberWithCustomTtl()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        $mockResponse = Mockery::mock(GeminiResponseInterface::class);
        
        // Create cache with enabled flag
        $cache = new GeminiCache($cacheRepo, true, 3600, 'gemini_cache_');
        
        // Set up expectations
        $cacheRepo->shouldReceive('has')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn(false);
        
        $cacheRepo->shouldReceive('put')
            ->once()
            ->with('gemini_cache_test_key', $mockResponse, 7200)
            ->andReturn(true);
        
        // Callback should be called when cache misses
        $callback = function () use ($mockResponse) {
            return $mockResponse;
        };
        
        // Call remember method with custom TTL
        $result = $cache->remember('test_key', ['test' => 'payload'], $callback, 7200);
        
        // Assert result is from callback
        $this->assertSame($mockResponse, $result);
    }

    public function testForget()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        
        // Create cache
        $cache = new GeminiCache($cacheRepo, true);
        
        // Set up expectations
        $cacheRepo->shouldReceive('forget')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn(true);
        
        // Call forget method
        $result = $cache->forget('test_key');
        
        // Assert result
        $this->assertTrue($result);
    }

    public function testHas()
    {
        // Mock dependencies
        $cacheRepo = Mockery::mock(CacheRepository::class);
        
        // Create cache
        $cache = new GeminiCache($cacheRepo, true);
        
        // Set up expectations
        $cacheRepo->shouldReceive('has')
            ->once()
            ->with('gemini_cache_test_key')
            ->andReturn(true);
        
        // Call has method
        $result = $cache->has('test_key');
        
        // Assert result
        $this->assertTrue($result);
    }
}
