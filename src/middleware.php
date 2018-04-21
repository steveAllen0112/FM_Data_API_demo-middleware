<?php
// Application middleware

$app->add(function($request, $response, $next) {

	$_ENV['APP'] = [
		'environment' => ($request->hasHeader('X-RTS-ENVIRONMENT') && !empty($request->getHeaderLine('X-RTS-ENVIRONMENT'))) ? $request->getHeaderLine('X-RTS-ENVIRONMENT') : 'UNKNOWN',
		'version' => ($request->hasHeader('X-RTS-VERSION') && !empty($request->getHeaderLine('X-RTS-VERSION'))) ? $request->getHeaderLine('X-RTS-VERSION') : 'UNKNOWN'
	];

	return $next($request, $response);
});

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(new \Tuupola\Middleware\Cors([
	"origin" => [
		"https://marquettemi.meterschedule.com",
		"https://api.meterschedule.com",
		"https://localhost:4200",
		
		"http://localhost:4200"
	],
	"methods" => ["GET", "POST", "OPTIONS"],
	"headers.allow" => [
		"X-Requested-With",
		"Content-Type",
		"Accept",
		"Origin",
		"Authorization",
		"X-RTS-ENVIRONMENT",
		"X-RTS-VERSION"
	],
	"headers.expose" => [],
	"credentials" => false,
	"cache" => 0,
]));

$app->add(new \Tuupola\Middleware\JwtAuthentication([
	'attribute' => 'jwt',
	'secret' => $_ENV['JWT_SECRET'],
	'path' => ['/'],
	'ignore' => [
		'/auth',
		'/lost'
	],
	'error' => function($response, $arguments){
		error_log('JWT Auth Error: '.$arguments['message']);
		return $response -> getBody() -> write(json_encode(['error' => 401, 'message' => $arguments['message']], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}
]));

$app->add(function($request, $response, $next) {
	$response = $next($request, $response);

	return $response -> withHeader('Content-type', 'application/json');
});