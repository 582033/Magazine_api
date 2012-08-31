<?php class Magdl extends MY_Controller { 
	function __construct () {
        parent::__construct();
		$this->load->helper('Url');
    }

	function dist($magazine_id, $filename){
		$filenames = array('mag.apk', 'mag.ipa', 'mag.magz', 'mag-pad.ipa', 'mag-ics.apk');
		if(!in_array($filename, $filenames)){
			show_error_text(404);
		}else{
			//下载数+1{{{
			$this->load->model('mag_model');
			$this->mag_model->incr_magazine($magazine_id, 'downloads');
			//}}}
			$read_mag_id = substr($magazine_id, 0, 3);
			$filename = preg_replace('/^(mag)/', $magazine_id, $filename);
	    	$url = $this->config->item('pub_host') . "/$read_mag_id/$magazine_id/dist/$filename";
			redirect($url);
		}
	} 
	function resources($magazine_id){
        $read_mag_id = substr($magazine_id, 0, 3);
        $url = $this->config->item('pub_host') . "/$read_mag_id/$magazine_id/resources.json";
        redirect($url);
	}
}	
