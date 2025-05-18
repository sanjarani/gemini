<?php

namespace Sanjarani\Gemini\Contracts;

interface GeminiClientInterface
{
    /**
     * Send a request to the Gemini API.
     *
     * @param array $payload The request payload
     * @param string|null $model The model to use (overrides default)
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     * @throws \Sanjarani\Gemini\Exceptions\GeminiApiException
     */
    public function send(array $payload, ?string $model = null): GeminiResponseInterface;
    
    /**
     * Set the model to use for requests.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): self;
    
    /**
     * Get the current model being used.
     *
     * @return string
     */
    public function getModel(): string;
    
    /**
     * Set the API key to use for requests.
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey): self;
}
