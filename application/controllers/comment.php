<?php
class Comment extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_comment_Model');
		$this->load->model('check_session_model');
	}

	function comments($type, $magazineId=null){ //{{{		杂志评论
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		if($request_method == 'post'){
			$now = new DateTime;
			$date = $now->format("Y-m-d H:i:s");
			$user_id = $this->check_session_model->check_session();
			$comment_json = file_get_contents('php://input', 'r');
			$comment = @json_decode($comment_json, TRUE);
			if (!$comment) {
				show_error_text(400, 'body not json');
			}
			if (empty($comment['content'])) {
				show_error_text(400, 'Bad request, empty content param');
			}
			$com_data = array(
				'type' => $type,
				'object_id' => $magazineId,
				'comment' => $comment['content'],
				'parent_id' => element('parentId', $comment, 0),
				'user_id' => $user_id,
				'send_time' => date('Y-m-d H:i:s'),
			);
			$data = json_encode($com_data, true);
			$this->User_comment_Model->add_comment($com_data);
			show_error_text(201);
		}
		if($request_method == 'get'){
			$limit = $this->input->get('limit') ? $this->input->get('limit') : '10';
			$start = $this->input->get('start') ? $this->input->get('start') : '0';
			$result = $this->User_comment_Model->comments(null, $type, $magazineId, $limit, $start);
			$this->_json_output($result);
		}
	}	//}}}
}
