<?php

class ApiConfig extends MY_Controller {

	var	$apiver;	

        function ApiConfig (){   //{{{
                parent::__construct();
                $this->load->config();
                $this->apiver = $this->config->item('api_version');

        }       //}}}

	function tool(){    //{{{
		$base_config = $this->_tool_config(); 
		$this->_json_output($base_config);		
	}      //}}}

	function client(){  //{{{
                $base_config = array(
                                'apiver' => $this->apiver,
                                'errcode' => '0',
                                'data' => $this->_api_config(),
                                'extra' => $this->_config_extra(),
                                );
                $this->_json_output($base_config);
	}      //}}}

	function _tool_config(){ //{{{
		$return = array(
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
                return $return;
	}        //}}}
	
        function _config_extra (){      //{{{
                $return = array(
                                'api_hosts' => $this->config->item('api_host'),
                                'pub_host' => $this->config->item('pub_host'),
                                );
                return $return;
        }       //}}}

        function _api_config (){ //{{{
                $return = array(
                                'visitor' => $this->_api_visitor(),
                                );
                return $return;
        } //}}}
	
        function _api_visitor (){       //{{{
                $return = array(
				'getkey' => '/magazine/getkey',
                                'category' => '/magazine/category',
                                'mag_list' => '/magazine/mag_list',
                                'download' => '/magazine/download',
				'detail' => '/magazine/detail',
				'login' => '/magazine/login',
				'reg' => '/magazine/reg',
				'search' => '/magazine/search',
				'love' => '/magazine/love',
				'comment' => '/magazine/get_user_comment',
				'loved_nums' => '/magazine/get_loved_nums',
				'loved_data' => '/magazine/get_loved_data',
				'mag_element' => '/magazine/get_mag_element',
                                );

                return $return;
        }       //}}}	
}
