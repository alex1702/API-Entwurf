<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// Get output format
$app->add(function ($request, $response, $next) {

    $queryParams = $request->getQueryParams();
    $format = isset($queryParams['format']) ? $format = $queryParams['format'] : $format = "json";

    $request = $request->withAttribute('format', $format);

    $response = $next($request, $response);
    return $response;
});
