<?php class User extends MY_Controller {

	function User () {	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	}	//}}}










	function ftpinfo ($userId) {	//{{{
		//$user_id = $userId == 'me' ? $this->session->userdata('user_id') : $userId;
		$user_id = '1';
		$ftpinfo = $this->User_Model->get_ftp_info($user_id);
		$this->_json_output($ftpinfo);
	}	//}}}
}

