<?php class Stats extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}

	function magread($magazineId) {
		$this->load->model('mag_model');
		$this->mag_model->incr_magazine($magazineId, 'views');
		$result = array('status' => 'OK');
		$this->_json_output($result);
	}
}
