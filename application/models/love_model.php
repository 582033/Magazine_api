<?php
class Love_Model extends mag_db {

	function Love_Model (){
		parent::__construct();
	}

	function _loved_check($loved_id, $user_id, $loved_type){		//喜欢接口{{{
		$where = array(
					'loved_id' => $loved_id,
					'user_id' => $user_id,
					'loved_type' => $loved_type,
					);
		$row = $this->row(USER_LOVE_TABLE, $where);
		if ($row == array()){
			return 'empty';
		}else{
			return $row;
		}
	}		//}}}
	
}
