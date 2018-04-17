<?php
namespace rts_scheduler\V1\Entities;

use airmoi\FileMaker\FileMakerException;
use \Exception;
use \DateTime;
use \DateTimeZone;

class TimeSlot {
	const TIMEZONE = 'America/Chicago';

	public function validate($slot){
		return $slot;
	}

	public function fixDates($slot,$format){
		return $slot;
	}

	public function readFM($rec){

		$appointments = [
			'made' => (int) $rec->getField('Appntments_Made'),
			'max' => (int) $rec->getField('Appntments_Max')
		];

		$date = $rec->getField('TheDate');
		$startTime = $rec->getField('TimeSlot_Start');
		$endTime = $rec->getField('TimeSlot_End');

		$start = DateTime::createFromFormat("n/j/Y H:i:s", $date.' '.$startTime, new DateTimeZone(self::TIMEZONE));
		$end = DateTime::createFromFormat("n/j/Y H:i:s", $date.' '.$endTime, new DateTimeZone(self::TIMEZONE));

		$slot = [
			'rid' => (int) $rec->getRecordId(),
			'id' => $rec->getField('_pk_Sched_ID'),
			'start' => $start -> format('c'),
			'end' => $end -> format('c'),
			'availability' => max(0,$appointments['max'] - $appointments['made'])
		];

		return $slot;
	}

	public function generate($slot){
		$slot = self::fixDates($slot,'n/j/Y');
		return $slot;
	}
}