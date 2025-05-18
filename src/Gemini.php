<?php

namespace Sanjarani\Gemini;

use Illuminate\Foundation\Bus\PendingDispatch;
use Sanjarani\Gemini\Contracts\TextGenerationServiceInterface;
use Sanjarani\Gemini\Contracts\VisionServiceInterface;
use Sanjarani\Gemini\Contracts\EmbeddingServiceInterface;
use Sanjarani\Gemini\Jobs\RunGeminiJob;

class Gemini
{
    /**
     * The text generation service instance.
     *
     * @var \Sanjarani\Gemini\Contracts\TextGenerationServiceInterface
     */
    protected TextGenerationServiceInterface $textService;

    /**
     * The vision service instance.
     *
     * @var \Sanjarani\Gemini\Contracts\VisionServiceInterface
     */
    protected VisionServiceInterface $visionService;

    /**
     * The embedding service instance.
     *
     * @var \Sanjarani\Gemini\Contracts\EmbeddingServiceInterface
     */
    protected EmbeddingServiceInterface $embeddingService;

    /**
     * Create a new Gemini instance.
     *
     * @param \Sanjarani\Gemini\Contracts\TextGenerationServiceInterface $textService
     * @param \Sanjarani\Gemini\Contracts\VisionServiceInterface $visionService
     * @param \Sanjarani\Gemini\Contracts\EmbeddingServiceInterface $embeddingService
     */
    public function __construct(
        TextGenerationServiceInterface $textService,
        VisionServiceInterface $visionService,
        EmbeddingServiceInterface $embeddingService
    ) {
        $this->textService = $textService;
        $this->visionService = $visionService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Generate text from a prompt.
     *
     * @param string $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generate(string $prompt, array $options = [])
    {
        return $this->textService->generate($prompt, $options);
    }

    /**
     * Generate a response from a chat conversation.
     *
     * @param array $messages
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function chat(array $messages, array $options = [])
    {
        return $this->textService->chat($messages, $options);
    }

    /**
     * Generate a response from an image and optional text prompt.
     *
     * @param string $imagePath
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromImage(string $imagePath, ?string $prompt = null, array $options = [])
    {
        return $this->visionService->generateFromImage($imagePath, $prompt, $options);
    }

    /**
     * Generate a response from multiple images and optional text prompt.
     *
     * @param array $imagePaths
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromMultipleImages(array $imagePaths, ?string $prompt = null, array $options = [])
    {
        return $this->visionService->generateFromMultipleImages($imagePaths, $prompt, $options);
    }

    /**
     * Generate a response from base64 encoded image data and optional text prompt.
     *
     * @param string $base64Image
     * @param string|null $prompt
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromBase64Image(string $base64Image, ?string $prompt = null, array $options = [])
    {
        return $this->visionService->generateFromBase64Image($base64Image, $prompt, $options);
    }

    /**
     * Generate embeddings for a single text.
     *
     * @param string $text
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedText(string $text, array $options = [])
    {
        return $this->embeddingService->embedText($text, $options);
    }

    /**
     * Generate embeddings for multiple texts.
     *
     * @param array $texts
     * @param array $options
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function embedBatch(array $texts, array $options = [])
    {
        return $this->embeddingService->embedBatch($texts, $options);
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
        return $this->embeddingService->calculateSimilarity($embedding1, $embedding2);
    }

    /**
     * Set the model to use for requests.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model)
    {
        app()->make('Sanjarani\Gemini\Contracts\GeminiClientInterface')->setModel($model);
        
        return $this;
    }

    /**
     * Set the API key to use for requests.
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey)
    {
        app()->make('Sanjarani\Gemini\Contracts\GeminiClientInterface')->setApiKey($apiKey);
        
        return $this;
    }

    /**
     * Generate text from a prompt asynchronously.
     *
     * @param string $prompt
     * @param array $options
     * @param string|null $callbackClass
     * @param string|null $callbackMethod
     * @param array $callbackParams
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function generateAsync(
        string $prompt,
        array $options = [],
        ?string $callbackClass = null,
        ?string $callbackMethod = null,
        array $callbackParams = []
    ): PendingDispatch {
        $model = $options['model'] ?? null;
        
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
            'generationConfig' => $this->prepareGenerationConfig($options),
            'safetySettings' => $this->prepareSafetySettings($options),
        ];
        
        return RunGeminiJob::dispatch(
            $payload,
            $model,
            $callbackClass,
            $callbackMethod,
            $callbackParams
        );
    }

    /**
     * Generate a response from a chat conversation asynchronously.
     *
     * @param array $messages
     * @param array $options
     * @param string|null $callbackClass
     * @param string|null $callbackMethod
     * @param array $callbackParams
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function chatAsync(
        array $messages,
        array $options = [],
        ?string $callbackClass = null,
        ?string $callbackMethod = null,
        array $callbackParams = []
    ): PendingDispatch {
        $model = $options['model'] ?? null;
        
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
            'generationConfig' => $this->prepareGenerationConfig($options),
            'safetySettings' => $this->prepareSafetySettings($options),
        ];
        
        return RunGeminiJob::dispatch(
            $payload,
            $model,
            $callbackClass,
            $callbackMethod,
            $callbackParams
        );
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
