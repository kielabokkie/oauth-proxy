# OAuth Proxy

OAuth proxy for single-page apps (SPA) built with [Lumen](https://lumen.laravel.com).

## Introduction

We all know that keeping secrets is hard when it comes to SPAs, especially when it comes to OAuth. The [OAuth 2.0 spec](https://tools.ietf.org/html/rfc6749) dictates that you should never expose your client id or secret and your access token as well as the refresh token. If you have to use OAuth you'll require some sort of trade-off as you can't keep all of the above a secret. There are a lot of different techniques, all with their own pros and cons.

OAuth Proxy sits between your SPA and your API and provides two endpoints that forward OAuth requests to your API and automatically adds the required client id and client secret. On top of that it can also handle refreshing access tokens using the `refresh_token` grant. Because all of this happens server-side you keep most of your secrets to yourself. The only trade-off is that the client requires the access token. If you use short lived access tokens and always use SSL (which I hope you do!) then the risk is minimised.

## Prerequisites

As this project is built with [Lumen](https://lumen.laravel.com) it requires a PHP version of `5.6.4` or higher. If you haven’t used Lumen or Laravel before I suggest you first have a look through the documentation.

At the moment it also depends on [Redis](https://redis.io) as it makes it fast and easy to store key-value pairs with an expiration but I intend to add other providers as well.

## Installation

You can install OAuth Proxy by running the `composer create-project` command in your terminal:

```
composer create-project --prefer-dist kielabokkie/oauth-proxy
```

## Configuration

OAuth Proxy provides two endpoints. The first one, to acquire an access token using the `password` grant, is `/oauth/token`. The second endpoint, which lets you refresh access tokens, is `/oauth/token/refresh`.  If you prefer to use different endpoints for the Proxy (maybe to match the style of your API) you can overwrite the endpoints in the `.env` file, more on that later.

### Environment file

If you are familiar with Lumen (or Laravel) you’ll know that the `.env` file contains various configuration settings. Before you can try out the proxy there are a couple of parameters that you need to enter.

Below is an example `.env` file:

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=tdXF17HfY1yWpPtIV4DGxrivKEpN4yDh

ROUTE_ACCESS_TOKEN='/oauth/token'
ROUTE_REFRESH_TOKEN='/oauth/token/refresh'

API_URL='http://api.myapp.dev'
API_ACCESS_TOKEN_ENDPOINT='/v1/oauth/token'
API_REFRESH_TOKEN_ENDPOINT='/v1/oauth/token'

OAUTH_CLIENT_ID=my-spa-client
OAUTH_CLIENT_SECRET=s3cr3tk3y
OAUTH_REFRESH_TOKEN_TTL=2592000 # 30 days

CORS_ALLOW_ORIGIN='*'
CORS_ALLOW_METHODS='OPTIONS, GET, POST, PUT, PATCH, DELETE'
CORS_ALLOW_HEADERS='Authorization, Content-Type'

REDIS_URI='tcp://127.0.0.1:6379'
```

The first three parameters are standard Lumen ones that you have to changed based on your environment (e.g. don’t set `APP_DEBUG` to `true` on production environments). The `APP_KEY` can be set automatically by running the `php artisan key:generate` command.

The `ROUTE_ACCESS_TOKEN` and `ROUTE_REFRESH_TOKEN` are used to customise the endpoints of the Proxy (as mentioned earlier).

The next set of parameters are all related to your actual API, the `API_URL` is the full URL of your API. `API_ACCESS_TOKEN_ENDPOINT` and `API_REFRESH_TOKEN_ENDPOINT` allow you to specify what endpoint on your API should be used for getting an access token and refreshing an access token.

Next we have the OAuth related parameters. Here you specify the client id and client secret and the TTL of your refresh tokens. For this last parameter you have to make sure it matches the TTL you have set for refresh tokens on your OAuth server. It’s important that these are the same as the TTL is used to automatically remove them from the datastore after they expire.

The next three parameters are all to handle CORS headers.

Lastly we have the optional `REDIS_URI` variable. If you don't add this variable to your `.env` file the application uses the following URI for the Redis connection `tcp://127.0.0.1:6379`. The `REDIS_URI` variable allows you to customise the URI in case you want to use a custom IP address or port.

### Webserver setup

As this Proxy is separate from your API and front-end you will need to setup your webserver to serve this application. You can either setup a subdomain (e.g. `proxy.myapp.com`) or have your webserver switch to your proxy based on the uri of your endpoints.

## Usage

So you have everything setup now and your Proxy is ready to be used!

### Password grant

The `/oauth/token` endpoint requires a `POST` request with the `username` and `password` as parameters. Below is a example CURL command that sends the username and password as `x-www-form-urlencoded` data (as per the OAuth 2.0 spec):

```
curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -d 'username=wouter@myapp.com&password=password' "http://proxy.myapp.com/oauth/token"
```

If you prefer to send JSON encoded data you can also do that:

```
curl -X POST -H "Content-Type: application/json" -d '{"username": "wouter@myapp.com", "password": "password"}' "http://proxy.myapp.com/oauth/token"
```

### Refresh token grant

The `/oauth/token/refresh` endpoint requires a `GET` request with the `Authorization` header containing the bearer token (e.g. the access token that expired). Below is an example of such request:

```
curl -X GET -H "Authorization: Bearer F9kqePKN424Ci3hRDqk5vzsGjP3qnXrnqGUxxiE9" "http://proxy.myapp.com/oauth/token/refresh"
```

That's it, happy Proxying!
