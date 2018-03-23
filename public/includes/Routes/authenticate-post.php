<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use rts_scheduler\V1\User;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> post('/auth', function(Request $request, Response $response, array $args){
	return $response -> withJson(success(array_merge(
		[
			'hello' => 'world'
		]
	)));
});