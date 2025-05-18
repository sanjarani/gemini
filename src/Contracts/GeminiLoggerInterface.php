<?php

namespace Sanjarani\Gemini\Contracts;

use Sanjarani\Gemini\Contracts\GeminiResponseInterface;

interface GeminiLoggerInterface
{
    /**
     * Log a request to the Gemini API.
     *
     * @param array $payload
     * @param string $model
     * @return void
     */
    public function logRequest(array $payload, string $model): void;
    
    /**
     * Log a response from the Gemini API.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiResponseInterface $response
     * @return void
     */
    public function logResponse(GeminiResponseInterface $response): void;
    
    /**
     * Log an error that occurred during a Gemini API request.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function logError(\Throwable $exception): void;
}
