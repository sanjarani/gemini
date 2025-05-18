<?php

namespace Sanjarani\Gemini\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sanjarani\Gemini\Contracts\GeminiClientInterface;
use Sanjarani\Gemini\Contracts\GeminiResponseInterface;

class RunGeminiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The request payload.
     *
     * @var array
     */
    protected array $payload;

    /**
     * The model to use.
     *
     * @var string|null
     */
    protected ?string $model;

    /**
     * The callback to run after completion.
     *
     * @var string|null
     */
    protected ?string $callbackClass;

    /**
     * The callback method to run.
     *
     * @var string|null
     */
    protected ?string $callbackMethod;

    /**
     * The callback parameters.
     *
     * @var array
     */
    protected array $callbackParams;

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [1, 5, 10];

    /**
     * Create a new job instance.
     *
     * @param array $payload
     * @param string|null $model
     * @param string|null $callbackClass
     * @param string|null $callbackMethod
     * @param array $callbackParams
     */
    public function __construct(
        array $payload,
        ?string $model = null,
        ?string $callbackClass = null,
        ?string $callbackMethod = null,
        array $callbackParams = []
    ) {
        $this->payload = $payload;
        $this->model = $model;
        $this->callbackClass = $callbackClass;
        $this->callbackMethod = $callbackMethod;
        $this->callbackParams = $callbackParams;
    }

    /**
     * Execute the job.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiClientInterface $client
     * @return void
     */
    public function handle(GeminiClientInterface $client): void
    {
        $response = $client->send($this->payload, $this->model);
        
        $this->runCallback($response);
    }

    /**
     * Run the callback if set.
     *
     * @param \Sanjarani\Gemini\Contracts\GeminiResponseInterface $response
     * @return void
     */
    protected function runCallback(GeminiResponseInterface $response): void
    {
        if (!$this->callbackClass || !$this->callbackMethod) {
            return;
        }

        if (!class_exists($this->callbackClass)) {
            return;
        }

        $instance = app($this->callbackClass);
        
        if (!method_exists($instance, $this->callbackMethod)) {
            return;
        }

        $instance->{$this->callbackMethod}($response, ...$this->callbackParams);
    }
}
