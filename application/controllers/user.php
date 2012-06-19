<?php class User extends MY_Controller {

	function User () {	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}	//}}}


	function user_info(){
		$user_id = $this->_get_non_empty('user_id');
		$user = $this->User_Model->get_user_info($user_id);
		$this->_json_output($user);
	}

	function users_info(){
		$start = $this->_get_non_empty('start');
		$limit = $this->_get_non_empty('limit');
		$data = $this->User_Model->get_all_users($start,$limit);
		$this->_json_output($data);
	}
	
	function ftpinfo ($userId) {	//{{{
		//$user_id = $userId == 'me' ? $this->session->userdata('user_id') : $userId;
		$user_id = '1';
		$ftpinfo = $this->User_Model->get_ftp_info($user_id);
		$this->_json_output($ftpinfo);
	}	//}}}

}
