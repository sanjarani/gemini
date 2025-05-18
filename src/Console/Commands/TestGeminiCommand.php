<?php

namespace Sanjarani\Gemini\Console\Commands;

use Illuminate\Console\Command;
use Sanjarani\Gemini\Facades\Gemini;

class TestGeminiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:test {prompt : The prompt to send to Gemini API} {--model= : The model to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Gemini API with a prompt';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prompt = $this->argument('prompt');
        $model = $this->option('model');
        
        $this->info('Sending prompt to Gemini API...');
        
        try {
            $options = [];
            if ($model) {
                $options['model'] = $model;
            }
            
            $response = Gemini::generate($prompt, $options);
            
            $this->info('Response:');
            $this->line($response->content());
            
            $this->newLine();
            $this->info('Token Usage:');
            $tokenUsage = $response->tokenUsage();
            $this->table(
                ['Prompt Tokens', 'Completion Tokens', 'Total Tokens', 'Estimated Cost'],
                [[
                    $tokenUsage['prompt_tokens'],
                    $tokenUsage['completion_tokens'],
                    $tokenUsage['total_tokens'],
                    '$' . number_format($response->estimatedCost(), 6)
                ]]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
