<?php
class Msgbroker extends CI_Model {
	function __construct() {
		parent::__construct();
		$CI =& get_instance();
		$CI->load->model('mq');
		$this->load->helper('ltitem');
	}

	function mgtransform($userid, $filename_ftp, $magazine_id) { //{{{
		$req = array('type' => 'mg', 'userid' => $userid, 'filename_ftp' => $filename_ftp, 'magazine_id' => $magazine_id);
		$queue = 'mgtransform';
		$spec = array('cmd' => 'mgtransform', 'req' => $req);
		$this->mq->publish($spec, $queue, 'mgtransform');
		$status = 'ok';
		$cnt = 1;
		$res = array('status' => $status, 'result' => $cnt,
				'spec' => $spec, 'queue' => $queue, 'vhost' => 'mgtransform');
		return $res;
	} //}}}
	
	function ftpuser($userid) { //{{{
		$req = array('type' => 'create_user', 'userid' => $userid);
		$queue = 'ftpuser';
		$spec = array('cmd' => 'ftpuser', 'req' => $req);
		$this->mq->publish($spec, $queue, 'ftpuser');
		$status = 'ok';
		$cnt = 1;
		$res = array('status' => $status, 'result' => $cnt,
				'spec' => $spec, 'queue' => $queue, 'vhost' => 'ftpuser');
		return $res;
	} //}}}
}
