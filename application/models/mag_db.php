<?php
class mag_db extends CI_Model {
	

	function  __construct(){
		parent::__construct();
        $this->load->database();
    }

	function row ($table, $where){	//查询单条数据{{{
		$row = $this->db
				->from($table)
				->where($where)
				->get()
				->row_array();
		return $row;
	}	//}}}

	function rows ($table, $where, $limit = NULL, $start = NULL){	//查询单条数据{{{
		$this->db
				->from($table)
				->where($where);
		if ($limit){
			$this->db
				->limit($limit)
				->offset($start);
		}
		$result = $this->db
				->get()
				->result_array();
		return $result;
	}	//}}}

	function mag_rows ($mag, $magfile, $where, $limit = NULL, $start = NULL){	//获取杂志数据{{{
		$this->db
				->from($mag)
				->join($magfile, $magfile.".mag_file_id=".$mag.".mag_file_id")
				->where($where);
		if ($limit){
			$this->db
				->limit($limit)
				->offset($start);
		}
		$result = $this->db
				->get()
				->result_array();
		return $result;
	}	//}}}

	function total ($table, $where){	//统计数量{{{
		$return = $this->db
				->from($table)
				->where($where)
				->count_all_results();
		return $return;
	}	//}}}

	function insert_row ($table, $data){	//{{{
		$row = $this->db->insert($table, $data);
		return $this->db->insert_id();
	}	//}}}

}
