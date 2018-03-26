<?php
namespace rts_scheduler\V1\Entities;

use airmoi\FileMaker\FileMakerException;
use \Exception;
use \DateTime;

class TimeSlot {
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

		$start = DateTime::createFromFormat("n/j/Y h:i:s a", $date.' '.$startTime);
		$end = DateTime::createFromFormat("n/j/Y h:i:s a", $date.' '.$endTime);

		$slot = [
			'rid' => (int) $rec->getRecordId(),
			'id' => $rec->getField('_pk_Sched_ID'),
			'start' => $date.' '.$startTime,
			'end' => $date.' '.$endTime,
			'availability' => max(0,$appointments['max'] - $appointments['made'])
		];

		return $slot;
	}

	public function generate($slot){
		$slot = self::fixDates($slot,'n/j/Y');
		return $slot;
	}
}