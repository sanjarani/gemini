<?php

namespace Sanjarani\Gemini\Contracts;

interface GeminiResponseInterface
{
    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content(): string;
    
    /**
     * Get the raw response data.
     *
     * @return array
     */
    public function raw(): array;
    
    /**
     * Get token usage information.
     *
     * @return array
     */
    public function tokenUsage(): array;
    
    /**
     * Get the estimated cost of the request.
     *
     * @return float
     */
    public function estimatedCost(): float;
    
    /**
     * Get the model used for the response.
     *
     * @return string
     */
    public function model(): string;
    
    /**
     * Get the finish reason.
     *
     * @return string|null
     */
    public function finishReason(): ?string;
    
    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool;
}
