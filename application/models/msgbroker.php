<?php
class Msgbroker extends CI_Model {
	function __construct() {
		parent::__construct();
		$CI =& get_instance();
		$CI->load->model('mq');
		$this->load->helper('ltitem');
	}
	function mgtransform($userid, $filename_ftp) { //{{{
		$req = array('type' => 'mg', 'userid' => $userid, 'filename_ftp' => $filename_ftp);
		$queue = 'mgtransform';
		$spec = array('cmd' => 'mgtransform', 'req' => $req);
		$this->mq->publish($spec, $queue, 'mgtransform');
		$status = 'ok';
		$cnt = 1;
		$res = array('status' => $status, 'result' => $cnt,
				'spec' => $spec, 'queue' => $queue, 'vhost' => 'mgtransform');
		return $res;
	} //}}}
}
