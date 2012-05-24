<?php
class Tool_model extends CI_Model {
	

	function  __construct(){
		parent::__construct();
        $this->load->database();
    }

	function row ($table, $where){	//{{{
		$row = $this->db
				->from($table)
				->where($where)
				->get()
				->row_array();
		return $row;
	}	//}}}

	function insert_row ($table, $data){	//}}}
		$row = $this->db->insert($table, $data);
		return $this->db->insert_id();
	}	//}}}

}
