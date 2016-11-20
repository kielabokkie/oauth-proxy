<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Httpful\Request as HttpfulRequest;
use Httpful\Response;
use Illuminate\Http\Request;
use Predis\Client;

class AuthController extends Controller
{
    /**
     * Redis client
     *
     * @var Client
     */
    protected $redis;

    /**
     * Create a new controller instance
     *
     * @param Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get an access token using the password grant
     *
     * @param  Request $request
     * @return Response
     */
    public function getToken(Request $request)
    {
        $data = $request->all();

        // Add the required OAuth parameters
        $data['grant_type'] = 'password';
        $data['client_id'] = env('OAUTH_CLIENT_ID');
        $data['client_secret'] = env('OAUTH_CLIENT_SECRET');

        $endpoint = sprintf('%s%s', env('API_URL'), env('API_ACCESS_TOKEN_ENDPOINT'));

        $response = HttpfulRequest::post($endpoint)
            ->addHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->body(http_build_query($data))
            ->send();

        $this->storeTokens($response);

        return response()->json($response->body, $response->code);
    }

    /**
     * Get a new access token using the refresh token grant
     *
     * @param  Request $request
     * @return Response
     */
    public function refreshToken(Request $request)
    {
        // Get the bearer token from the header
        $accessToken = $request->bearerToken();

        // Lookup the refresh token in Redis
        $refreshToken = $this->redis->get($accessToken);

        // Refresh token will be used, so remove from Redis
        $this->redis->del($accessToken);

        // Add the required OAuth parameters
        $data['grant_type'] = 'refresh_token';
        $data['refresh_token'] = $refreshToken;
        $data['client_id'] = env('OAUTH_CLIENT_ID');
        $data['client_secret'] = env('OAUTH_CLIENT_SECRET');

        $endpoint = sprintf('%s%s', env('API_URL'), env('API_REFRESH_TOKEN_ENDPOINT'));

        $response = HttpfulRequest::post($endpoint)
            ->addHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->body(http_build_query($data))
            ->send();

        $this->storeTokens($response);

        return response()->json($response->body, $response->code);
    }

    /**
     * Store tokens from a response in Redis
     *
     * @param Response $response
     */
    private function storeTokens(Response $response)
    {
        $accessToken = $response->body->access_token;
        $refreshToken = $response->body->refresh_token;

        // Create expire timestamp
        $expireAt = Carbon::now()
            ->addSeconds(env('OAUTH_REFRESH_TOKEN_TTL', 2592000))
            ->timestamp;

        // Store the tokens in Redis
        $this->redis->set($accessToken, $refreshToken);
        $this->redis->expireAt($accessToken, $expireAt);
    }
}
