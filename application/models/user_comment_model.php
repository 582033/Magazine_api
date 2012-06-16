<?php
class User_comment_Model extends mag_db {

	function Love_Model (){
		parent::__construct();
	}

	function comment($data){		//用户评论{{{
		$user_comment_id = $this->mag_db->insert_row(USER_COMMENT_TABLE, $data);
		$item = $this->mag_db->row(USER_COMMENT_TABLE, array('user_comment_id' => $user_comment_id));
		return $item;
	}//}}}

	function _get_user_comment($user_id, $type, $object_id, $limit=10, $start=0){		//取得评论{{{
		$where = array('type' => $type, 'object_id' => $object_id);
		$sql = "select * from user_comment as C,user as U where C.user_id=U.user_id and C.type='$type' and C.object_id=$object_id order by send_time desc limit $limit";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		if ($result){
			$return = array('errcode' => '0', 'data' => $result);
		}else{
			$return = array('errcode' => '1', 'data' => null);
		}
		return $return;
	}//}}}

	function _get_parent_comment_info($item){
	}
}