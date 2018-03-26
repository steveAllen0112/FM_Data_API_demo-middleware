<?php
// Application middleware
use Tuupola\Middleware\JwtAuthentication;

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
		"https://api.meterschedule.com",
		"https://localhost:4200",
		
		"http://api.meterschedule.com",
		"http://localhost:4200"
	],
	"methods" => ["GET", "POST", "OPTIONS"],
	"headers.allow" => [
		"X-Requested-With",
		"Content-Type",
		"Accept",
		"Origin",
		"Authorization",
		"X-DISPATCH-ENVIRONMENT",
		"X-DISPATCH-VERSION",
		"X-DISPATCH-MODULE"
	],
	"headers.expose" => [],
	"credentials" => false,
	"cache" => 0,
]));

$app->add(new JwtAuthentication([
	'attribute' => 'jwt',
	'secret' => $_ENV['RTS_JWT_SECRET'],
	'ignore' => [
		'/auth'
	]
]));

$app->add(function($request, $response, $next) {
	$response = $next($request, $response);

	return $response -> withHeader('Content-type', 'application/json');
});