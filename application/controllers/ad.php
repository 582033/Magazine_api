<?php 
class Ad extends MY_Controller {


	function Ad (){	
		parent::__construct();
		$this->load->model('mag_db');
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
		//限制9个，设为8，因为index从0开始
		foreach($res as $k => $v){
			$res[$k]['id'] = $v['magazine_id'];
			$res[$k]['cate'] = $v['mag_category'];
			$res[$k]['publishedAt'] = $v['publish_time'];
			$res[$k]['likes'] = $v['num_loved'];
			//$res[$k]['cover'] = $this->config->item('thumb_host').$v['index_img'];


			$res[$k]['author'] = array(
					'id' => $v['user_id'],
					'nickname' => $v['nickname'],
					);
			if($k>$limit-1){
				unset($res[$k]);
			
			}
				

			
		
		}

		$ret=array(
				'kind' => "magazine#magazine",
				'totalResults' => '999',
				'start'  => '0',
				'items' => $res,
				);


		echo json_encode($ret);
	
	
	
	}

}
