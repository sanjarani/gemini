<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;
use Sanjarani\Gemini\Responses\GeminiResponse;
use Sanjarani\Gemini\Services\TextGenerationService;

class TextGenerationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateMethodCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('getModel')->andReturn('gemini-pro');
        $logger->shouldReceive('logRequest')->once();
        
        $expectedPayload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Hello, world!'
                        ]
                    ]
                ]
            ],
            'generationConfig' => [],
            'safetySettings' => [],
        ];
        
        $mockResponse = new GeminiResponse([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Hello from Gemini!']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-pro');
        
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
            ->with($expectedPayload, null)
            ->andReturn($mockResponse);
        
        // Create service and call method
        $service = new TextGenerationService($client, $cache, $logger, $tokenCounter);
        $response = $service->generate('Hello, world!');
        
        // Assert response
        $this->assertEquals('Hello from Gemini!', $response->content());
    }

    public function testChatMethodCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('getModel')->andReturn('gemini-pro');
        $logger->shouldReceive('logRequest')->once();
        
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'model', 'content' => 'Hi there!'],
            ['role' => 'user', 'content' => 'How are you?']
        ];
        
        $expectedPayload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => 'Hello'
                        ]
                    ]
                ],
                [
                    'role' => 'model',
                    'parts' => [
                        [
                            'text' => 'Hi there!'
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => 'How are you?'
                        ]
                    ]
                ]
            ],
            'generationConfig' => [],
            'safetySettings' => [],
        ];
        
        $mockResponse = new GeminiResponse([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'I\'m doing well, thank you!']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-pro');
        
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
            ->with($expectedPayload, null)
            ->andReturn($mockResponse);
        
        // Create service and call method
        $service = new TextGenerationService($client, $cache, $logger, $tokenCounter);
        $response = $service->chat($messages);
        
        // Assert response
        $this->assertEquals('I\'m doing well, thank you!', $response->content());
    }

    public function testGenerateWithOptions()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('getModel')->andReturn('gemini-pro');
        $logger->shouldReceive('logRequest')->once();
        
        $options = [
            'temperature' => 0.7,
            'top_p' => 0.9,
            'top_k' => 40,
            'max_tokens' => 100,
            'stop' => ['END'],
            'model' => 'gemini-ultra'
        ];
        
        $expectedPayload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Hello, world!'
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.9,
                'topK' => 40,
                'maxOutputTokens' => 100,
                'stopSequences' => ['END']
            ],
            'safetySettings' => [],
        ];
        
        $mockResponse = new GeminiResponse([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Hello from Gemini Ultra!']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-ultra');
        
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
            ->with($expectedPayload, 'gemini-ultra')
            ->andReturn($mockResponse);
        
        // Create service and call method
        $service = new TextGenerationService($client, $cache, $logger, $tokenCounter);
        $response = $service->generate('Hello, world!', $options);
        
        // Assert response
        $this->assertEquals('Hello from Gemini Ultra!', $response->content());
    }
}
