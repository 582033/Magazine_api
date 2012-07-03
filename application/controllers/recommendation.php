<?php class Recommendation extends MY_Controller {

	var $apiver;

	function Recommendation (){	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('recommendation_model');
		$this->load->model('User_Model');
		$this->load->model('Ads_Model');
		$this->load->model('Mag_Model');
		$this->load->model('Love_Model');
		$this->load->model('mag_element_model');
		$this->load->model('mag_file_model');
		$this->load->model('User_comment_Model');
		$this->load->model('search_model');
		$this->load->library('session');
		$this->apiver = $this->config->item('api_version');
	}	//}}}
	
	function by_category($cateId){		//按照分类推荐{{{
		if ($cateId == NULL){
			header("HTTP/1.1 401");
		}
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$id = $this->input->get('id');	
		if ($id != ''){
			$where = array('mg.status' => '4', 'mg.magazine_id <>' => $id);
		}else{
			$where = array('mg.status' => '4');
		}
		$mag_list = $this->recommendation_model->_get_by_category($where, $limit, $start);
		$this->_json_output($mag_list);
	}//}}}
	
	function maylike(){		//猜你喜欢{{{
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$id = $this->input->get('id');
		if ($id != ''){
			$where = array('mg.status' => '4', 'mg.magazine_id <>' => $id);
		}else{
			$where = array('mg.status' => '4');
		}
		$mag_list = $this->recommendation_model->_get_maylike($where, $limit, $start);
		$this->_json_output($mag_list);
	}//}}}
	
	function authors(){		//作者推荐{{{
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$where = array();
		$mag_list = $this->recommendation_model->_get_authors($where, $limit, $start);
		$this->_json_output($mag_list);
	}//}}}
}
