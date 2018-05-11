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

	$guzzleClient = new Client([
		'base_url' => $location + "/fmi/data/v1/databases/${filename}"
	]);

	$response = $guzzleClient->post('/sessions', [
		'headers' => [
			'Authorization' => 'Bearer ' + base64_encode("$username:$password"),
			'Content-Type' => 'application/json'
		],
		'body' => '{}'
	]);

	return $response;
}

function disconnectFromDB($project, $token) {

	$filename = $_ENV[$project.'_FILE'];
	$location = $_ENV[$project.'_LOCATION'];
	$username = $_ENV[$project.'_USERNAME'];
	$password = $_ENV[$project.'_PASSWORD'];

	$guzzleClient = new Client([
		'base_url' => $location + "/fmi/data/v1/databases/${filename}"
	]);

	$response = $guzzleClient->delete("/sessions/$token", [
		'headers' => [
			'Authorization' => 'Bearer ' + base64_encode("$username:$password"),
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