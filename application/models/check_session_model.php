<?php
/*************************************************
	如果需要验证session_id是否正确,则调用此Model

	如果session_id存在,则返回此session_id对应的user_id;
	如果不存在,则设置http头为401
**************************************************/
class check_session_model extends CI_Model{
	function __construct() {
		$this->load->library('session');
	}

	function check_session () {	//{{{
		if(!$this->session->checkAndRead()){
			show_error_text(401, 'Unauthorized');
		}
		else {
			$user_id = $this->session->userdata('user_id');
			return $user_id;
		}
	}	//}}}
}
