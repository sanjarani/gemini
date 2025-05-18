<?php

namespace Sanjarani\Gemini\Clients;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;
use Sanjarani\Gemini\Exceptions\GeminiApiException;
use Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException;
use Sanjarani\Gemini\Exceptions\GeminiModelNotFoundException;
use Sanjarani\Gemini\Exceptions\GeminiNetworkException;
use Sanjarani\Gemini\Exceptions\GeminiConfigurationException;
use Sanjarani\Gemini\Responses\GeminiResponse;

class GeminiClient implements GeminiClientInterface
{
    /**
     * The HTTP client instance.
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected HttpClient $httpClient;

    /**
     * The API key.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * The base URL for the API.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * The model to use for requests.
     *
     * @var string
     */
    protected string $model;

    /**
     * The request timeout in seconds.
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Create a new Gemini client instance.
     *
     * @param string $apiKey
     * @param string $baseUrl
     * @param string $model
     * @param int $timeout
     */
    public function __construct(
        string $apiKey,
        string $baseUrl,
        string $model,
        int $timeout = 30
    ) {
        $this->httpClient = new HttpClient();
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->model = $model;
        $this->timeout = $timeout;
        
        if (empty($this->apiKey)) {
            throw new GeminiConfigurationException('Gemini API key is not set. Please set GEMINI_API_KEY in your .env file.');
        }
    }

    /**
     * Send a request to the Gemini API.
     *
     * @param array $payload
     * @param string|null $model
     * @return \Sanjarani\Gemini\Contracts\GeminiResponseInterface
     * @throws \Sanjarani\Gemini\Exceptions\GeminiApiException
     */
    public function send(array $payload, ?string $model = null): GeminiResponseInterface
    {
        $model = $model ?? $this->model;
        
        // Updated endpoint URL format for Gemini API v1
        // Removed 'models/' prefix as per latest API requirements
        $endpoint = "{$this->baseUrl}/{$model}:generateContent";
        
        try {
            $response = $this->makeRequest()
                ->post($endpoint, $payload);
            
            $this->handleResponseErrors($response);
            
            return new GeminiResponse(
                $response->json(),
                $model
            );
        } catch (GeminiApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GeminiNetworkException(
                "Network error while connecting to Gemini API: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Set the model to use for requests.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        
        return $this;
    }

    /**
     * Get the current model being used.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Set the API key to use for requests.
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        
        return $this;
    }

    /**
     * Create a new HTTP request instance.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function makeRequest(): PendingRequest
    {
        return $this->httpClient
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withQueryParameters([
                'key' => $this->apiKey,
            ]);
    }

    /**
     * Handle response errors.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return void
     * @throws \Sanjarani\Gemini\Exceptions\GeminiApiException
     */
    protected function handleResponseErrors(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();
        $body = $response->json();
        $error = Arr::get($body, 'error', ['message' => 'Unknown error']);
        $message = Arr::get($error, 'message', 'Unknown error');

        switch ($status) {
            case 400:
                throw new GeminiApiException("Bad request: {$message}", $status);
            case 401:
                throw new GeminiApiException("Authentication error: {$message}", $status);
            case 404:
                throw new GeminiModelNotFoundException("Model not found: {$message}", $status);
            case 429:
                throw new GeminiApiRateLimitException("Rate limit exceeded: {$message}", $status);
            default:
                throw new GeminiApiException("Gemini API error ({$status}): {$message}", $status);
        }
    }
}
