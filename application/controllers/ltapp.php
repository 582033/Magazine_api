<?php

class Ltapp extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('ads_model');
	}

	function ads(){
		$type = $this->_get_non_empty('type');
		$slot = $this->_get_non_empty('slot');
		$res = $this->ads_model->ads($type, $slot);
		$this->_json_output($res);
	}

}
?>