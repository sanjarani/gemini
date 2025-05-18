<?php

namespace Sanjarani\Gemini\Utilities;

use Sanjarani\Gemini\Contracts\TokenCounterInterface;

class TokenCounter implements TokenCounterInterface
{
    /**
     * The models configuration.
     *
     * @var array
     */
    protected array $models;

    /**
     * Create a new token counter instance.
     *
     * @param array $models
     */
    public function __construct(array $models)
    {
        $this->models = $models;
    }

    /**
     * Count the number of tokens in a text.
     *
     * @param string $text
     * @return int
     */
    public function countTokens(string $text): int
    {
        // This is a simple approximation. In a real implementation,
        // you would use a proper tokenizer library or API.
        // For English text, a rough approximation is 4 characters per token.
        return (int) ceil(mb_strlen($text) / 4);
    }

    /**
     * Estimate the cost of a request based on token usage.
     *
     * @param int $inputTokens
     * @param int $outputTokens
     * @param string $model
     * @return float
     */
    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        if (!isset($this->models[$model])) {
            return 0.0;
        }

        $modelConfig = $this->models[$model];
        
        $inputCost = ($inputTokens / 1000) * ($modelConfig['input_price_per_1k'] ?? 0);
        $outputCost = ($outputTokens / 1000) * ($modelConfig['output_price_per_1k'] ?? 0);
        
        return $inputCost + $outputCost;
    }
}
