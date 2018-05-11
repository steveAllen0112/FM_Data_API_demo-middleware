<?php
use GuzzleHttp\Client;

function checkProjectConfiguration($project) {
	$projects = explode(',', $_ENV['PROJECTS']);
	if (empty($project)) {
		return [ 'status' => 400, 'message' => "Application Configuration Error: No project set."];
	}

	if(!in_array($project,$projects)) {
		return [ 'status' => 503, 'message' =>  "[Project: $project] Application Configuration Error: Project not recognized."];
	}

	$filename = $_ENV["{$project}_FILE"];
	$location = $_ENV["{$project}_LOCATION"];
	$username = $_ENV["{$project}_USERNAME"];
	$password = $_ENV["{$project}_PASSWORD"];
	
	if (empty($filename)) {
		return [ 'status' => 503, 'message' => "[Project: $project] Application Configuration Error: Missing project configuration: DB name."];
	}
	
	if (empty($location)) {
		return [ 'status' => 503, 'message' => "[Project: $project] Application Configuration Error: Missing project configuration: DB location."];
	}
	
	if (empty($username)) {
		return [ 'status' => 503, 'message' => "[Project: $project] Application Configuration Error: Missing project configuration: DB username."];
	}
	
	if (empty($password)) {
		return [ 'status' => 503, 'message' => "[Project: $project] Application Configuration Error: Missing project configuration: DB password."];
	}

	return [ 'status' => 200 ];
}

function connectToDB($project){

	$filename = $_ENV[$project.'_FILE'];
	$location = $_ENV[$project.'_LOCATION'];
	$username = $_ENV[$project.'_USERNAME'];
	$password = $_ENV[$project.'_PASSWORD'];

	$base_uri = "https://$location/fmi/data/v1/databases/$filename";
	$authStrUP = base64_encode("$username:$password");

	$guzzleClient = new Client([
		'base_uri' => $base_uri
	]);

	$guzzleResponse = $guzzleClient->post("$base_uri/sessions", [
		'verify' => false,
		'headers' => [
			'Authorization' => "Basic $authStrUP",
			'Content-Type' => 'application/json'
		],
		'body' => '{}'
	]);

	$body = json_decode($guzzleResponse->getBody()->getContents(), true);

	$token = $body['response']['token'];

	return [
		'base_uri' => $base_uri,
		'token' => $token
	];
}

function disconnectFromDB($session) {

	$guzzleClient = new Client([
		'base_uri' => $session['base_uri']
	]);

	$guzzleResponse = $guzzleClient->delete( "{$session['base_uri']}/sessions/{$session['token']}", [
		'verify' => false,
		'headers' => [
			'Authorization' => "Bearer {$session['token']}",
			'Content-Type' => 'application/json'
		],
		'body' => '{}'
	]);
}

$fmFindSymbols = [
	'<' => '\<',
	'>' => '\>',
	'≥' => '\≥',
	'≤' => '\≤',
	'=' => '\=',
	'...' => '*',
	'!' => '\!',
	'//' => '*',
	'?' => '\?',
	'@' => '\@',
	'#' => '\#',
	'*' => '\*',
	'"' => '\"',
	'~' => '\~'
];