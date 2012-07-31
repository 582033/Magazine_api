<?php

class Msgpub extends MY_Controller {

	function __construct() {
		parent::__construct();
	}

	function mgtransform() { //{{{
		/**
		  add to check message queue
		  */

		$userid = $this->_get_non_empty('userid');
		$filename_ftp = $this->_get_non_empty('filename_ftp');
		print_r($filename_ftp);
		print_r($userid);

		$this->load->model('msgbroker');
		$res = $this->msgbroker->mgtransform($userid, $filename_ftp);
	} //}}}

	function ftpuser() { // {{{
		$userid = $this->_get_non_empty('userid');
		$this->load->model('msgbroker');
		$res = $this->msgbroker->ftpuser($userid);
	} //}}}
}
