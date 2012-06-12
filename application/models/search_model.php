<?php
class search_model extends mag_db{

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
	
}

