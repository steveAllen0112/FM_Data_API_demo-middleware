<?php
$dotenv = new Dotenv\Dotenv(__DIR__, '.env.config');
$dotenv -> load();

$dotenv -> required('STAGE')->notEmpty();
$dotenv -> required('VERSION')->notEmpty();
$dotenv -> required('RTS_FILE')->notEmpty();
$dotenv -> required('RTS_LOCATION')->notEmpty();
$dotenv -> required('RTS_USERNAME')->notEmpty();
$dotenv -> required('RTS_PASSWORD')->notEmpty();
$dotenv -> required('JWT_SECRET')->notEmpty();
$dotenv -> required('TIME_ZONE_DEFAULT')->notEmpty();

$environment = $_ENV['STAGE'];

return [
	'settings' => [
        'displayErrorDetails' => ($environment == 'PRODUCTION')?false:true,
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		'determineRouteBeforeAppMiddleware' => true,

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
	],
];
