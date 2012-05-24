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

	function rows ($table, $where){	//查询单条数据{{{
		$result = $this->db
				->from($table)
				->where($where)
				->get()
				->result_array();
		return $result;
	}	//}}}


	function insert_row ($table, $data){	//{{{
		$row = $this->db->insert($table, $data);
		return $this->db->insert_id();
	}	//}}}

}
