<?php
namespace rts_scheduler\V1\Entities;

use airmoi\FileMaker\FileMakerException;
use \Exception;

class BlackoutDate {
	public function validate($blackout){
		return $blackout;
	}

	public function fixDates($blackout,$format){
		return $blackout;
	}

	public function readFM($rec){
		
		$blackout = [
			'rid' => (int) $rec->getRecordId()
		];

		return $blackout;
	}

	public function generate($blackout){
		$blackout = self::fixDates($blackout,'n/j/Y');
		return $blackout;
	}
}