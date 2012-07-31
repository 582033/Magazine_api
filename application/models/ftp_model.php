<?php
class ftp_model extends CI_Model {

	function check ($filename, $filemd5, $userid) {	//{{{
		$mag_path = $this->config->item('ftp_dir').$userid.'/'.$filename;
		if (!file_exists($mag_path)) {
			return 'INVALID_FILE';
		}
		else if (md5_file($mag_path) != $filemd5) {
			return 'MD5_UNMATCH';
		}
		else {
			return	'OK';
		}
	}	//}}}
}
