<?php

namespace Sanjarani\Gemini\Contracts;

interface TokenCounterInterface
{
    /**
     * Count the number of tokens in a text.
     *
     * @param string $text The text to count tokens for
     * @return int The number of tokens
     */
    public function countTokens(string $text): int;
    
    /**
     * Estimate the cost of a request based on token usage.
     *
     * @param int $inputTokens The number of input tokens
     * @param int $outputTokens The number of output tokens
     * @param string $model The model used
     * @return float The estimated cost in USD
     */
    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float;
}
