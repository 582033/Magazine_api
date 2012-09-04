<?php class Stats extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}

	function magread($magazineId) {
		$num_times = $this->_get('nts', 1);
		$num_times = $num_times <= 0 ? 1 : floor($num_times);
		$this->load->model('mag_model');
		$this->mag_model->incr_magazine($magazineId, 'views', $num_times);
		$result = array('status' => 'OK');
		$this->_json_output($result);
	}
}
