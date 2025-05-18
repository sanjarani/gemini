<?php

namespace Sanjarani\Gemini;

use Illuminate\Support\ServiceProvider;
use Sanjarani\Gemini\Clients\GeminiClient;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;
use Sanjarani\Gemini\Contracts\GeminiLoggerInterface;
use Sanjarani\Gemini\Contracts\TextGenerationServiceInterface;
use Sanjarani\Gemini\Contracts\VisionServiceInterface;
use Sanjarani\Gemini\Contracts\EmbeddingServiceInterface;
use Sanjarani\Gemini\Contracts\TokenCounterInterface;
use Sanjarani\Gemini\Services\TextGenerationService;
use Sanjarani\Gemini\Services\VisionService;
use Sanjarani\Gemini\Services\EmbeddingService;
use Sanjarani\Gemini\Support\GeminiCache;
use Sanjarani\Gemini\Support\GeminiLogger;
use Sanjarani\Gemini\Utilities\TokenCounter;
use Sanjarani\Gemini\Console\Commands\TestGeminiCommand;
use Sanjarani\Gemini\Console\Commands\ClearGeminiCacheCommand;

class GeminiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/gemini.php', 'gemini'
        );
        
        // Register the main Gemini client
        $this->app->singleton(GeminiClientInterface::class, function ($app) {
            return new GeminiClient(
                $app['config']['gemini.api_key'],
                $app['config']['gemini.base_url'],
                $app['config']['gemini.default_model'],
                $app['config']['gemini.request_timeout']
            );
        });
        
        // Register the cache service
        $this->app->singleton(GeminiCacheInterface::class, function ($app) {
            return new GeminiCache(
                $app['cache.store'],
                $app['config']['gemini.cache.enabled'],
                $app['config']['gemini.cache.ttl'],
                $app['config']['gemini.cache.prefix']
            );
        });
        
        // Register the logger service
        $this->app->singleton(GeminiLoggerInterface::class, function ($app) {
            return new GeminiLogger(
                $app['log']->channel($app['config']['gemini.logging.channel']),
                $app['config']['gemini.logging.enabled']
            );
        });
        
        // Register the token counter utility
        $this->app->singleton(TokenCounterInterface::class, function ($app) {
            return new TokenCounter($app['config']['gemini.models']);
        });
        
        // Register the text generation service
        $this->app->singleton(TextGenerationServiceInterface::class, function ($app) {
            return new TextGenerationService(
                $app[GeminiClientInterface::class],
                $app[GeminiCacheInterface::class],
                $app[GeminiLoggerInterface::class],
                $app[TokenCounterInterface::class]
            );
        });
        
        // Register the vision service
        $this->app->singleton(VisionServiceInterface::class, function ($app) {
            return new VisionService(
                $app[GeminiClientInterface::class],
                $app[GeminiCacheInterface::class],
                $app[GeminiLoggerInterface::class],
                $app[TokenCounterInterface::class]
            );
        });
        
        // Register the embedding service
        $this->app->singleton(EmbeddingServiceInterface::class, function ($app) {
            return new EmbeddingService(
                $app[GeminiClientInterface::class],
                $app[GeminiCacheInterface::class],
                $app[GeminiLoggerInterface::class],
                $app[TokenCounterInterface::class]
            );
        });
        
        // Register the main Gemini facade accessor
        $this->app->singleton('gemini', function ($app) {
            return new Gemini(
                $app[TextGenerationServiceInterface::class],
                $app[VisionServiceInterface::class],
                $app[EmbeddingServiceInterface::class]
            );
        });
    }
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/gemini.php' => config_path('gemini.php'),
        ], 'gemini-config');
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestGeminiCommand::class,
                ClearGeminiCacheCommand::class,
            ]);
        }
    }
}
