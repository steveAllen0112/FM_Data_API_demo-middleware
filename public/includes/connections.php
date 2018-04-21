<?php
use airmoi\FileMaker\FileMaker;

function connectToDB($project,$params=['errorHandling' => 'exception']){

	$filename = $_ENV[$project.'_FILE'];
	$location = $_ENV[$project.'_LOCATION'];
	$username = $_ENV[$project.'_USERNAME'];
	$password = $_ENV[$project.'_PASSWORD'];

	$db = new FileMaker($filename, $location, $username, $password);

	foreach($params as $prop => $value){
		$db ->setProperty($prop, $value);
	}
	return $db;
	
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