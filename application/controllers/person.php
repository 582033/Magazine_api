<?php class Person extends MY_Controller {

	var $start;
	var $limit;

	function Person(){
		parent::__construct();
		$this->load->library('session');
		$this->start = $this->input->get('start') ? $this->input->get('start') : 0;
		$this->limit = $this->input->get('limit') ? $this->input->get('limit') : 10;
	}

	function user($user_id){
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->load->model('User_Model');
		$this->load->model('check_session_model');
		if($method == 'put'){
			$user_id = $this->check_session_model->check_session();
			$user_json = file_get_contents('php://input', 'r');
			$user_info = json_decode($user_json, true);
			$this->User_Model->edit_user($user_id, $user_info);
		}elseif($method == 'get'){
			if ($user_id == 'me') $user_id = $this->check_session_model->check_session();
			$projection = $this->_get('projection', 'full');
			$user = $this->User_Model->get_user_info($user_id, $projection);
			$this->_json_output($user);
		}
	}

	function users(){
		$keyword = $this->input->get('q') ? $this->input->get('q') : null;
		$this->load->model('User_Model');
		$data = $this->User_Model->get_all_users($this->start,$this->limit, $keyword);
		$this->_json_output($data);
	}

	function followers ($userId) {	//{{{
		$this->load->model('User_Model');
		$followers = $this->User_Model->user_loved($userId, 'followers', $this->start, $this->limit);
		$this->_json_output($followers);
	}	//}}}

	function followees ($userId) {	//{{{
		$this->load->model('User_Model');
		$followees = $this->User_Model->user_loved($userId, 'followees', $this->start, $this->limit);
		$this->_json_output($followees);
	}	//}}}

	function like($type, $id, $action){
		$this->load->model('check_session_model');
		$user_id = $this->check_session_model->check_session();
		$this->load->model('Love_Model');
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
		$this->load->model('check_session_model');
		$user_id = $this->check_session_model->check_session();
		$this->load->model('Love_Model');
		if($yes == 1){
			$this->Love_Model->like('author', $id, $user_id);
		}else{
			$this->Love_Model->cancellike('author', $id, $user_id);
		}
	}

	function apply_author($uid) { // {{{
		$this->load->model('check_session_model');
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
			$this->load->model('User_Model');
			$this->User_Model->to_be_author($user_id);
			$this->invitation_model->use_code($code);
			$result = array('status' => 'OK');
			$pubstr = file_get_contents($this->config->item('api_host') . "/msgpub/ftpuser?userid=".$user_id);
		}
		$this->_json_output($result);
	} //}}}

	function change_password(){
		$this->load->model('check_session_model');
		$user_id = $this->check_session_model->check_session();
		$item = array(
					'old_pwd' => $this->input->post('passwd'),
					'new_pwd' => $this->input->post('newpasswd'),
					);
		$this->load->model('User_Model');
		$data = $this->User_Model->_change_password($user_id, $item);
		$this->_json_output($data);
	}
	
	function checkexists() {
		$username = $this->input->get('username');
		$this->load->model('User_Model');
		if($username && $username != '') {
			$result = $this->User_Model->checkexists($username);
			$this->_json_output($result);
		}
		else {
			show_error('Bad Request', 400);
		}
	}
}
