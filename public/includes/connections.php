<?php
use airmoi\FileMaker\FileMaker;

function connectToDB($file,$params=['errorHandling' => 'exception']){

	$filename = $_ENV[$file.'_FILE'];
	$location = $_ENV[$file.'_LOCATION'];
	$username = $_ENV[$file.'_USERNAME'];
	$password = $_ENV[$file.'_PASSWORD'];

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