<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;
use Sanjarani\Gemini\Responses\GeminiResponse;
use Sanjarani\Gemini\Services\VisionService;

class VisionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateFromImageCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('setModel')->once()->with('gemini-pro-vision');
        $logger->shouldReceive('logRequest')->once();
        
        // Create a partial mock of VisionService to mock file operations
        $service = Mockery::mock(VisionService::class, [
            $client, $cache, $logger, $tokenCounter
        ])->makePartial();
        
        $service->shouldReceive('getImageData')->andReturn('base64encodedimage');
        $service->shouldReceive('getMimeType')->andReturn('image/jpeg');
        
        $expectedPayload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Describe this image'
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => 'image/jpeg',
                                'data' => 'base64encodedimage'
                            ]
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
                            ['text' => 'This is an image of a cat.']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-pro-vision');
        
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
            ->with($expectedPayload, 'gemini-pro-vision')
            ->andReturn($mockResponse);
        
        // Call method
        $response = $service->generateFromImage('/path/to/image.jpg', 'Describe this image');
        
        // Assert response
        $this->assertEquals('This is an image of a cat.', $response->content());
    }

    public function testGenerateFromMultipleImagesCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('setModel')->once()->with('gemini-pro-vision');
        $logger->shouldReceive('logRequest')->once();
        
        // Create a partial mock of VisionService to mock file operations
        $service = Mockery::mock(VisionService::class, [
            $client, $cache, $logger, $tokenCounter
        ])->makePartial();
        
        $service->shouldReceive('getImageData')
            ->andReturn('base64encodedimage1', 'base64encodedimage2');
        $service->shouldReceive('getMimeType')
            ->andReturn('image/jpeg', 'image/png');
        
        $expectedPayload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Compare these images'
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => 'image/jpeg',
                                'data' => 'base64encodedimage1'
                            ]
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => 'image/png',
                                'data' => 'base64encodedimage2'
                            ]
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
                            ['text' => 'The first image is a cat, the second is a dog.']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-pro-vision');
        
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
            ->with($expectedPayload, 'gemini-pro-vision')
            ->andReturn($mockResponse);
        
        // Call method
        $response = $service->generateFromMultipleImages(
            ['/path/to/image1.jpg', '/path/to/image2.png'],
            'Compare these images'
        );
        
        // Assert response
        $this->assertEquals('The first image is a cat, the second is a dog.', $response->content());
    }

    public function testGenerateFromBase64ImageCallsClient()
    {
        // Mock dependencies
        $client = Mockery::mock(GeminiClientInterface::class);
        $cache = Mockery::mock(GeminiCacheInterface::class);
        $logger = Mockery::mock(GeminiLoggerInterface::class);
        $tokenCounter = Mockery::mock(TokenCounterInterface::class);
        
        // Set up expectations
        $client->shouldReceive('setModel')->once()->with('gemini-pro-vision');
        $logger->shouldReceive('logRequest')->once();
        
        // Create a partial mock of VisionService to mock file operations
        $service = Mockery::mock(VisionService::class, [
            $client, $cache, $logger, $tokenCounter
        ])->makePartial();
        
        $service->shouldReceive('getMimeTypeFromBase64')
            ->andReturn('image/jpeg');
        
        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKAP/2Q==';
        
        $expectedPayload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'What is in this image?'
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => 'image/jpeg',
                                'data' => $base64Image
                            ]
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
                            ['text' => 'This is a blank image.']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ], 'gemini-pro-vision');
        
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
            ->with($expectedPayload, 'gemini-pro-vision')
            ->andReturn($mockResponse);
        
        // Call method
        $response = $service->generateFromBase64Image(
            $base64Image,
            'What is in this image?'
        );
        
        // Assert response
        $this->assertEquals('This is a blank image.', $response->content());
    }
}
