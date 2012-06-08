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
	
	function _get_user_comment($user_id, $magazine_id, $limit, $start){		//取得评论{{{
		if ($magazine_id == ''){
			$where = array('user_id' => $user_id);
		}else{
			$where = array('user_id' => $user_id, 'magazine_id' => $magazine_id);
		}
		$result = $this->mag_db->rows(USER_COMMENT_TABLE, $where, $limit, $start);
		if ($result){
			$return = array('errcode' => '0', 'data' => $result);
		}else{
			$return = array('errcode' => '1', 'data' => null);
		}
		return $return;
	}//}}}
}
