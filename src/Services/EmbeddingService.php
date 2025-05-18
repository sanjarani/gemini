<?php

namespace Sanjarani\Gemini\Services;

use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;
use Sanjarani\Gemini\Contracts\EmbeddingServiceInterface;

class EmbeddingService implements EmbeddingServiceInterface
{
    /**
     * The Gemini client instance.
     *
     * @var \Sanjarani\Gemini\Contracts\GeminiClientInterface
     */
    protected GeminiClientInterface $client;

    /**
     * The cache service instance.
     *
     * @var \Sanjarani\Gemini\Contracts\GeminiCacheInterface
     */
    protected GeminiCacheInterface $cache;

    /**
     * The logger service instance.
     *
     * @var \Sanjarani\Gemini\Contracts\GeminiLoggerInterface
     */
    protected GeminiLoggerInterface $logger;

    /**
     * The token counter utility.
     *
     * @var \Sanjarani\Gemini\Contracts\TokenCounterInterface
     */
    protected TokenCounterInterface $tokenCounter;

    /**
     * Create a new embedding service instance.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiClientInterface $client
     * @param \Sanjarani\Gemini\Contracts\GeminiCacheInterface $cache
     * @param \Sanjarani\Gemini\Contracts\GeminiLoggerInterface $logger
     * @param \Sanjarani\Gemini\Contracts\TokenCounterInterface $tokenCounter
     */
    public function __construct(
        GeminiClientInterface $client,
        GeminiCacheInterface $cache,
        GeminiLoggerInterface $logger,
        TokenCounterInterface $tokenCounter
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->tokenCounter = $tokenCounter;
    }

    /**
     * Generate embeddings for a single text.
     *
     * @param string $text
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedText(string $text, array $options = []): GeminiResponseInterface
    {
        // Default to embedding model if not specified
        $model = $options['model'] ?? 'embedding-001';
        
        // Set the model for this request
        $this->client->setModel($model);
        
        $cacheKey = $this->generateCacheKey($text, $options);
        
        $payload = [
            'model' => $model,
            'content' => [
                'parts' => [
                    [
                        'text' => $text
                    ]
                ]
            ],
            'taskType' => 'RETRIEVAL_DOCUMENT',
            'title' => $options['title'] ?? null,
        ];
        
        $this->logger->logRequest($payload, $model);
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
    }

    /**
     * Generate embeddings for multiple texts.
     *
     * @param array $texts
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedBatch(array $texts, array $options = []): GeminiResponseInterface
    {
        // For batch processing, we'll process each text individually and combine the results
        // This is because the Gemini API doesn't have a native batch embedding endpoint
        
        $results = [];
        
        foreach ($texts as $text) {
            $results[] = $this->embedText($text, $options);
        }
        
        // Return the last response as a placeholder
        // In a real implementation, you would combine the embeddings from all responses
        return end($results);
    }

    /**
     * Calculate similarity between two embeddings.
     *
     * @param array $embedding1
     * @param array $embedding2
     * @return float
     */
    public function calculateSimilarity(array $embedding1, array $embedding2): float
    {
        // Calculate cosine similarity between two embedding vectors
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        foreach ($embedding1 as $i => $value) {
            $dotProduct += $value * $embedding2[$i];
            $magnitude1 += $value * $value;
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Generate a cache key for the request.
     *
     * @param string $input
     * @param array $options
     * @return string
     */
    protected function generateCacheKey(string $input, array $options): string
    {
        $model = $options['model'] ?? 'embedding-001';
        $optionsHash = md5(json_encode($options));
        
        return "gemini_embedding_{$model}_" . md5($input . $optionsHash);
    }
}
