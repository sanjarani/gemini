<?php

namespace Sanjarani\Gemini\Responses;

use Illuminate\Support\Arr;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;

class GeminiResponse implements GeminiResponseInterface
{
    /**
     * The raw response data.
     *
     * @var array
     */
    protected array $response;

    /**
     * The model used for the response.
     *
     * @var string
     */
    protected string $model;

    /**
     * Create a new response instance.
     *
     * @param array $response
     * @param string $model
     */
    public function __construct(array $response, string $model)
    {
        $this->response = $response;
        $this->model = $model;
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content(): string
    {
        $candidates = Arr::get($this->response, 'candidates', []);
        
        if (empty($candidates)) {
            return '';
        }
        
        $content = Arr::get($candidates[0], 'content', []);
        $parts = Arr::get($content, 'parts', []);
        
        if (empty($parts)) {
            return '';
        }
        
        return Arr::get($parts[0], 'text', '');
    }

    /**
     * Get the raw response data.
     *
     * @return array
     */
    public function raw(): array
    {
        return $this->response;
    }

    /**
     * Get token usage information.
     *
     * @return array
     */
    public function tokenUsage(): array
    {
        $usage = Arr::get($this->response, 'usageMetadata', []);
        
        return [
            'prompt_tokens' => Arr::get($usage, 'promptTokenCount', 0),
            'completion_tokens' => Arr::get($usage, 'candidatesTokenCount', 0),
            'total_tokens' => Arr::get($usage, 'totalTokenCount', 0),
        ];
    }

    /**
     * Get the estimated cost of the request.
     *
     * @return float
     */
    public function estimatedCost(): float
    {
        // This would typically be calculated based on the token usage and model pricing
        // For now, returning 0 as a placeholder
        return 0.0;
    }

    /**
     * Get the model used for the response.
     *
     * @return string
     */
    public function model(): string
    {
        return $this->model;
    }

    /**
     * Get the finish reason.
     *
     * @return string|null
     */
    public function finishReason(): ?string
    {
        $candidates = Arr::get($this->response, 'candidates', []);
        
        if (empty($candidates)) {
            return null;
        }
        
        return Arr::get($candidates[0], 'finishReason');
    }

    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return !empty(Arr::get($this->response, 'candidates', []));
    }
}
