<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use rts_scheduler\V1\Entities\TimeSlot;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> get('/slots/{year}/{month}/{day}', function(Request $request, Response $response, array $args){

	$date = strtotime($args['month'].'/'.$args['day'].'/'.$args['year']);

	$db = connectToDB('RTS',['errorHandling' => 'exception']);
	
	try {
		$q = $db -> newFindCommand('web_time_slots');

		$q -> addFindCriterion('TheDate', '=='.date('n/j/Y', $date));

		$r = $q -> execute();

		$records = $r -> getRecords();
		$slots = [];
		foreach($records as $record){
			$slots[] = TimeSlot::generate(
				TimeSlot::readFM($record)
			);
		}
		// error_log('finished processing project load: ' . date('H:i:s:u', strtotime('now')));
		return $response -> withJson(success([
			'date' => date('Y-m-j', $date),
			'slots' => $slots
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