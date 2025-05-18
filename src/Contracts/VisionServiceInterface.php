<?php

namespace Sanjarani\Gemini\Contracts;

interface VisionServiceInterface
{
    /**
     * Generate a response from an image and optional text prompt.
     *
     * @param string $imagePath Path to the image file
     * @param string|null $prompt Optional text prompt to accompany the image
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromImage(string $imagePath, ?string $prompt = null, array $options = []): GeminiResponseInterface;
    
    /**
     * Generate a response from multiple images and optional text prompt.
     *
     * @param array $imagePaths Array of paths to image files
     * @param string|null $prompt Optional text prompt to accompany the images
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromMultipleImages(array $imagePaths, ?string $prompt = null, array $options = []): GeminiResponseInterface;
    
    /**
     * Generate a response from base64 encoded image data and optional text prompt.
     *
     * @param string $base64Image Base64 encoded image data
     * @param string|null $prompt Optional text prompt to accompany the image
     * @param array $options Additional options for the request
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     */
    public function generateFromBase64Image(string $base64Image, ?string $prompt = null, array $options = []): GeminiResponseInterface;
}
