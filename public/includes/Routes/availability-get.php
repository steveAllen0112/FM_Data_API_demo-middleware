<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> get('/availability/{route}/{year}/{month}', function(Request $request, Response $response, array $args){

	$route = $args['route'];
	if(empty($route)) {
		return $response -> withStatus(400) -> withJson(error(-1,'No Route specified.'));
	}
	$year = $args['year'];
	if(empty($year)) {
		return $response -> withStatus(400) -> withJson(error(-1,'No Year specified.'));
	}
	$month = $args['month'];
	if(empty($month)) {
		return $response -> withStatus(400) -> withJson(error(-1,'No Month specified.'));
	}

	$params = packForFM([
		'route' => $route,
		'year' => $year,
		'month' => $month
	]);

	$db = connectToDB($_ENV['APP']['project'],['errorHandling' => 'exception']);
	
	try {
		$q = $db -> newPerformScriptCommand('web_variables', 'web_get_availability', $params);

		$r = $q -> execute();

		$data = checkForScriptResponse($r, 'throw');

		$available = [];
		if (!empty($data)) {
			$available = explode(',', $data);
		}

		// error_log('finished processing project load: ' . date('H:i:s:u', strtotime('now')));
		return $response -> withJson(success([
			'route' => (int) $route,
			'collection' => [
				'year' => (int) $year,
				'month' => (int) $month,
				'dates' => $available
			]
		]));
	}
	catch(Exception $e){
		$code = $e->getCode();
		$msg = $e->getMessage();
		return $response -> withJson(error($code,$msg));
	}
});