<?php
namespace rts_scheduler\V1\Entities;

use airmoi\FileMaker\FileMakerException;
use \Exception;

class User {
	public function validate($user){
		return $user;
	}

	public function fixDates($user,$format){
		return $user;
	}

	public function readFM($rec){
		
		$user = [
			'rid' => (int) $rec->getRecordId(),
			'id' => $rec->getField('__pk_UUID'),
			'rtsNumber' => $rec->getField('cd_RTS'),
			'accountNumber' => $rec->getField('cd_account_number'),
			'name' => $rec->getField('cd_name'),
			'route' => (int) $rec->getField('cd_route'),
			'address' => [
				'location' => $rec->getField('cd_property_location'),
				'city' => $rec->getField('cd_city'),
				'state' => $rec->getField('cd_state'),
				'zip' => $rec->getField('cd_zip')
			]
		];

		return $user;
	}

	public function generate($user){
		$user = self::fixDates($user,'n/j/Y');
		return $user;
	}
}