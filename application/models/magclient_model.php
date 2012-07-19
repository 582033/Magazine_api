<?php

class Magclient_Model extends CI_Model {
	
	function Magclient_Model(){
		parent::__construct();
	}
	
	function _get_ver_info($client){
		if ($client == 'android'){
			$item = array(
						'status' => 'OK',
						'result' => array(
										'vcode' => 1,
										'vname' => 'The first android version',
										'size' => 10257127,
										'url' => "http://d.in1001.com/apk/magazine_bookshelf.apk",
										'checksum' => 'bfe68652003e50f98f24f89c29faa645',
										'released_at' => date("Y-m-d H:i:s"),
										'relnote' => 'android relnote',
										),
						);
		}else if ($client == 'ios'){
			$item = array(
						'status' => 'OK',
						'result' => array(
										'vname' => 'The first android version',
										'size' => 15640,
										'url' => $this->config->item('www_host') . "/client/$client/download",
										'checksum' => md5('ios'),
										'released_at' => date("Y-m-d H:i:s"),
										'relnote' => 'ios relnote',
										),
						);
		}else if ($client == 'pctool'){
			$item = array(
						'status' => 'OK',
						'result' => array(
										'vcode' => 1,
										'vname' => 'The first pctool version',
										'size' => 13480,
										'url' => $this->config->item('www_host') . "/client/$client/download",
										'checksum' => md5('pctool'),
										'released_at' => date("Y-m-d H:i:s"),
										'relnote' => 'pctool relnote',
										),
						);
		}
		return $item;
	}
}
