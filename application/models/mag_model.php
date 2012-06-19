<?php
class Mag_Model extends mag_db {
	
	function Mag_model(){
		parent::__construct();
	}

	function _get_category_list (){	//获取杂志分类{{{
		$where = array('dic_name' => 'mag_category');
		$category_list = $this->rows(DICTIONARY_TABLE, $where);
		$category_return = array();
		foreach ($category_list as $cat){
			$category_return[] = array(
					'id' => $cat['dic_key'],
					'name' => $cat['dic_value'],
					);
		}
		return $category_return;
	}	//}}}

	function _get_mag_list($where, $from_url = null){	//获取杂志列表结果{{{
		if ($from_url){
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where, $from_url['limit'], $from_url['start']);
		}
		else{
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where);
		}
		$mag_list = $this->_get_mag_download($mag_list);
		foreach ($mag_list as &$mag){
			if ($mag['edit_index_img']){
				$mag['edit_index_img'] = explode(',', trim($mag['edit_index_img']));
			}
		}
		if ($mag_list == array()) $mag_list = null;
		return $mag_list;
	}	//}}}	

	function _mag_list_extra($where, $from_url){	//杂志列表附加值{{{
		$total = $this->mag_db->total(MAGAZINE_TABLE, $where);
		return array(
				'type' => $from_url['type'],
				'start' => $from_url['start'],
				'limit' => $from_url['limit'],
				'total' => $total,
				);
	}	//}}}

	function _get_mag_download($mag_list){	//拼出杂志下载地址{{{
		foreach($mag_list as &$mag){
			if ($mag['filepath'] && $mag['filename_ftp'])
				$mag['download'] = $this->config->item('file_hosts').$mag['filepath'].$mag['filename_ftp'];
			else
				$mag['download'] = null;
		}
		return $mag_list;
	}	//}}}
	
	function _get_index_mag_list($table2, $field1, $field2, $limit, $start, $where){		//获取首页杂志列表{{{
		$result = $this->db
						->select ('m.*, u.nickname')
						->from("magazine as m")
						->join("$table2 as u", "m.".$field1."= u.".$field2)
						->where($where)
						->order_by('m.weight desc')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		return $result;
	}//}}}
	
	function _get_same_author_mag($magazine_id, $limit, $start, $status = '2'){		//获得该作者的其他杂志{{{
		$result = $this->db
						->select('user_id')
						->from(MAGAZINE_TABLE)
						->where(array('magazine_id' => $magazine_id))
						->get()
						->row_array();
		$user_id = $result['user_id'];
		$row = $this->db
					->from(MAGAZINE_TABLE)
					->where(array('user_id' => $user_id, 'magazine_id <>' => $magazine_id, 'status' => $status))
					->order_by('weight desc')
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
		return $row;
	}//}}}
	
	function _get_same_category_mag($magazine_id, $limit, $start, $status = '2'){		//获得同类型杂志{{{
		$result = $this->db
						->select('mag_category')
						->from(MAGAZINE_TABLE)
						->where(array('magazine_id' => $magazine_id))
						->get()
						->row_array();
		$mag_cat = $result['mag_category'];
		$row = $this->db
						->from(MAGAZINE_TABLE)
						->where(array('mag_category' => $mag_cat, 'magazine_id <>' => $magazine_id, 'status' => $status))
						->order_by('weight desc')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		return $row;
	}//}}}
	
	function _get_mag_for_list($style, $where, $limit, $start){		//杂志列表页数据{{{
		//tag mag_category order_by
		if ($style == 'new'){
			$result = $this->db
							->select ('m.*,u.nickname, u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.publish_time desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}else if ($style == 'hot'){
			$result = $this->db
							->select ('m.*,u.nickname, u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.num_loved desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}else if ($style == 'common'){
			$result = $this->db
							->select ('m.*,u.nickname,u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.weight desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}
		return $result;
	}//}}}
}
