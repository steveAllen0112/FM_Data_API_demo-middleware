<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/lost', function(Request $request, Response $response, array $args){

	$name = $request -> getParsedBodyParam('name', '');
	$phone = $request -> getParsedBodyParam('phone', '');
	$email = $request -> getParsedBodyParam('email', '');
	$address = $request -> getParsedBodyParam('address', '');
	$bestTime = $request -> getParsedBodyParam('bestTime', '');

	if(empty($name)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Name given.'));
	}
	if(empty($phone)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Phone Number specified.'));
	}
	if(empty($address)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Address given.'));
	}
	if(empty($bestTime)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Best Time To Contact specified.'));
	}

	$params = packForFM([
		'reason' => "Lost/Didn't Receive Postcard",
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'rtsNumber' => $rtsNumber,
		'address' => $address,
		'bestTimeToContact' => $bestTime
	]);

	$db = connectToDB($_ENV['APP']['project'],['errorHandling' => 'exception']);

	try {
		$q = $db -> newPerformScriptCommand('web_contact_requests', 'web_post_contact_request', $params);
		$r = $q -> execute();

		return $response -> withJson(success());
	}
	catch(Exception $e) {
		$code = $e->getCode();
		$msg = $e->getMessage();
		if ($code === 0){
			$code = -1;
			$response = $response -> withStatus(503);
		}
		return $response -> withJson(error($code,$msg));
	}
});