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
		if($type=='maga'){
			return $this->ad_list_maga($slot);
		
		}
		elseif($type == 'elem')
		{
		
			return $this->ad_list_elem($slot);
		
		}
	$res=$this->ad_model->ad_list($type,$slot);

	$ret=array(
	'kind'=>"magazine#ads",
	'totalResults'=>$res['num'],
	'items'=>$res['content'],

);
	$this->_json_output($ret);
}
	//for elem only
	function ad_list_elem($slot){
		$arr_data=$this->ad_model->ad_list_elem($slot);
	$this->_json_output($arr_data);
	
	
	
	}
	
	//for maga only	
	function ad_list_maga($slot){
		if(isset($_GET['limit'])){
			$limit=(int)$_GET['limit'];
		}
		else{
			$limit=5;
		
		}
		$res=$this->ad_model->ad_list_indextopmaga('maga',$slot,$limit);

		$ret=array(
				'kind' => "magazine#magazine",
				'totalResults' => $limit,
				'start'  => '0',
				'items' => $res,
				);
		$this->_json_output($ret);
	
	}

}
