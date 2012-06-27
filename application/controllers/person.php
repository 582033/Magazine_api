<?php class Person extends MY_Controller {

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
		$this->load->library('session');
	}

	function user($user_id){
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if($method == 'post'){
			$user_json = file_get_contents('php://input', 'r');
			$user_info = json_decode($user_json, true);
			$return = $this->User_Model->edit_user($user_id, $user_info);
			return $return;
		}elseif($method == 'get'){
			$user = $this->User_Model->get_user_info($user_id);
			$this->_json_output($user);
		}
	}

	function users(){
		$start = $this->_get_non_empty('start');
		$limit = $this->_get_non_empty('limit');
		$data = $this->User_Model->get_all_users($start,$limit);
		$this->_json_output($data);
	}

	function followers ($userId) {	//{{{
		$start = $this->_get_non_empty('start');
		$limit = $this->_get_non_empty('limit');
		$followers = $this->User_Model->get_followers($userId, $start, $limit);
		$this->_json_output($followers);
	}	//}}}

	function followees ($userId) {	//{{{
		$start = $this->_get_non_empty('start');
		$limit = $this->_get_non_empty('limit');
		//$followees = $this->User_Model->get_followees($userId, $start, $limit);
		$followees = $this->User_Model->get_followers($userId, $start, $limit);
		$this->_json_output($followees);
	}	//}}}

	function like($type, $id, $action){
		$user_id = 1;//$this->session->userdata('user_id');
		if($action == 'like'){
			$this->Love_Model->like($type, $id, $user_id);
		}else{
			$this->Love_Model->cancellike($type, $id, $user_id);
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

	function apply_author($user_id){
		$user_id = $this->session->userdata('user_id');
		$this->User_Model->to_be_author($user_id);
	}

	function user_avatar($user_id){

	}

}
