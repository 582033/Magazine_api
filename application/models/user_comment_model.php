<?php
class User_comment_Model extends mag_db {

	function __construct(){
		parent::__construct();
	}

	function add_comment($data){		//用户评论{{{
		$user_comment_id = $this->mag_db->insert_row(USER_COMMENT_TABLE, $data);
		$item = $this->mag_db->row(USER_COMMENT_TABLE, array('user_comment_id' => $user_comment_id));
		return $item;
	}//}}}

	function get_comment_by_id($id) {
		return $this->row(USER_COMMENT_TABLE, array('user_comment_id', $id));
	}
	function comments($user_id, $type, $object_id, $limit=10, $start=0){		//取得评论{{{
		$where = array('type' => $type, 'object_id' => $object_id);
		$sql = "select * from user_comment as C,user as U where C.user_id=U.user_id and C.type='$type' and C.object_id=$object_id order by send_time desc limit $start,$limit";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		$sql_0 = "select count(*) from user_comment as C,user as U where C.user_id=U.user_id and C.type='$type' and C.object_id=$object_id";
		$result_0 = $this->db->query($sql_0);
		$result_0 = $result_0->row_array();
		$totalResults = $result_0['count(*)'];
		$CI = &get_instance();
		$CI->load->model('user_model');
		if ($totalResults > 0) {
			foreach($result as $k){
				$parent = NULL;
				$parent_id = $k['parent_id'];
				if ($parent_id) {
					$parent_user = $this->db->select('u.*')
						->from(USER_COMMENT_TABLE . ' as uc')
						->join(USER_TABLE . ' as u', 'uc.user_id = u.user_id')
						->where('uc.user_comment_id', $parent_id)
						->get()
						->row_array();
					if ($parent_user) {
						$parent = array(
							'id' => $k['parent_id'],
							'author' => $this->user_model->mapping_user_info($parent_user, 'short'),
						);
					}
				}
				$comment = array(
					'id' => $k['user_comment_id'],
					'content' => $k['comment'],
					'author' => $this->user_model->mapping_user_info($k, 'short'),
				);
				if ($parent) {
					$comment['parent'] = $parent;
				}
				$res[] = $comment;
			}
		}
		else {
			$res = null;
		}
		$res = array(
			'kind' => "magazine#comments",
			'totalResults' => $totalResults,
			'start' => 0,
			'items' => $res,
		);
		return $res;
	}//}}}

}
