<?php

namespace Sanjarani\Gemini\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException;

class GeminiRateLimitMiddleware
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Cache\RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return mixed
     * @throws \Sanjarani\Gemini\Exceptions\GeminiApiRateLimitException
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): mixed
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            throw new GeminiApiRateLimitException(
                'Too many requests to Gemini API. Please try again later.',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response;
    }

    /**
     * Resolve the request signature.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->user()?->getAuthIdentifier() ?? $request->ip()
        );
    }
}
