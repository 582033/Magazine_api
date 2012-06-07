<?php class SessionTest extends MY_controller {

	function SessionTest (){	//{{{ 
		parent::__construct();
		$this->load->library('session');
	}	//}}}

	function createSession(){
		$this->session->initSession();
		$this->session->set_userdata('a','m');
		$sessionid = $this->session->get_session_id();
		$this->output->set_output($sessionid);
	}
    	function needSession(){
	    	if(!$this->session->checkAndRead()){
			echo 'need session but can\'t read it ';
			return;
		}
		$sessionid = $this->session->get_session_id();
		$a = $this->session->userdata('a');
		$this->session->set_userdata('b','n');
   		$this->output->set_output('sessionid:'.$sessionid.'a:'.$a);
	}

    	function needSession2(){
	    	if(!$this->session->checkAndRead()){
			echo 'need session but can\'t read it ';
			return;
		}
		$sessionid = $this->session->get_session_id();
		$a = $this->session->userdata('a');
		print_r($this->session->all_userdata());
   		$this->output->set_output('sessionid:'.$sessionid.'a:'.$a.',b:'.$this->session->userdata('b'));
	}

	function notneedSession(){
		$this->output->set_output('s');
	}
}
