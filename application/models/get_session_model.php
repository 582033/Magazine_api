<?php
class get_session_model extends mag_db {
	function get_session_model(){
		parent::__construct();
	}	

	function _get_session(){	//{{{
                if(!session_id() or session_id() == '') {
                   	$sid = $this->_get_non_empty('session_id');
			if (!$sid){
				$error = array(
					'apiver' => $this->config->item('api_ver'),
					'err_code' => '3',
					);
				$this->_json_output($error);
				exit;
			}
			else {
				session_id($sid);
			}
                }
	}	//}}}

}
