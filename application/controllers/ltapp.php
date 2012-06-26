<?php

class Ltapp extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('ads_model');
	}

	function ads($type, $slot){
		$start = $this->_get('start', 0);
		$limit = $this->_get('limit', 10);
		$res = $this->ads_model->ads($type, $slot, $start, $limit);
		$this->_json_output($res);
	}

}
?>