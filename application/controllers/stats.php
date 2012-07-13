<?php class Stats extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}

	function magread($magazineId) {
		$this->load->model('mag_model');
		$callback = $this->_get('callback');
		$this->mag_model->incr_magazine_views($magazineId);
		$result = array('status' => 'OK');
		if ($callback) {
			header('Content-Type: application/javascript');
			echo $callback . '(' . json_encode($result) . ');';
			exit;
		}
		else {
			$this->_json_output($result);
		}
	}
}
