<?php

class Ltapp extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('ads_model');
	}

	function ads($type, $slot){
//		$type = 'image';
//		$slot = 'indexmaga';
		$res = $this->ads_model->ads($type, $slot);
		$this->_json_output($res);
	}

}
?>