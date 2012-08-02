<?php class Mag extends MY_Controller {

	function __construct (){	//{{{
		parent::__construct();
		$this->load->model('Mag_Model');
		$this->load->model('mag_file_model');
		$this->load->library('session');
	}	//}}}

	function _no_session_result(){	//{{{
		return array(
				'errcode' => '5',
				'msg' => 'need session_id'
			);
	}	//}}}

	function magazines(){		//获取杂志列表(new){{{
		$tag = $this->input->get('tag');
		$cate = $this->input->get('cate');
		$keyword = $this->input->get('q');
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);

		$mag_list = $this->Mag_Model->_get_magazine_list($tag, $cate, $keyword, $limit, $start);
		$this->_json_output($mag_list);
	}//}}}

	function magazine($magazine_id){ // {{{
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if ($method == 'put'){
			$this->load->model('check_session_model');
			$user_id = $this->check_session_model->check_session();
			$mag_json = file_get_contents('php://input', 'r');
			$mag_info = array_merge(json_decode($mag_json, true), array('magazine_id' => $magazine_id));
			$this->Mag_Model->edit_mag_info($user_id, $mag_info);
		}
		else {
			if ($magazine_id == NULL){
				header("HTTP/1.1 401");
			}
			$mag = $this->Mag_Model->_get_magazine($magazine_id);
			$this->_json_output($mag);
		}
	} //}}}

	function user_magazines($userId, $collection){		//用户的杂志(发布|喜欢|未发布){{{
		if ($userId == NULL){
			header("HTTP/1.1 401");
		}
		if ($collection == NULL){
			header("HTTP/1.1 401");
		}
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$mag_list = $this->Mag_Model->_get_user_magazines($userId, $limit, $start, $collection);
		$this->_json_output($mag_list);
	}//}}}

	function element($elementId){		//获取单个杂志元素{{{
		if ($elementId == NULL){
			header("HTTP/1.1 401");
		}
		$element = $this->Mag_Model->_get_element($elementId);
		$this->_json_output($element);
	}//}}}

	function elements(){		//获取杂志元素列表{{{
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$type = $this->input->get('type');
		$order_by = 'me.weight desc';
		$element_list = $this->Mag_Model->_get_element_list($limit, $start, $order_by, $type);
		$this->_json_output($element_list);
	}//}}}

	function user_liked_elements($userId){		//用户喜欢的元素{{{
		if ($userId == NULL){
			header("HTTP/1.1 401");
		}
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$element = $this->Mag_Model->_user_liked_elements($userId, $limit, $start);
		$this->_json_output($element);
	}//}}}

	function cates(){		//杂志类型{{{
		$cates = $this->Mag_Model->_get_mag_cates();
		$cates = array(
				'totalResults' => 3,
				'items' => array (
							array(
							    'name' => '目的地推荐',
							    ),
							array(
							    'name' => '异域风情',
							    ),
							array(
							    'name' => '狂野中国',
							    ),
						    ),
				);
		$this->_json_output($cates);
	}//}}}

	function tags(){		//杂志标签{{{
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$tags = $this->Mag_Model->_get_mag_tags($limit, $start);
		$this->_json_output($tags);
	}//}}}

	function user_tags($userId, $collection='own'){		//作者对杂志定义的标签{{{
		if ($userId == NULL){
			header("HTTP/1.1 401");
		}
		if ($collection == NULL){
			header("HTTP/1.1 401");
		}
		$limit = $this->_get('limit', 10);
		$start = $this->_get('start', 0);
		$user_tags = $this->Mag_Model->_get_user_tags($userId, $limit, $start);
		$this->_json_output($user_tags);
	}//}}}
}
