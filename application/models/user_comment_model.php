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

	function comments($user_id, $type, $object_id, $limit=10, $start=0){		//取得评论{{{
		$where = array('type' => $type, 'object_id' => $object_id);
		$sql = "select * from user_comment as C,user as U where C.user_id=U.user_id and C.type='$type' and C.object_id=$object_id order by send_time desc limit $start,$limit";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		$sql_0 = "select count(*) from user_comment as C,user as U where C.user_id=U.user_id and C.type='$type' and C.object_id=$object_id";
		$result_0 = $this->db->query($sql_0);
		$result_0 = $result_0->row_array();
		$totalResults = $result_0['count(*)'];
		foreach($result as $k){
			if($k['parent_id'] != 0){
				$parent_id = $k['parent_id'];
				$sql_0 = "select * from user where user_id='$parent_id'";
				$parent_user = $this->db->query($sql_0);
				$parent_user = $parent_user->row_array();
				$parent = array(
					'id' => $k['parent_id'],
					'author' => array(
						'id' => $parent_user['user_id'],
						'nickname' => $parent_user['nickname'],
						'image' => $parent_user['avatar'],
					),
				);
			}else{
				$parent = array();
			}
			$res[] = array(
				'id' => $k['user_comment_id'],
				'content' => $k['comment'],
				'author' => array(
					'id' => $k['user_id'],
					'nickname' => $k['nickname'],
					'image' => $k['avatar'],
				),
				'parent' => $parent,
			);
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
