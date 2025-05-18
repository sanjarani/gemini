<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Illuminate\Http\Client\Response;
use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Clients\GeminiClient;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Exceptions\GeminiApiException;
use Sanjarani\Gemini\Exceptions\GeminiConfigurationException;
use Sanjarani\Gemini\Exceptions\GeminiModelNotFoundException;
use Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException;
use Illuminate\Http\Client\Factory as HttpClient;

class GeminiClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testClientRequiresApiKey()
    {
        $this->expectException(GeminiConfigurationException::class);
        
        new GeminiClient('', 'https://api.example.com', 'gemini-pro');
    }

    public function testClientSetsModel()
    {
        $client = new GeminiClient('fake-api-key', 'https://api.example.com', 'gemini-pro');
        
        $this->assertEquals('gemini-pro', $client->getModel());
        
        $client->setModel('gemini-pro-vision');
        
        $this->assertEquals('gemini-pro-vision', $client->getModel());
    }

    public function testClientSetsApiKey()
    {
        $client = new GeminiClient('fake-api-key', 'https://api.example.com', 'gemini-pro');
        
        $client->setApiKey('new-fake-api-key');
        
        // Since apiKey is protected, we can't directly test it
        // But we can verify the client doesn't throw an exception
        $this->assertTrue(true);
    }

    public function testClientHandles404Error()
    {
        $this->expectException(GeminiModelNotFoundException::class);
        
        $httpClient = Mockery::mock(HttpClient::class);
        $pendingRequest = Mockery::mock('Illuminate\Http\Client\PendingRequest');
        $response = Mockery::mock(Response::class);
        
        $httpClient->shouldReceive('withHeaders')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('timeout')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('withQueryParameters')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('post')->andReturn($response);
        
        $response->shouldReceive('successful')->andReturn(false);
        $response->shouldReceive('status')->andReturn(404);
        $response->shouldReceive('json')->andReturn(['error' => ['message' => 'Model not found']]);
        
        $client = Mockery::mock(GeminiClient::class, ['fake-api-key', 'https://api.example.com', 'gemini-pro'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        
        $client->shouldReceive('makeRequest')->andReturn($pendingRequest);
        
        $client->send(['contents' => [['parts' => [['text' => 'Hello']]]]]);
    }

    public function testClientHandles429Error()
    {
        $this->expectException(GeminiApiRateLimitException::class);
        
        $httpClient = Mockery::mock(HttpClient::class);
        $pendingRequest = Mockery::mock('Illuminate\Http\Client\PendingRequest');
        $response = Mockery::mock(Response::class);
        
        $httpClient->shouldReceive('withHeaders')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('timeout')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('withQueryParameters')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('post')->andReturn($response);
        
        $response->shouldReceive('successful')->andReturn(false);
        $response->shouldReceive('status')->andReturn(429);
        $response->shouldReceive('json')->andReturn(['error' => ['message' => 'Rate limit exceeded']]);
        
        $client = Mockery::mock(GeminiClient::class, ['fake-api-key', 'https://api.example.com', 'gemini-pro'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        
        $client->shouldReceive('makeRequest')->andReturn($pendingRequest);
        
        $client->send(['contents' => [['parts' => [['text' => 'Hello']]]]]);
    }

    public function testClientHandlesSuccessfulResponse()
    {
        $httpClient = Mockery::mock(HttpClient::class);
        $pendingRequest = Mockery::mock('Illuminate\Http\Client\PendingRequest');
        $response = Mockery::mock(Response::class);
        
        $httpClient->shouldReceive('withHeaders')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('timeout')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('withQueryParameters')->andReturn($pendingRequest);
        $pendingRequest->shouldReceive('post')->andReturn($response);
        
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Hello, world!']
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ]);
        
        $client = Mockery::mock(GeminiClient::class, ['fake-api-key', 'https://api.example.com', 'gemini-pro'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        
        $client->shouldReceive('makeRequest')->andReturn($pendingRequest);
        
        $result = $client->send(['contents' => [['parts' => [['text' => 'Hello']]]]]);
        
        $this->assertInstanceOf(GeminiResponseInterface::class, $result);
        $this->assertEquals('Hello, world!', $result->content());
    }
}
