<?php
// Application middleware

$app->add(function($request, $response, $next) {

	$project = ($request->hasHeader('X-RTS-PROJECT') && !empty($request->getHeaderLine('X-RTS-PROJECT'))) ? $request->getHeaderLine('X-RTS-PROJECT') : '';
	
	// check to make sure it's a valid project
	$projectCheckResult = checkProjectConfiguration($project);
	if($projectCheckResult['status'] !== 200) {
		return $response -> withStatus($projectCheckResult['status']) -> withJson(error(-1,$projectCheckResult['message']));
	}

	$_ENV['APP'] = [
		'project' => $project,
		'environment' => ($request->hasHeader('X-RTS-ENVIRONMENT') && !empty($request->getHeaderLine('X-RTS-ENVIRONMENT'))) ? $request->getHeaderLine('X-RTS-ENVIRONMENT') : 'UNKNOWN',
		'version' => ($request->hasHeader('X-RTS-VERSION') && !empty($request->getHeaderLine('X-RTS-VERSION'))) ? $request->getHeaderLine('X-RTS-VERSION') : 'UNKNOWN',
		'timezone' => ($request->hasHeader('X-RTS-TIMEZONE') && !empty($request->getHeaderLine('X-RTS-TIMEZONE'))) ? $request->getHeaderLine('X-RTS-TIMEZONE') : $_ENV['TIME_ZONE_DEFAULT']
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
		"X-RTS-PROJECT",
		"X-RTS-ENVIRONMENT",
		"X-RTS-VERSION",
		"X-RTS-TIMEZONE"
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