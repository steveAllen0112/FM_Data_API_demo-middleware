<?php
// Application middleware

$app->add(function($request, $response, $next) {

	$project = ($request->hasHeader('X-RCC-PROJECT') && !empty($request->getHeaderLine('X-RCC-PROJECT'))) ? $request->getHeaderLine('X-RCC-PROJECT') : '';
	
	// check to make sure it's a valid project
	$projectCheckResult = checkProjectConfiguration($project);
	if($projectCheckResult['status'] !== 200) {
		return $response -> withStatus($projectCheckResult['status']) -> withJson(error(-1,$projectCheckResult['message']));
	}

	$_ENV['APP'] = [
		'project' => $project,
		'environment' => ($request->hasHeader('X-RCC-ENVIRONMENT') && !empty($request->getHeaderLine('X-RCC-ENVIRONMENT'))) ? $request->getHeaderLine('X-RCC-ENVIRONMENT') : 'UNKNOWN',
		'version' => ($request->hasHeader('X-RCC-VERSION') && !empty($request->getHeaderLine('X-RCC-VERSION'))) ? $request->getHeaderLine('X-RCC-VERSION') : 'UNKNOWN',
	];
	
	return $next($request, $response);
});

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(new \Tuupola\Middleware\Cors([
	"origin" => [
		"https://steveAllen0112.github.io",

		"https://localhost:4200",
		"http://localhost:4200",
		"https://localhost:8000",
		"http://localhost:8000"
	],
	"methods" => ["POST", "OPTIONS"],
	"headers.allow" => [
		"X-Requested-With",
		"Content-Type",
		"Accept",
		"Origin",
		"Authorization",
		"X-RCC-PROJECT",
		"X-RCC-ENVIRONMENT",
		"X-RCC-VERSION"
	],
	"headers.expose" => [],
	"credentials" => false,
	"cache" => 0,
]));

$app->add(function($request, $response, $next) {
	$response = $next($request, $response);

	return $response -> withHeader('Content-type', 'application/json');
});