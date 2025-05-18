<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;
use Sanjarani\Gemini\Responses\GeminiResponse;
use Sanjarani\Gemini\Services\EmbeddingService;

class EmbeddingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testEmbedTextCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('setModel')->once()->with('embedding-001');
        $logger->shouldReceive('logRequest')->once();
        
        $expectedPayload = [
            'model' => 'embedding-001',
            'content' => [
                'parts' => [
                    [
                        'text' => 'Hello, world!'
                    ]
                ]
            ],
            'taskType' => 'RETRIEVAL_DOCUMENT',
            'title' => null,
        ];
        
        $mockResponse = new GeminiResponse([
            'embedding' => [
                'values' => [0.1, 0.2, 0.3, 0.4, 0.5]
            ]
        ], 'embedding-001');
        
        $cache->shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $payload, $callback) use ($expectedPayload) {
                return is_string($key) && $payload === $expectedPayload && is_callable($callback);
            })
            ->andReturnUsing(function ($key, $payload, $callback) {
                return $callback();
            });
        
        $client->shouldReceive('send')
            ->once()
            ->with($expectedPayload, 'embedding-001')
            ->andReturn($mockResponse);
        
        // Create service and call method
        $service = new EmbeddingService($client, $cache, $logger, $tokenCounter);
        $response = $service->embedText('Hello, world!');
        
        // Assert response
        $this->assertSame($mockResponse, $response);
    }

    public function testEmbedBatchCallsEmbedTextForEachItem()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Create a partial mock of EmbeddingService
        $service = Mockery::mock(EmbeddingService::class, [
            $client, $cache, $logger, $tokenCounter
        ])->makePartial();
        
        $texts = ['Hello', 'World', 'Testing'];
        $options = ['model' => 'embedding-001'];
        
        $mockResponse = new GeminiResponse([
            'embedding' => [
                'values' => [0.1, 0.2, 0.3, 0.4, 0.5]
            ]
        ], 'embedding-001');
        
        // Set up expectations for embedText to be called for each text
        $service->shouldReceive('embedText')
            ->times(count($texts))
            ->andReturn($mockResponse);
        
        // Call embedBatch method
        $response = $service->embedBatch($texts, $options);
        
        // Assert response is the last mock response
        $this->assertSame($mockResponse, $response);
    }

    public function testCalculateSimilarity()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Create service
        $service = new EmbeddingService($client, $cache, $logger, $tokenCounter);
        
        // Test with identical vectors (should return 1.0)
        $embedding1 = [0.1, 0.2, 0.3, 0.4, 0.5];
        $embedding2 = [0.1, 0.2, 0.3, 0.4, 0.5];
        $similarity = $service->calculateSimilarity($embedding1, $embedding2);
        $this->assertEquals(1.0, $similarity);
        
        // Test with orthogonal vectors (should return 0.0)
        $embedding1 = [1, 0, 0];
        $embedding2 = [0, 1, 0];
        $similarity = $service->calculateSimilarity($embedding1, $embedding2);
        $this->assertEquals(0.0, $similarity);
        
        // Test with somewhat similar vectors
        $embedding1 = [0.1, 0.2, 0.3];
        $embedding2 = [0.2, 0.3, 0.4];
        $similarity = $service->calculateSimilarity($embedding1, $embedding2);
        $this->assertGreaterThan(0.0, $similarity);
        $this->assertLessThan(1.0, $similarity);
        
        // Test with zero vectors (should return 0.0)
        $embedding1 = [0, 0, 0];
        $embedding2 = [0, 0, 0];
        $similarity = $service->calculateSimilarity($embedding1, $embedding2);
        $this->assertEquals(0.0, $similarity);
    }
}
