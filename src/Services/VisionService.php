<?php

namespace Sanjarani\Gemini\Services;

use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Contracts\VisionServiceInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;

class VisionService implements VisionServiceInterface
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
     * Create a new vision service instance.
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
     * Generate a response from an image and optional text prompt.
     *
     * @param string $imagePath
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromImage(string $imagePath, ?string $prompt = null, array $options = []): GeminiResponseInterface
    {
        // Default to vision model if not specified
        $model = $options['model'] ?? 'gemini-pro-vision';
        
        // Ensure we're using a vision-capable model
        if (!str_contains($model, 'vision')) {
            $model = 'gemini-pro-vision';
        }
        
        // Set the model for this request
        $this->client->setModel($model);
        
        // Get image data
        $imageData = $this->getImageData($imagePath);
        $mimeType = $this->getMimeType($imagePath);
        
        // Prepare parts array
        $parts = [];
        
        // Add text prompt if provided
        if ($prompt) {
            $parts[] = [
                'text' => $prompt
            ];
        }
        
        // Add image data
        $parts[] = [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $imageData
            ]
        ];
        
        // Prepare payload
        $payload = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => $this->prepareGenerationConfig($options),
            'safetySettings' => $this->prepareSafetySettings($options),
        ];
        
        // Generate cache key
        $cacheKey = $this->generateCacheKey($imagePath . ($prompt ?? ''), $options);
        
        $this->logger->logRequest($payload, $model);
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
    }

    /**
     * Generate a response from multiple images and optional text prompt.
     *
     * @param array $imagePaths
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromMultipleImages(array $imagePaths, ?string $prompt = null, array $options = []): GeminiResponseInterface
    {
        // Default to vision model if not specified
        $model = $options['model'] ?? 'gemini-pro-vision';
        
        // Ensure we're using a vision-capable model
        if (!str_contains($model, 'vision')) {
            $model = 'gemini-pro-vision';
        }
        
        // Set the model for this request
        $this->client->setModel($model);
        
        // Prepare parts array
        $parts = [];
        
        // Add text prompt if provided
        if ($prompt) {
            $parts[] = [
                'text' => $prompt
            ];
        }
        
        // Add all images
        foreach ($imagePaths as $imagePath) {
            $imageData = $this->getImageData($imagePath);
            $mimeType = $this->getMimeType($imagePath);
            
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $imageData
                ]
            ];
        }
        
        // Prepare payload
        $payload = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => $this->prepareGenerationConfig($options),
            'safetySettings' => $this->prepareSafetySettings($options),
        ];
        
        // Generate cache key
        $cacheKey = $this->generateCacheKey(implode('', $imagePaths) . ($prompt ?? ''), $options);
        
        $this->logger->logRequest($payload, $model);
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
    }

    /**
     * Generate a response from base64 encoded image data and optional text prompt.
     *
     * @param string $base64Image
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromBase64Image(string $base64Image, ?string $prompt = null, array $options = []): GeminiResponseInterface
    {
        // Default to vision model if not specified
        $model = $options['model'] ?? 'gemini-pro-vision';
        
        // Ensure we're using a vision-capable model
        if (!str_contains($model, 'vision')) {
            $model = 'gemini-pro-vision';
        }
        
        // Set the model for this request
        $this->client->setModel($model);
        
        // Get mime type from base64 string if possible
        $mimeType = $this->getMimeTypeFromBase64($base64Image);
        
        // Prepare parts array
        $parts = [];
        
        // Add text prompt if provided
        if ($prompt) {
            $parts[] = [
                'text' => $prompt
            ];
        }
        
        // Add image data
        $parts[] = [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $base64Image
            ]
        ];
        
        // Prepare payload
        $payload = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => $this->prepareGenerationConfig($options),
            'safetySettings' => $this->prepareSafetySettings($options),
        ];
        
        // Generate cache key
        $cacheKey = $this->generateCacheKey(md5($base64Image) . ($prompt ?? ''), $options);
        
        $this->logger->logRequest($payload, $model);
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
    }

    /**
     * Get base64 encoded image data from a file path.
     *
     * @param string $imagePath
     * @return string
     */
    protected function getImageData(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException("Image file not found: {$imagePath}");
        }
        
        return base64_encode(file_get_contents($imagePath));
    }

    /**
     * Get the MIME type of an image file.
     *
     * @param string $imagePath
     * @return string
     */
    protected function getMimeType(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException("Image file not found: {$imagePath}");
        }
        
        $mimeType = mime_content_type($imagePath);
        
        if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
            throw new \InvalidArgumentException("Invalid image file: {$imagePath}");
        }
        
        return $mimeType;
    }

    /**
     * Extract MIME type from a base64 encoded image.
     *
     * @param string $base64Image
     * @return string
     */
    protected function getMimeTypeFromBase64(string $base64Image): string
    {
        // Check if the base64 string contains MIME type information
        if (preg_match('/^data:(image\/[a-zA-Z0-9+.-]+);base64,/', $base64Image, $matches)) {
            return $matches[1];
        }
        
        // Default to PNG if we can't determine the MIME type
        return 'image/png';
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
        $model = $options['model'] ?? $this->client->getModel();
        $optionsHash = md5(json_encode($options));
        
        return "gemini_vision_{$model}_" . md5($input . $optionsHash);
    }

    /**
     * Prepare generation config from options.
     *
     * @param array $options
     * @return array
     */
    protected function prepareGenerationConfig(array $options): array
    {
        $config = [];
        
        if (isset($options['temperature'])) {
            $config['temperature'] = (float) $options['temperature'];
        }
        
        if (isset($options['top_p'])) {
            $config['topP'] = (float) $options['top_p'];
        }
        
        if (isset($options['top_k'])) {
            $config['topK'] = (int) $options['top_k'];
        }
        
        if (isset($options['max_tokens'])) {
            $config['maxOutputTokens'] = (int) $options['max_tokens'];
        }
        
        if (isset($options['stop'])) {
            $config['stopSequences'] = (array) $options['stop'];
        }
        
        return $config;
    }

    /**
     * Prepare safety settings from options.
     *
     * @param array $options
     * @return array
     */
    protected function prepareSafetySettings(array $options): array
    {
        if (!isset($options['safety_settings'])) {
            return [];
        }
        
        return (array) $options['safety_settings'];
    }
}
