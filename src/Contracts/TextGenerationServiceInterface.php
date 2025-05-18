<?php

namespace Sanjarani\Gemini\Contracts;

interface TextGenerationServiceInterface
{
    /**
     * Generate text from a prompt.
     *
     * @param string $prompt The prompt to generate text from
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generate(string $prompt, array $options = []): GeminiResponseInterface;
    
    /**
     * Generate a response from a chat conversation.
     *
     * @param array $messages The chat messages
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function chat(array $messages, array $options = []): GeminiResponseInterface;
}
