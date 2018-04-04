<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/request', function(Request $request, Response $response, array $args){

	$slot_id = $request -> getParsedBodyParam('slot_id', '');

	if(empty($slot_id)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Slot selected!'));
	}

	$jwt = $request -> getAttribute('jwt');

	$user_id = $jwt['user_id'];

	$params = packForFM([
		'idUSR' => $user_id,
		'idSLOT' => $slot_id
	]);

	$db = connectToDB('RTS',['errorHandling' => 'exception']);

	try {
		$q = $db -> newPerformScriptCommand('web_time_slots', 'web_post_appointment_request', $params);
		$r = $q -> execute();

		return $response -> withJson(success());
	}
	catch(Exception $e) {
		$code = $e->getCode();
		$msg = $e->getMessage();
		if ($code === 401) {
			$response = $response -> withStatus(400);
		}
		if ($code === 0){
			$code = -1;
			$response = $response -> withStatus(503);
		}
		return $response -> withJson(error($code,$msg));
	}
});