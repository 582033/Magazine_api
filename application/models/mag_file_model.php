<?php
class Mag_file_model extends mag_db{

	private  $ftp_path;

	function Mag_file_model(){
		parent::__construct();
		$ftp_path = $this->config->item('ftp_path');
	}
	function save_mag_file($file_data,$user_id){
		if($user_id == null){
			return array('errcode' => '1','msg'=>'session 过期');
		}
		if(!$this->_check_file($file_data,$user_id)){
			return array('errcode' => '2','msg'=>'file error');
		}
		$this->_save_mag_file_2db($file_data,$user_id);
			
		return array('errcode' => '0','msg'=>'success');

	}
	function _check_file($file_data,$user_id){
		return true;
		$filepath = $ftp_path.$user_id.'/'.$file_data['ftpfilename'];
		if(!file_exists($filepath)) return false;
		if(!filesize($filepath)==$file_data['filesize']) return false;
		if(!md5_file($filepath)==$file_data['filemd5']) return false;
		return true;
	}
	function _save_mag_file_2db($file_data,$user_id){
		$mag_file_info = array(
			'filename_show' => $file_data['filename'],
			'filepath' => $user_id,
			'filename_ftp' => $file_data['ftpfilename'],
			'user_id' => $user_id,
			'filesize' => $file_data['filesize'] 	
		);
		$this->insert_row(MAG_FILE_TABLE,$mag_file_info);	
	}

}
