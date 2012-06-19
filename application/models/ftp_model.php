<?php
class ftp_model extends CI_Model {

	function check ($filename, $filemd5) {	//}}}
		return	'OK';
		//status: {OK|INVALID_FILE|MD5_UNMATCH}, // OK - 成功, INVALID_FILE - 文件不存在, MD5_UNMATCH - md5验证失败
	}	//}}}
}
