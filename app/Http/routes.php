<?php

$app->post(
    env('ROUTE_ACCESS_TOKEN', '/oauth/token'),
    'AuthController@getToken'
);
$app->get(
    env('ROUTE_REFRESH_TOKEN', '/oauth/token/refresh'),
    'AuthController@refreshToken'
);
