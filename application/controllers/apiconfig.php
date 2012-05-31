<?php

class ApiConfig extends MY_Controller {
	
        function ApiConfig (){   //{{{
                parent::__construct();
                $this->load->config();
                $this->apiver = $this->config->item('api_version');

        }       //}}}
	function tool(){    //{{{
		$base_config =  array(
                                'errcode' => '0',
                                'data' => $this->_tool_config()
                                ); 
			
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
                                'regurl' => $this->config->item('regurl'),
				'keyurl' => $this->config->item('keyurl'),
				'loginurl' => $this->config->item('loginurl'),
				'ftpurl' => $this->config->item('ftpurl'),
				'ftpport' => $this->config->item('ftpport'),
				'uploadcurl' => $this->config->item('uploadcurl'),
				'manageurl' => $this->config->item('manageurl'),
				'toolversion' => $this->config->item('toolversion')
                                );
                return $return;
	}        //}}}
	
        function _config_extra (){      //{{{
                $return = array(
                                'api_hosts' => $this->config->item('api_hosts'),
                                'file_hosts' => $this->config->item('file_hosts'),
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
                                'category' => '/magazine/category',
                                'mag_list' => '/magazine/mag_list?type=&start=&limit=',
                                'download' => '/magazine/download?id=1',
                                );

                return $return;
        }       //}}}	
}
