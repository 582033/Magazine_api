<?php
class pctool extends MY_Controller {

	function pctool (){   //{{{
		parent::__construct();
		$this->load->model('ftp_model');
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->model('mag_file_model');
		$this->load->library('session');
	} 			//}}}
	
	function config(){ //{{{
        $config = array(
                'webLinks' => array(
                    'signup' => $this->config->item('www_host') . $this->config->item('signup'),
                    'management' => $this->config->item('www_host') . $this->config->item('management'),
                    'forgot_password' => $this->config->item('www_host') . $this->config->item('forgot_password'),
                    ),
                'apiLinks' => array(
                    'getkey' => $this->config->item('getkey'),
                    'signin' => $this->config->item('signin'),
                    'ftpinfo' => $this->config->item('ftpinfo'),
                    'uploadComplete' => $this->config->item('uploadcomplete'),
                    ),
				'apiHost' => $this->config->item('api_host'),
                                );
		$this->_json_output($config);
    }        //}}}

	function uploadComplete () {	//{{{
		$filename = $this->_get_non_empty('filename');
		$filemd5 = $this->_get_non_empty('filemd5');
		$this->load->model('check_session_model');
		$user_id = $this->check_session_model->check_session();
		$info = array(
				'status' => $this->ftp_model->check($filename, $filemd5, $user_id),
				);
		if ($info['status'] == 'OK'){
			$magazine_id = $this->mag_file_model->save_mag_file($filename, $user_id);
			$pubstr = file_get_contents($this->config->item('api_host') . "/msgpub/mgtransform?userid=".$user_id."&filename_ftp=".$filename."&magazine_id=".$magazine_id);
		}
		$this->_json_output($info);
	}	//}}}

	function ftpinfo ($user_id) {	//{{{
		$this->load->model('check_session_model');
		$user_id = $this->check_session_model->check_session();
		$ftpinfo = $this->User_Model->get_ftp_info($user_id);
		$this->_json_output($ftpinfo);
	}	//}}}

	function verinfo () {	//pctool组件升级接口{{{
		$vcode = $this->_get_non_empty('vcode');
		$ver_code = $this->config->item('versioncode');
		$this->load->model('pctool_vcode_model');
		$config = $this->pctool_vcode_model->verinfo($vcode, $ver_code);
		$this->_json_output($config);
	}	//}}}
}
