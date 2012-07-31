<?php
class Mag_file_model extends mag_db{

	private  $ftp_path;

	function Mag_file_model(){
		parent::__construct();
		$ftp_path = $this->config->item('ftp_path');
	}
	function _check_file($file_data,$user_id){
		return true;
		$filepath = $ftp_path.$user_id.'/'.$file_data['ftpfilename'];
		if(!file_exists($filepath)) return false;
		if(!filesize($filepath)==$file_data['filesize']) return false;
		if(!md5_file($filepath)==$file_data['filemd5']) return false;
		return true;
	}
	function save_mag_file($filename, $user_id){
		$mag_path = $this->config->item('ftp_dir').$user_id.'/'.$filename;
		$mag_file_info = array(
			'filename_show' => substr($filename, 0, -5),
			'filepath' => $user_id,
			'filename_ftp' => $filename,
			'user_id' => $user_id,
			'filesize' => filesize($mag_path)
		);
		$mag_fileid = $this->insert_row(MAG_FILE_TABLE, $mag_file_info);	
		$magazine_info = array(
				'mag_file_id' => $mag_fileid,
				'user_id' => $user_id,
				'name' => $filename,
				'status' => 0
				);
		$magazine_id = $this->insert_row(MAGAZINE_TABLE, $magazine_info);
		$sql = "update ".MAG_FILE_TABLE." set magazine_id = $magazine_id". " where mag_file_id = $mag_fileid";
		$this->db->query($sql);
		return $magazine_id;
	}

}
