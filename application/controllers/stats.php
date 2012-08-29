<?php class Stats extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}

	function action($type, $magazineId) {
		switch($type) {
			case 'magdl':
				$field = 'downloads';
				break;
			case 'magread':
				$field = 'views';
				break;
		}
		$this->load->model('mag_model');
		$this->mag_model->incr_magazine($magazineId, $field);
		$result = array('status' => 'OK');
		$this->_json_output($result);
	}
}
