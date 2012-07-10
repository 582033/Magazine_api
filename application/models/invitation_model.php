<?php
class Invitation_Model extends mag_db {

	function  __construct(){
		parent::__construct();
	}

	function check_invitation_code($code) {
		$row = $this->row(INVITATION_CODES_TABLE, array('code' => $code));
		if ($row) {
			if ($row['used'] == 1) return 'CODE_USED';
			else return 'OK';
		}
		else return 'INVALID_CODE';
	}
	function use_code($code) {
		$this->update_row(INVITATION_CODES_TABLE, array('used' => 1), array('code' => $code));
	}
}
