<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use rts_scheduler\V1\Entities\TimeSlot;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> get('/slots/{route}/{year}/{month}/{day}', function(Request $request, Response $response, array $args){

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
	$day = $args['day'];
	if(empty($day)) {
		return $response -> withStatus(400) -> withJson(error(-1,'No Day specified.'));
	}
	
	$jwt = $request -> getAttribute('jwt');
	
	$params = packForFM([
		'route' => $route,
		'year' => $year,
		'month' => $month,
		'day' => $day
	]);

	$db = connectToDB($_ENV['APP']['project'],['errorHandling' => 'exception']);
	
	try {
		$q = $db -> newPerformScriptCommand('web_time_slots', 'web_get_time_slots', $params);

		$r = $q -> execute();

		checkForScriptResponse($r, 'throw');

		$records = $r -> getRecords();
		$slots = [];

		foreach($records as $record){
			$slots[] = TimeSlot::generate(
				TimeSlot::readFM($record, $jwt['timezone'])
			);
		}
		// error_log('finished processing project load: ' . date('H:i:s:u', strtotime('now')));
		return $response -> withJson(success([
			'collection' => [
				'route' => (int) $route,
				'year' => (int) $year,
				'month' => (int) $month,
				'day' => (int) $day,
				'slots' => $slots
			]
		]));
	}
	catch(Exception $e){
		$code = $e->getCode();
		$msg = $e->getMessage();
		if($code == 401){
			return $response -> withJson(success(['slots' => []]));
		}
		return $response -> withJson(error($code,$msg));
	}
});