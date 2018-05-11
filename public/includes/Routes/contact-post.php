<?php
use GuzzleHttp\Client;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/contacts', function(Request $request, Response $response, array $args){

	$name = [
		'first' => $request -> getParsedBodyParam('name_first', ''),
		'last' => $request -> getParsedBodyParam('name_last', '')
	];
	$email = $request -> getParsedBodyParam('email', '');

	if(empty($name['first']) and empty($name['last'])){
		return $response -> withStatus(400) -> withJson(error(-1,'No Name given.'));
	}
	if(empty($email)){
		return $response -> withStatus(400) -> withJson(error(-1,'No Email given.'));
	}

	try {
		//connect
		$dataAPI_session = connectToDB($_ENV['APP']['project']);
		$layout = 'web_contacts';

		//add contact
		$guzzleClient = new Client([
			'base_uri' => $dataAPI_session['base_uri']
		]);

		$guzzleResponse = $guzzleClient->post("{$dataAPI_session['base_uri']}/layouts/$layout/records", [
			'verify' => false,
			'headers' => [
				'Authorization' => "Bearer {$dataAPI_session['token']}",
				'Content-Type' => 'application/json'
			],
			'body' => json_encode([
				'fieldData' => [
					'First_Name' => $name['first'],
					'Last_Name' => $name['last'],
					'Email' => $email
				]
			])
		]);

		//disconnect
		disconnectFromDB($dataAPI_session);

		return $response -> withJson(success([]));
	}
	catch(Exception $e) {
		if(!empty($dataAPI_session)){
			try {
				disconnectFromDB($dataAPI_session);
			} catch(Exception $e) {
				$code = $e->getCode();
				$msg = $e->getMessage();
				if ($code === 0){
					$code = -1;
					$response = $response -> withStatus(503);
				}
				return $response -> withJson(error($code,$msg));
			}
		}

		$code = $e->getCode();
		$msg = $e->getMessage();
		if ($code === 0){
			$code = -1;
			$response = $response -> withStatus(503);
		}
		return $response -> withJson(error($code,$msg));
	}
});