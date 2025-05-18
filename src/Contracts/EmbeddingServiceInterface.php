<?php

namespace Sanjarani\Gemini\Contracts;

interface EmbeddingServiceInterface
{
    /**
     * Generate embeddings for a single text.
     *
     * @param string $text The text to generate embeddings for
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedText(string $text, array $options = []): GeminiResponseInterface;
    
    /**
     * Generate embeddings for multiple texts.
     *
     * @param array $texts Array of texts to generate embeddings for
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedBatch(array $texts, array $options = []): GeminiResponseInterface;
    
    /**
     * Calculate similarity between two embeddings.
     *
     * @param array $embedding1 First embedding vector
     * @param array $embedding2 Second embedding vector
     * @return float Similarity score between 0 and 1
     */
    public function calculateSimilarity(array $embedding1, array $embedding2): float;
}
