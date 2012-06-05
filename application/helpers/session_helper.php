<?php

	function _get_session(){	//{{{
                if(!session_id()) {
                       session_start(); 
                       $sid = session_id();
                }else{
                        $sid = $this->_get_non_empty('session_id');
                        session_id($sid);
                        session_start(); 
                        if(!session_id()) {session_start();} 
                }
	}	//}}}

