<?php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\MultipartStream;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

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

	/*
	 * handle file upload, if any
	 */
	$hasFile = false;

	$uploadedFiles = $request -> getUploadedFiles();
	$uploadedFile = $uploadedFiles['photo'];
	if ($uploadedFile and $uploadedFile->getError() === UPLOAD_ERR_OK) {
		$directory = $this->get('upload_directory');
		$extension = pathinfo($uploadedFile->getClientFileName(), PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(8));
		$filename = sprintf('%s.%0.8s', $basename, $extension);

		$uploadedFile -> moveTo($directory . DIRECTORY_SEPARATOR . $filename);
		$hasFile = true;
	}

	try {
		$historyContainer = [];
		$history = Middleware::history($historyContainer);
		$stack = HandlerStack::create();
		$stack -> push($history);

		//connect
		$dataAPI_session = connectToDB($_ENV['APP']['project']);
		$layout = 'web_contacts';

		//add contact
		$guzzleClient = new Client([
			'base_uri' => $dataAPI_session['base_uri'],
			'handler' => $stack
		]);

		$createContact['guzzleResponse'] = $guzzleClient->post("{$dataAPI_session['base_uri']}/layouts/$layout/records", [
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

		$createContact['FMResponse'] = json_decode((string) $createContact['guzzleResponse']->getBody(), true);

		if ($hasFile) {
			//Get the Record ID of the record just created.
			$contactRID = (int) $createContact['FMResponse']['response']['recordId'];
			$file_location = $directory . DIRECTORY_SEPARATOR . $filename;
			$file = file_get_contents($file_location);

			//upload the photo to the record
			$addPhoto['guzzleResponse'] = $guzzleClient->post("{$dataAPI_session['base_uri']}/layouts/$layout/records/$contactRID/containers/Photo/1", [
				'verify' => false,
				'headers' => [
					'Authorization' => "Bearer {$dataAPI_session['token']}",
				],
				'multipart' => [[
					'Content-Type' => 'multipart/form-data',
					'name' => 'upload',
					'contents' => $file,
					'filename' => $filename
				]]
			]);

			$addPhoto['FMResponse'] = json_decode((string) $addPhoto['guzzleResponse']->getBody(), true);
		}

		//disconnect
		disconnectFromDB($dataAPI_session);

		return $response -> withJson(success(['results' => [
			'create' => $createContact['FMResponse'],
			'upload' => $addPhoto['FMResponse']
		]]));
	}
	catch(Exception $e) {
		if(!empty($dataAPI_session)){
			try {
				disconnectFromDB($dataAPI_session);
			} catch(Exception $e) {
				error_log('problem disconnecting from session: ' . print_r($dataAPI_session, true));
				/*$code = $e->getCode();
				$msg = $e->getMessage();
				if ($code === 0){
					$code = -1;
					$response = $response -> withStatus(503);
				}
				return $response -> withJson(error($code,$msg));*/
			}
		}
		$code = $e->getCode();
		$msg = $e->getMessage();
		error_log("problem detected: ($code) $msg");
		foreach($historyContainer as $transaction){
			$historyStr .= ' | ' . (string) $transaction['request']->getBody();
		}
		error_log("guzzle history: $historyStr");
		if ($code === 0){
			$code = -1;
			$response = $response -> withStatus(503);
		}
		return $response -> withJson(error($code,$msg));
	}
});