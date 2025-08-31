<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log all headers
        Log::debug('Headers: ', $response->headers->all());

        // Log cookies
        Log::debug('Cookies: ', array_map(fn($cookie) => $cookie->__toString(), $response->headers->getCookies()));

        // Check for newlines in headers
        foreach ($response->headers->all() as $name => $values) {
            foreach ($values as $value) {
                if (preg_match('/[\r\n]/', $value)) {
                    Log::error("Newline detected in header: $name => $value");
                }
            }
        }

        // Check for newlines in cookies
        foreach ($response->headers->getCookies() as $cookie) {
            $cookieStr = $cookie->__toString();
            if (preg_match('/[\r\n]/', $cookieStr)) {
                Log::error("Newline detected in cookie: $cookieStr");
            }
        }

        return $response;
    }
}
