<?php

namespace Sanjarani\Gemini\Tests\Unit;

use Illuminate\Contracts\Logging\Log;
use Mockery;
use Orchestra\Testbench\TestCase;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Support\GeminiLogger;

class GeminiLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLogRequestWhenLoggingIsDisabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        
        // Create logger with disabled flag
        $logger = new GeminiLogger($log, false);
        
        // Log should not be called when disabled
        $log->shouldNotReceive('info');
        
        // Call logRequest method
        $logger->logRequest(['test' => 'payload'], 'gemini-pro');
    }

    public function testLogRequestWhenLoggingIsEnabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        
        // Create logger with enabled flag
        $logger = new GeminiLogger($log, true);
        
        // Set up expectations
        $log->shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Gemini API Request' &&
                       isset($context['model']) &&
                       isset($context['payload']) &&
                       $context['model'] === 'gemini-pro';
            });
        
        // Call logRequest method
        $logger->logRequest(['test' => 'payload'], 'gemini-pro');
    }

    public function testLogResponseWhenLoggingIsDisabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        $response = Mockery::mock(GeminiResponseInterface::class);
        
        // Create logger with disabled flag
        $logger = new GeminiLogger($log, false);
        
        // Log should not be called when disabled
        $log->shouldNotReceive('info');
        
        // Call logResponse method
        $logger->logResponse($response);
    }

    public function testLogResponseWhenLoggingIsEnabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        $response = Mockery::mock(GeminiResponseInterface::class);
        
        // Set up response expectations
        $response->shouldReceive('model')->andReturn('gemini-pro');
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('finishReason')->andReturn('STOP');
        $response->shouldReceive('tokenUsage')->andReturn([
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30
        ]);
        $response->shouldReceive('estimatedCost')->andReturn(0.0005);
        
        // Create logger with enabled flag
        $logger = new GeminiLogger($log, true);
        
        // Set up log expectations
        $log->shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Gemini API Response' &&
                       isset($context['model']) &&
                       isset($context['successful']) &&
                       isset($context['finish_reason']) &&
                       isset($context['token_usage']) &&
                       isset($context['estimated_cost']) &&
                       $context['model'] === 'gemini-pro' &&
                       $context['successful'] === true &&
                       $context['finish_reason'] === 'STOP';
            });
        
        // Call logResponse method
        $logger->logResponse($response);
    }

    public function testLogErrorWhenLoggingIsDisabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        $exception = new \Exception('Test error');
        
        // Create logger with disabled flag
        $logger = new GeminiLogger($log, false);
        
        // Log should not be called when disabled
        $log->shouldNotReceive('error');
        
        // Call logError method
        $logger->logError($exception);
    }

    public function testLogErrorWhenLoggingIsEnabled()
    {
        // Mock dependencies
        $log = Mockery::mock(Log::class);
        $exception = new \Exception('Test error', 500);
        
        // Create logger with enabled flag
        $logger = new GeminiLogger($log, true);
        
        // Set up expectations
        $log->shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Gemini API Error' &&
                       isset($context['message']) &&
                       isset($context['code']) &&
                       isset($context['file']) &&
                       isset($context['line']) &&
                       $context['message'] === 'Test error' &&
                       $context['code'] === 500;
            });
        
        // Call logError method
        $logger->logError($exception);
    }
}
