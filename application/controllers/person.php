<?php class Person extends MY_Controller {

	var $start;
	var $limit;

	function Person(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->model('Ads_Model');
		$this->load->model('Mag_Model');
		$this->load->model('Love_Model');
		$this->load->model('mag_element_model');
		$this->load->model('mag_file_model');
		$this->load->model('User_comment_Model');
		$this->load->model('search_model');
		$this->load->model('check_session_model');
		$this->load->library('session');

		$this->start = $this->input->get('start') ? $this->input->get('start') : 0;
		$this->limit = $this->input->get('limit') ? $this->input->get('limit') : 10;
	}

	function user($user_id){
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if($method == 'put'){
			$user_id = $this->check_session_model->check_session();
			$user_json = file_get_contents('php://input', 'r');
			$user_info = json_decode($user_json, true);
			$this->User_Model->edit_user($user_id, $user_info);
		}elseif($method == 'get'){
			if ($user_id == 'me') $user_id = $this->check_session_model->check_session();
			$user = $this->User_Model->get_user_info($user_id);
			$this->_json_output($user);
		}
	}

	function users(){
		$keyword = $this->input->get('q') ? $this->input->get('q') : null;
		$data = $this->User_Model->get_all_users($this->start,$this->limit, $keyword);
		$this->_json_output($data);
	}

	function followers ($userId) {	//{{{
		$followers = $this->User_Model->user_loved($userId, 'followers', $this->start, $this->limit);
		$this->_json_output($followers);
	}	//}}}

	function followees ($userId) {	//{{{
		$followees = $this->User_Model->user_loved($userId, 'followees', $this->start, $this->limit);
		$this->_json_output($followees);
	}	//}}}

	function like($type, $id, $action){
		//$user_id = 1;//$this->session->userdata('user_id');
		$user_id = $this->check_session_model->check_session();
		if ($action == 'like') {
			$this->Love_Model->like($type, $id, $user_id);
		}
		elseif ($action == 'cancelLike') {
			$this->Love_Model->cancellike($type, $id, $user_id);
		}
		else {
			show_error('Bad Request', 400);
		}
	}

	function follow($id, $yes){
		$user_id = $this->session->userdata('user_id');
		if($yes == 1){
			$this->Love_Model->like('author', $id, $user_id);
		}else{
			$this->Love_Model->cancellike('author', $id, $user_id);
		}
	}

	function apply_author($uid) { // {{{
		$user_id = $this->check_session_model->check_session();
		if ($uid == 'me') {
			$uid = $user_id;
		}
		if ($user_id != $uid) {
			show_error('', 401);
		}

		$code = $this->input->get('code');
		if (!$code) {
			show_error('', 400);
		}
		$result = array();
		$this->load->model('invitation_model');
		$status = $this->invitation_model->check_invitation_code($code);
		if ($status != 'OK') {
			$result = array('status' => $status);
		}
		else {
			$this->User_Model->to_be_author($user_id);
			$this->invitation_model->use_code($code);
			$result = array('status' => 'OK');
		}
		$this->_json_output($result);
	} //}}}

	function user_avatar($user_id){

	}

}
