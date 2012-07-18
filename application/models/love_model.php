<?php
class Love_Model extends mag_db {

	function Love_Model (){
		parent::__construct();
	}

	function like($loved_type, $loved_id, $user_id){		//喜欢接口{{{
		$where = array(
			'loved_id' => $loved_id,
			'user_id' => $user_id,
			'loved_type' => $loved_type,
		);
		$row = $this->row(USER_LOVE_TABLE, $where);
		$row_user = $this->db->from(USER_TABLE)->where(array('user_id' =>$user_id))->get()->row_array();
		$this->load->model('Msg_Model');
		$msg_obj= array(
					'type' => 'person',
					'data' =>array(
					     'id' => $user_id,
					     'nickname' => $row_user['nickname'],
					)
					);
		$arr_love_author = array(
				'user_id' => $loved_id,
				'occur_time' => date("Y-m-d H:i:s"),
				'actor' => '0',
				'verb' => 'follow',
				'object' =>json_encode($msg_obj),
				);
		$json_love_author = json_encode($arr_love_author);
		$this->Msg_Model->msg_add($json_love_author);
		if ($row == array()){
			$data = $where;
			$this->mag_db->insert_row(USER_LOVE_TABLE, $data);
		}
		switch ($loved_type) {
			case 'magazine':
				$table = 'magazine';
				$id_field = 'magazine_id';
				break;
			case 'element':
				$table = 'mag_element';
				$id_field = 'mag_element_id';
				break;
		}
		if ($loved_type != 'author') {
			$sql = "update $table set num_loved = num_loved + 1 where $id_field = $loved_id";
			$this->db->query($sql);
		}
	} //}}}

	function cancellike($loved_type, $loved_id, $user_id){
		$where = array(
			'loved_id' => $loved_id,
			'user_id' => $user_id,
			'loved_type' => $loved_type,
		);
		$this->db->delete(USER_LOVE_TABLE, $where);
	}

	function _loved_nums($user_id){		//用户个人喜欢数量{{{
		$where_mag = array('user_id' => $user_id, 'loved_type' => 'magazine');
		$where_author = array('user_id' => $user_id, 'loved_type' => 'author');
		$where_elem = array('user_id' => $user_id, 'loved_type' => 'element');
		$result_mag = $this->mag_db->total(USER_LOVE_TABLE, $where_mag);			//收藏的杂志
		$result_author = $this->mag_db->total(USER_LOVE_TABLE, $where_author);		//订阅的作者
		$result_elem = $this->mag_db->total(USER_LOVE_TABLE, $where_elem);			//喜欢的元素
		$item = array(
					'love_magazine_nums' => "$result_mag",
					'love_element_nums' => "$result_elem",
					'love_author_nums' => "$result_author",
					);
		return $item;
	}//}}}

	function _loved_data($user_id, $limit, $start, $type, $mag_category, $element_type){		//获取喜欢数据{{{
		if ($type == ''){
				$where_mag = array('user_love.user_id' => $user_id, 'loved_type' => 'magazine');
				$where_author = array('user_love.user_id' => $user_id, 'loved_type' => 'author');
				$where_elem = array('user_love.user_id' => $user_id, 'loved_type' => 'element');
				$result_mag = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAGAZINE_TABLE, 'magazine_id', $where_mag, $limit, $start);
				$result_author = $this->mag_db->loved_rows(USER_LOVE_TABLE, USER_TABLE, 'user_id', $where_author, $limit, $start);
				$result_elem = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAG_ELEMENT_TABLE, 'mag_element_id', $where_elem, $limit, $start);
				$return = array(
							'errcode' => '0',
							'data' => array(
											'loved_magazine' => $result_mag,
											'loved_element' => $result_elem,
											'loved_author' => $result_author,
											),
							);
		}else{
				$where = array('user_love.user_id' => $user_id, 'loved_type' => $type);
				if ($type == 'element'){
					if ($element_type != ''){
						$where['element_type'] = $element_type;
					}
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAG_ELEMENT_TABLE, 'mag_element_id', $where, $limit, $start);
				}else if ($type == 'author'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, USER_TABLE, 'user_id', $where, $limit, $start);
				}else if ($type == 'magazine'){
					if ($mag_category != ''){
						$where['mag_category'] = $mag_category;
					}
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAGAZINE_TABLE, 'magazine_id', $where, $limit, $start);
				}else{
					$result = NULL;
				}
				$return = array(
							'errcode' => '0',
							'data' => $result,
							);
				if ($return['data'] == NULL){
					$return['errcode'] = '1';
					$return['data'] = null;
				}
		}
		return $return;
	}//}}}

	function _nums_loved($where){		//获取杂志、元素、作者被喜欢的次数{{{
		$nums = $this->mag_db->total(USER_LOVE_TABLE, $where);
		return $nums;
	}//}}}
}
