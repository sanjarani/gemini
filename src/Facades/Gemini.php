<?php

namespace Sanjarani\Gemini\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface generate(string $prompt, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface chat(array $messages, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface generateFromImage(string $imagePath, ?string $prompt = null, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface generateFromMultipleImages(array $imagePaths, ?string $prompt = null, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface generateFromBase64Image(string $base64Image, ?string $prompt = null, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface embedText(string $text, array $options = [])
 * @method static \Sanjarani\Gemini\Contracts\GeminiResponseInterface embedBatch(array $texts, array $options = [])
 * @method static float calculateSimilarity(array $embedding1, array $embedding2)
 * @method static \Sanjarani\Gemini\Gemini setModel(string $model)
 * @method static \Sanjarani\Gemini\Gemini setApiKey(string $apiKey)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch generateAsync(string $prompt, array $options = [], ?string $callbackClass = null, ?string $callbackMethod = null, array $callbackParams = [])
 * @method static \Illuminate\Foundation\Bus\PendingDispatch chatAsync(array $messages, array $options = [], ?string $callbackClass = null, ?string $callbackMethod = null, array $callbackParams = [])
 * 
 * @see \Sanjarani\Gemini\Gemini
 */
class Gemini extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gemini';
    }
}
