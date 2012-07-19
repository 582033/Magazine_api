<?php 
class Magclient extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('magclient_model');
	}
	
	function get_ver_info($client){		//{{{
		$ver_info = $this->magclient_model->_get_ver_info($client);
		header("HTTP/1.1 200");
		$this->_json_output($ver_info);
	}//}}}
}
