# Gemini for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)
[![Total Downloads](https://img.shields.io/packagist/dt/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)
[![License](https://img.shields.io/packagist/l/sanjarani/gemini.svg?style=flat-square)](https://packagist.org/packages/sanjarani/gemini)

A professional, extensible, and scalable package for integrating the Google Gemini API into the Laravel framework.

## Features

- Support for all Gemini models (gemini-pro, gemini-pro-vision, etc.)
- Capability to switch models at runtime
- Built on Laravel HTTP Client with precise error handling
- Service-oriented architecture with Dependency Injection support
- Support for Prompt Caching to save on tokens and achieve faster response times
- Input/Output token counting with cost estimation
- Support for async execution using dedicated Queues and Jobs
- Precise rate limiting control and handling of Rate Limit errors
- Facade for easy access
- Middleware for request management
- Configurable logging system
- Support for generating embeddings and calculating text similarity

## Installation

You can install this package via Composer:

```bash
composer require sanjarani/gemini
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --tag=gemini-config
```

## Configuration

After publishing the configuration file, you can configure the settings in `config/gemini.php`. You can also use environment variables in your `.env` file:

```env
GEMINI_API_KEY=your-api-key
GEMINI_DEFAULT_MODEL=gemini-pro
GEMINI_REQUEST_TIMEOUT=30
GEMINI_ENABLE_CACHE=false
GEMINI_CACHE_TTL=3600
GEMINI_ENABLE_LOGGING=false
```

## Usage

### Text Generation with Gemini Model

```php
use Sanjarani\Gemini\Facades\Gemini;

// Simple usage
$response = Gemini::generate('Tell me about Artificial Intelligence');
echo $response->content();

// Usage with additional configuration
$response = Gemini::generate('Tell me about Artificial Intelligence', [
    'temperature' => 0.7,
    'top_p' => 0.9,
    'top_k' => 40,
    'max_tokens' => 500,
]);
```

### Chatting with Gemini Model

```php
use Sanjarani\Gemini\Facades\Gemini;

$messages = [
    ['role' => 'user', 'content' => 'Hello, how are you?'],
    ['role' => 'model', 'content' => 'Hello! I am doing well, how can I help you?'],
    ['role' => 'user', 'content' => 'I want to learn more about PHP programming.'],
];

$response = Gemini::chat($messages);
echo $response->content();
```

### Using Vision Model for Image Analysis

```php
use Sanjarani\Gemini\Facades\Gemini;

// Analyze a single image
$response = Gemini::generateFromImage(
    '/path/to/image.jpg',
    'Describe this image'
);

// Analyze multiple images
$response = Gemini::generateFromMultipleImages(
    ['/path/to/image1.jpg', '/path/to/image2.jpg'],
    'Compare these two images'
);

### Using Base64 Image
$base64Image = 'data:image/jpeg;base64,...';
$response = Gemini::generateFromBase64Image(
    $base64Image,
    'Describe this image'
);
```

### Embeddings

```php
// Create embedding for text
$response = Gemini::embedText('This is a sample text');
$embedding = $response->raw()['embedding']['values'];

// Create embedding for a batch of texts
$texts = ['First text', 'Second text', 'Third text'];
$response = Gemini::embedBatch($texts);

// Calculate similarity between two embeddings
$embedding1 = $response1->raw()['embedding']['values'];
$embedding2 = $response2->raw()['embedding']['values'];
$similarity = Gemini::calculateSimilarity($embedding1, $embedding2);
echo "Similarity score: {$similarity}"; // A number between 0 and 1
```

### Runtime Model Switching

```php
use Sanjarani\Gemini\Facades\Gemini;

$response = Gemini::setModel('gemini-ultra')
    ->generate('Write a short story');
```

### Async Execution

```php
use Sanjarani\Gemini\Facades\Gemini;
use App\Handlers\GeminiResponseHandler;

// Async execution with callback
Gemini::generateAsync(
    'Write an article about AI',
    [],
    GeminiResponseHandler::class,
    'handleResponse',
    ['article_id' => 123]
);
```

### Accessing Response Data

```php
use Sanjarani\Gemini\Facades\Gemini;

$response = Gemini::generate('Hello, how are you?');

// Access response text
echo $response->content();

// Access raw response data
$rawData = $response->raw();

// Access token usage information
$tokenUsage = $response->tokenUsage();
echo "Prompt tokens: {$tokenUsage['prompt_tokens']}\n";
echo "Completion tokens: {$tokenUsage['completion_tokens']}\n";
echo "Total tokens: {$tokenUsage['total_tokens']}\n";

// Access estimated cost
echo "Estimated cost: \${$response->estimatedCost()}\n";

// Access used model
echo "Model: {$response->model()}\n";

// Access finish reason
echo "Finish reason: {$response->finishReason()}\n";

// Check if request was successful
if ($response->successful()) {
    echo "Request was successful!\n";
}
```

### Dependency Injection

```php
use Sanjarani\Gemini\Contracts\TextGenerationServiceInterface;

class MyService
{
    protected $textService;
    
    public function __construct(TextGenerationServiceInterface $textService)
    {
        $this->textService = $textService;
    }
    
    public function generateContent($prompt)
    {
        return $this->textService->generate($prompt);
    }
}
```

### Rate Limiting Middleware

In `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'gemini.limit' => \Sanjarani\Gemini\Middleware\GeminiRateLimitMiddleware::class,
];
```

In Routes:

```php
Route::post('/generate', [GeminiController::class, 'generate'])
    ->middleware('gemini.limit:60,1'); // 60 requests per minute
```

## Artisan Commands

### Test Gemini API

```bash
php artisan gemini:test "Hello, how are you?"
```

### Clear Gemini Cache

```bash
php artisan gemini:cache-clear
```

## Error Handling

This package handles various errors using dedicated exceptions:

```php
use Sanjarani\Gemini\Facades\Gemini;
use Sanjarani\Gemini\Exceptions\GeminiApiException;
use Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException;
use Sanjarani\Gemini\Exceptions\GeminiModelNotFoundException;
use Sanjarani\Gemini\Exceptions\GeminiNetworkException;
use Sanjarani\Gemini\Exceptions\GeminiConfigurationException;

try {
    $response = Gemini::generate('Hello, how are you?');
} catch (GeminiApiRateLimitException $e) {
    // Handle rate limit error
    echo "Rate limit exceeded: {$e->getMessage()}";
} catch (GeminiModelNotFoundException $e) {
    // Handle model not found error
    echo "Model not found: {$e->getMessage()}";
} catch (GeminiNetworkException $e) {
    // Handle network error
    echo "Network error: {$e->getMessage()}";
} catch (GeminiConfigurationException $e) {
    // Handle configuration error
    echo "Configuration error: {$e->getMessage()}";
} catch (GeminiApiException $e) {
    // Handle other API errors
    echo "API error: {$e->getMessage()}";
}
```

## Testing

To run tests:

```bash
composer test
```

## Contribution

Contributions are welcome! Please run the tests before submitting a pull request.

## License

This package is released under the MIT License. Please see the [LICENSE](LICENSE) file for more information.
