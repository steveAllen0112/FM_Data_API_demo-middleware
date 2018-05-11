<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/contacts', function(Request $request, Response $response, array $args){

	$name = $request -> getParsedBodyParam('name', '');
	$email = $request -> getParsedBodyParam('email', '');

	if(empty($name)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Name given.'));
	}
	if(empty($email)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Email given.'));
	}

	try {
		$token = connectToDB($_ENV['APP']['project']);

		//TODO: Add Contact

		return $response -> withJson(success(['response' => $token]));
	}
	catch(Exception $e) {
		disconnectFromDB($_ENV['APP']['project']);

		$code = $e->getCode();
		$msg = $e->getMessage();
		if ($code === 0){
			$code = -1;
			$response = $response -> withStatus(503);
		}
		return $response -> withJson(error($code,$msg));
	}
});