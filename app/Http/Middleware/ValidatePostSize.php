<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\PostTooLargeException;

class ValidatePostSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Http\Exceptions\PostTooLargeException
     */
    public function handle($request, Closure $next)
    {
        $max = $this->getPostMaxSize();

        if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {
            throw new PostTooLargeException;
        }

        return $next($request);
    }

    /**
     * Get the maximum request content length in bytes.
     *
     * @return int
     */
    protected function getPostMaxSize()
    {
        // Use Laravel's config if available, otherwise use PHP's ini
        $maxSize = config('filesystems.max_post_size', ini_get('post_max_size'));
        
        if (is_numeric($maxSize)) {
            return (int) $maxSize;
        }

        $metric = strtoupper(substr($maxSize, -1));
        $maxSize = (int) $maxSize;

        switch ($metric) {
            case 'K':
                return $maxSize * 1024;
            case 'M':
                return $maxSize * 1024 * 1024;
            case 'G':
                return $maxSize * 1024 * 1024 * 1024;
            default:
                return $maxSize;
        }
    }
} 