<?php
class Comment extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_comment_Model');
	}

	function comments($type, $magazineId=null){ //{{{		杂志评论
		$now = new DateTime;
		$date = $now->format("Y-m-d H:i:s");
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
//		$user_id = $this->_get_user_id();
		$user_id = 1;
		if($request_method == 'post'){
			$com_data = array(
				'type' => $type,
				'object_id' => $magazineId,
				'comment' => $this->input->post('comment'),
				'parent_id' => $this->input->post('parent_id'),
				'user_id' => $user_id,
				'send_time' => date('Y-m-d H:i:s'),
			);
			$data = json_encode($com_data, true);
			$this->User_comment_Model->add_comment($com_data);
		}
		if($request_method == 'get'){
			$limit = $this->input->get('limit') ? $this->input->get('limit') : '10';
			$start = $this->input->get('start') ? $this->input->get('start') : '0';
			$result = $this->User_comment_Model->comments($user_id, $type, $magazineId, $limit, $start);
			$this->_json_output($result);
		}
	}	//}}}

	function user_comments(){
	}

}