<?php
class Mag_element_Model extends mag_db {

	function Mag_element_Model (){
		parent::__construct();
	}

	function _get_mag_element($for, $limit, $start, $type){		//喜欢接口{{{
		if ($for == 'index'){
			$order_by = 'weight desc';
			if ($type == ''){
				$where = array();
			}else{
				$where = array('element_type' => $type);
			}
		}else if ($for == 'list'){
			$order_by = 'create_at desc';
			if ($type == ''){
				$where = array();
			}else{
				$where = array('element_type' => $type);
			}
		}
		return $this->elem_rows(MAG_ELEMENT_TABLE, $where, $limit, $start, $order_by);
	}		//}}}
	
}
