<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use rts_scheduler\V1\Entities\BlackoutDate;

use Slim\Http\Request;
use Slim\Http\Response;

$app -> get('/blackouts/{year}', function(Request $request, Response $response, array $args){

	$year = $args['year'];
	if(empty($year)) {
		return $response -> withStatus(400) -> withJson(error(-1,'No Year specified.'));
	}

	$db = connectToDB('RTS',['errorHandling' => 'exception']);
	
	try {
		$q = $db -> newFindCommand('web_blackout_dates');

		$q -> addFindCriterion('Year', '=='.$year);

		$r = $q -> execute();

		$records = $r -> getRecords();
		$dates = [];
		foreach($records as $record){
			$dates[] = BlackoutDate::generate(
				BlackoutDate::readFM($record)
			);
		}
		// error_log('finished processing project load: ' . date('H:i:s:u', strtotime('now')));
		return $response -> withJson(success([
			'year' => $year,
			'dates' => $blackouts
		]));
	}
	catch(Exception $e){
		$code = $e->getCode();
		$msg = $e->getMessage();
		if($code == 401){
			return $response -> withJson(success([
				'year' => $year,
				'dates' => []
			]));
		}
		return $response -> withJson(error($code,$msg));
	}
});