<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use rts_scheduler\V1\User;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/auth', function(Request $request, Response $response, array $args){

	$rtsNumber = $request -> getParsedBodyParam('rtsNumber', '');
	$validationNumber = $request -> getParsedBodyParam('validationNumber', '');

	if(empty($rtsNumber)){
		return $response -> withJson(error(-1,'No RTS Number specified.'));
	}
	if(empty($validationNumber)){
		return $response -> withJson(error(-1,'No Validation Number specified.'));
	}

	$db = connectToDB('RTS',['errorHandling' => 'exception']);

	try {
		$q = $db -> newFindCommand('web_authenticate');
		$q -> addFindCriterion('cd_RTS', '=='.$rtsNumber);
		// TODO: $q -> addFindCriterion('cd_validationNumber', '=='.$validationNumber);
		
		$r = $q -> execute();

		$userRecord = $r -> getFirstRecord();

		$user = User::generate(
			User::readFM($userRecord)
		);

		return $response -> withJson(success(array_merge(
			[
				'user' => $user
			]
		)));
	}
	catch(Exception $e) {
		$code = $e->getCode();
		$msg = $e->getMessage();
		return $response -> withJson(error($code,$msg));
	}
});