<?php

namespace Sanjarani\Gemini\Services;

use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Contracts\TextGenerationServiceInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;

class TextGenerationService implements TextGenerationServiceInterface
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
     * Create a new text generation service instance.
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
     * Generate text from a prompt.
     *
     * @param string $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generate(string $prompt, array $options = []): GeminiResponseInterface
    {
        $model = $options['model'] ?? null;
        $cacheKey = $this->generateCacheKey($prompt, $options);
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
        ];
        
        // Only add generationConfig if there are actual settings
        $generationConfig = $this->prepareGenerationConfig($options);
        if (!empty($generationConfig)) {
            $payload['generationConfig'] = $generationConfig;
        }
        
        // Only add safetySettings if there are actual settings
        $safetySettings = $this->prepareSafetySettings($options);
        if (!empty($safetySettings)) {
            $payload['safetySettings'] = $safetySettings;
        }
        
        $this->logger->logRequest($payload, $model ?? $this->client->getModel());
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
    }

    /**
     * Generate a response from a chat conversation.
     *
     * @param array $messages
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function chat(array $messages, array $options = []): GeminiResponseInterface
    {
        $model = $options['model'] ?? null;
        $cacheKey = $this->generateCacheKey(json_encode($messages), $options);
        
        $contents = [];
        
        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';
            
            $contents[] = [
                'role' => $role,
                'parts' => [
                    [
                        'text' => $content
                    ]
                ]
            ];
        }
        
        $payload = [
            'contents' => $contents,
        ];
        
        // Only add generationConfig if there are actual settings
        $generationConfig = $this->prepareGenerationConfig($options);
        if (!empty($generationConfig)) {
            $payload['generationConfig'] = $generationConfig;
        }
        
        // Only add safetySettings if there are actual settings
        $safetySettings = $this->prepareSafetySettings($options);
        if (!empty($safetySettings)) {
            $payload['safetySettings'] = $safetySettings;
        }
        
        $this->logger->logRequest($payload, $model ?? $this->client->getModel());
        
        return $this->cache->remember($cacheKey, $payload, function () use ($payload, $model) {
            return $this->client->send($payload, $model);
        });
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
        
        return "gemini_{$model}_" . md5($input . $optionsHash);
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
        if (!isset($options['safety_settings']) || empty($options['safety_settings'])) {
            return [];
        }
        
        return (array) $options['safety_settings'];
    }
}
