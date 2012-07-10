<?php 
class Ad extends MY_Controller {


	function Ad (){	
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('mag_model');
		$this->load->model('ad_model');
		$this->load->model('check_session_model');
		$this->load->library('session');
	}	
	//list ads if special type and slot
	function ad_list($type,$slot){
	$res=$this->ad_model->ad_list($type,$slot);

	$ret=array(
	'kind'=>"magazine#ads",
	'totalResults'=>$res['num'],
	'items'=>$res['content'],

);
	echo json_encode($ret);
}
	function ad_list_maga($slot,$limit){
		$res=$this->ad_model->ad_list_indextopmaga('maga',$slot,$limit);
			
		

		$ret=array(
				'kind' => "magazine#magazine",
				'totalResults' => $limit,
				'start'  => '0',
				'items' => $res,
				);


		echo json_encode($ret);
	
	
	
	}

}
