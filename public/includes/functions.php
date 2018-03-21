<?php
use airmoi\FileMaker\FileMaker;
use Slim\Http\Response;

function getTimestamp($record,$fieldName){
	$tsVal = $record->getField($fieldName);
	if(empty($tsVal)){
		$ts = '';
	}else{
		$ts = $record->getFieldAsTimestamp($fieldName)*1000;
	}
	return $ts;
}

function array_to_xml( $data, &$xml_data ) {
	foreach( $data as $key => $value ) {
		if( is_array($value) ) {
			if( is_numeric($key) ){
				/*
				 * Since we are using this only to package data for FileMaker,
				 * which will be read with a Custom Function,
				 * and for which we need to know how many there are,
				 * the best way is just to repeat the key.
				 * 
				 * We will find (in FM) how many there are using a PatternCount on the closing tag,
				 * and then use ExtractData() CF to pull the one we want, since it allows
				 * specification of a desired instance.
				 * 
				 * -- SRA 9/29/2015 1:45 p.m.
				 */
				$key = 'specialNumericKeyTagForFileMaker';//.$key; //dealing with <0/>..<n/> issues 
			}
			$subnode = $xml_data->addChild($key);
			array_to_xml($value, $subnode);
		} else {
			$xml_data->addChild("$key",htmlspecialchars("$value"));
		}
	 }
}

function success($data=[]){
	
	$out = array_merge([
		'code' => 0,
		'type' => 'success'
	],$data);
	
	return $out;
}

function error($code,$message){
	return [
		'code' => $code,
		'message' => $message
	];
}

function isFMError($r){
	return FileMaker::isError($r);
}

function checkForScriptResponse($r,$notFoundCode=false){
	$rec = $r->getFirstRecord();
	if(gettype($rec) == 'object'){
		if(in_array('g_web_response_code',$rec->getFields())){
			$code = +$rec->getField('g_web_response_code');
			if($code){
				if($code == 401) {
					if($notFoundCode == 'return'){
						return 401;
					}
					if($notFoundCode == 'throw'){
						throw new Exception('None Found',401);
					}
				}
				$msg = $rec -> getField('g_web_response_message').' '.$rec->getField('g_web_response_data');
				$code = $rec->getField('g_web_response_code');
				throw new Exception(
					$msg,
					$code
				);
			}
			$data = $rec->getField('g_web_response_data');
			return $data;
		}
	}
	return $r;
}

function xml2array ( $xmlObject, $out = array () )
{
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ||  is_array ( $node ) ) ? xml2array ( $node ) : $node;

		return $out;
}

function packForFM($arr){
	$withServerInfo = array_merge($arr, [
		'app' => $_ENV['APP'],
		'api' => [
			'environment' => $_ENV['STAGE'],
			'version' => $_ENV['VERSION']
		]
	]);

	$xml = new SimpleXMLElement('<dataPack></dataPack>');
	array_to_xml($withServerInfo,$xml);
	$xmlStr = $xml->asXML();

	if($_ENV['STAGE'] !== 'PRODUCTION'){
		error_log($xmlStr);
	}

	return $xmlStr;
}