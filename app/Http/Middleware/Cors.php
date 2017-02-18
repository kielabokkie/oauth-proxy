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
            'Access-Control-Allow-Methods' => 'OPTIONS, POST, GET, PUT, PATCH, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With'
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
