<?php
class pctool extends MY_Controller {
	function pctool (){   //{{{
		parent::__construct();
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
}
