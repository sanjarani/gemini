<?php

namespace Sanjarani\Gemini\Console\Commands;

use Illuminate\Console\Command;
use Sanjarani\Gemini\Contracts\GeminiCacheInterface;

class ClearGeminiCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:cache-clear {key? : Specific cache key to clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Gemini API response cache';

    /**
     * The cache service.
     *
     * @var \Sanjarani\Gemini\Contracts\GeminiCacheInterface
     */
    protected $cache;

    /**
     * Create a new command instance.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiCacheInterface $cache
     * @return void
     */
    public function __construct(GeminiCacheInterface $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $key = $this->argument('key');
        
        if ($key) {
            $result = $this->cache->forget($key);
            
            if ($result) {
                $this->info("Cache key '{$key}' cleared successfully.");
            } else {
                $this->warn("Cache key '{$key}' not found or could not be cleared.");
            }
        } else {
            // Since we can't directly clear all cache with a specific prefix,
            // we inform the user to use Laravel's cache:clear command
            $this->info('To clear all Gemini cache, use Laravel\'s cache:clear command:');
            $this->line('php artisan cache:clear');
        }
        
        return Command::SUCCESS;
    }
}
