<?php
class pctool extends MY_Controller {

	function pctool (){   //{{{
		parent::__construct();
		$this->load->model('ftp_model');
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->library('session');
	} 
	
	function config(){ //{{{
        $config = array(
                'weblinks' => array(
                    'signup' => $this->config->item('signup'),
                    'management' => $this->config->item('management'),
                    ),
                'apilinks' => array(
                    'getkey' => $this->config->item('getkey'),
                    'signin' => $this->config->item('signin'),
                    'ftpinfo' => $this->config->item('ftpinfo'),
                    'uploadcomplete' => $this->config->item('uploadcomplete'),
                    ),
                'tool' => array(
                    'versionname' => $this->config->item('versionname'),
                    'versioncode' => $this->config->item('versioncode'),
                    ),
                                );
		$this->_json_output($config);
    }        //}}}

	function uploadComplete () {	//{{{
		$filename = $this->_get_non_empty('filename');
		$filemd5 = $this->_get_non_empty('filemd5');
		//还未进行用户检测	
		$info = array(
				'status' => $this->ftp_model->check($filename, $filemd5),
				);
		$this->_json_output($info);
	}	//}}}

	function ftpinfo () {	//{{{
		//$user_id = $userId == 'me' ? $this->session->userdata('user_id') : $userId;
		$user_id = '1';
		$ftpinfo = $this->User_Model->get_ftp_info($user_id);
		$this->_json_output($ftpinfo);
	}	//}}}
}
