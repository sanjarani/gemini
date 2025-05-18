<?php

namespace Sanjarani\Gemini\Support;

use Illuminate\Log\Logger;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;

class GeminiLogger implements GeminiLoggerInterface
{
    /**
     * The log instance.
     *
     * @var \Illuminate\Log\Logger
     */
    protected Logger $log;

    /**
     * Whether logging is enabled.
     *
     * @var bool
     */
    protected bool $enabled;

    /**
     * Create a new logger instance.
     *
     * @param \Illuminate\Log\Logger $log
     * @param bool $enabled
     */
    public function __construct(
        Logger $log,
        bool $enabled = false
    ) {
        $this->log = $log;
        $this->enabled = $enabled;
    }

    /**
     * Log a request to the Gemini API.
     *
     * @param array $payload
     * @param string $model
     * @return void
     */
    public function logRequest(array $payload, string $model): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->log->info('Gemini API Request', [
            'model' => $model,
            'payload' => $this->sanitizePayload($payload),
        ]);
    }

    /**
     * Log a response from the Gemini API.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiResponseInterface $response
     * @return void
     */
    public function logResponse(GeminiResponseInterface $response): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->log->info('Gemini API Response', [
            'model' => $response->model(),
            'successful' => $response->successful(),
            'finish_reason' => $response->finishReason(),
            'token_usage' => $response->tokenUsage(),
            'estimated_cost' => $response->estimatedCost(),
        ]);
    }

    /**
     * Log an error that occurred during a Gemini API request.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function logError(\Throwable $exception): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->log->error('Gemini API Error', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    /**
     * Sanitize the payload for logging.
     *
     * @param array $payload
     * @return array
     */
    protected function sanitizePayload(array $payload): array
    {
        // Create a copy of the payload to avoid modifying the original
        $sanitized = $payload;

        // Truncate long text content for readability in logs
        if (isset($sanitized['contents'])) {
            foreach ($sanitized['contents'] as $key => $content) {
                if (isset($content['parts'])) {
                    foreach ($content['parts'] as $partKey => $part) {
                        if (isset($part['text']) && strlen($part['text']) > 100) {
                            $sanitized['contents'][$key]['parts'][$partKey]['text'] = substr($part['text'], 0, 100) . '... [truncated]';
                        }
                    }
                }
            }
        }

        return $sanitized;
    }
}
