<?php
class Comment extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_comment_Model');
	}

	function comments($magazineId){ //{{{		杂志评论
		$now = new DateTime;
		$date = $now->format("Y-m-d H:i:s");
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
//		$user_id = $this->_get_user_id();
		$user_id = 1;
		if($request_method == 'post'){
			$com_data = array(
				'type' => 'magazine',
				'object_id' => $magazineId,
				'comment' => $this->input->post('comment'),
				'parent_id' => $this->input->post('parent_id'),
				'user_id' => $user_id,
				'send_time' => date('Y-m-d H:i:s'),
			);
			$data = json_encode($com_data, true);
			$item = $this->User_comment_Model->add_comment($com_data);
			$result = array(
							'apiver' => $this->apiver,
							'errcode' => '0',
							'data' => $item,
							);
		}elseif($request_method == 'get'){
			$type = $this->_get_non_empty('type');
			$object_id = $this->_get_non_empty('object_id');
			$limit = $this->_get_non_empty('limit');
			$start = $this->_get_non_empty('start');
			$result = $this->User_comment_Model->comments($user_id, $type, $object_id, $limit, $start);
		}
		$this->_json_output($result);
	}	//}}}

	function user_comments(){

	}

}