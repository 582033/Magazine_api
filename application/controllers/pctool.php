<?php
class pctool extends MY_Controller {
	function pctool (){   //{{{
		parent::__construct();
		$this->load->model('ftp_model');
		$this->apiver = $this->config->item('api_version');
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
}
