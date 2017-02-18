<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin' => env('CORS_ALLOW_ORIGIN'),
            'Access-Control-Allow-Methods' => env('CORS_ALLOW_METHODS'),
            'Access-Control-Allow-Headers' => env('CORS_ALLOW_HEADERS'),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '600'
        ];

        // Catch OPTIONS requests as Lumen doesn't handle these by default
        if ($request->isMethod('OPTIONS')) {
            return response(null)->withHeaders($headers);
        }

        $response = $next($request);

        // Add the access control headers to the response
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
