<?php
class search_model extends CI_Model{

	function search($items, $type){	//{{{
		$keywords = $items['keywords'];
		$start = $items['start'];
		$limit = $items['limit'];
		$this->db->from(MAGAZINE_TABLE)->join(MAG_FILE_TABLE, MAG_FILE_TABLE.".mag_file_id=".MAGAZINE_TABLE.".mag_file_id");
		$this->db
					->like('name', $keywords)
					->or_like('description', $keywords)
					->limit($limit)
					->offset($start);
		if ($type == 'data'){
			$return = $this->db->get()->result_array();
		}
		else{
			$return = $this->db->get()->num_rows();
		}
		return $return == array() ? null : $return;
	}	//}}}

	function search_extra ($items){	//{{{
		$return = array(
				'keywords' => $items['keywords'], 
				'start' => $items['start'],
				'limit' => $items['limit'],
				'total' => $this->search($items, 'extra'),
				);
		return $return;
	}	//}}

	function suggest ($keyword, $limit, $type) {	//{{{
		switch ($type) {
			case 'magazine':
				return $this->suggest_magazine($keyword, $limit);
				break;
			case 'user':
				return $this->suggest_user($keyword, $limit);
				break;
			case 'all':
				return $this->suggest_all($keyword, $limit);
				break;
			case '':
				return $this->suggest_all($keyword, $limit);
				break;
			default :
				show_error_text('404');
				break;
		}
	}	//}}}

	
	function suggest_magazine ($keyword, $limit) {	//{{{
		$this->load->model('mag_model');
		$magazine_item = $this->mag_model->_get_magazine_list(null, null, $keyword, $limit, '0');
		$result_magazine = array();
		foreach ($magazine_item['items'] as $item) {
			$result_magazine[] = $item['name'];
		}
		$result = array();
		$result['q'] = $keyword;
		$result['suggestions'] = array_unique($result_magazine);
		return $result;
	}	//}}}

	function suggest_user ($keyword, $limit) {	//{{{
		$this->load->model('User_Model');
		$user_item = $this->User_Model->get_all_users('0', $limit, $keyword);
		$result_user = array();
		if(is_array($user_item['items'])) {
			foreach ($user_item['items'] as $item) {
				$result_user[] = $item['nickname'];
			}
		}
		$result = array();
		$result['q'] = $keyword;
		$result['suggestions'] = array_unique($result_user);
		return $result;
	}	//}}}
	function suggest_all ($keyword, $limit) {	//{{{
		$this->load->model('User_Model');
		$this->load->model('mag_model');
		$user_item = $this->User_Model->get_all_users('0', $limit, $keyword);
		$magazine_item = $this->mag_model->_get_magazine_list(null, null, $keyword, $limit, '0');
		$result_user = array();
		$result_magazine = array();
		if(is_array($user_item['items'])) {
			foreach ($user_item['items'] as $item) {
				$result_user[] = $item['nickname'];
			}	
		}
		if(is_array($magazine_item['items'])) {
			foreach ($magazine_item['items'] as $item) {
				$result_magazine[] = $item['name'];
			}
		}
		$result = array();
		$result['q'] = $keyword;
		$result['suggestions'] = array_slice(array_unique(array_merge($result_user, $result_magazine)),0,10);
		return $result;
	}	//}}}


}

