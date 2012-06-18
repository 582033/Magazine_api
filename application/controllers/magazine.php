<?php class Magazine extends MY_Controller {

	var $apiver;

	function Magazine (){	//{{{
		parent::__construct();
		$this->load->model('mag_db');
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

	function _get_values ($more, $action){	//{{{
		$result = array();
		foreach ($more as $data){
			$result[$data] = $this->input->$action($data);
		}
		return $result;
	}	//}}}

	function _get_more_non_empty ($more){	//{{{
		$result = array();
		foreach ($more as $data){
			$result[$data] = $this->_get_non_empty($data);
		}
		return $result;
	}	//}}}

	function _no_session_result(){	//{{{
		return array('apiver' => $this->apiver,
				'errcode' => '5',
				'msg' => 'need session_id'
			);
	}	//}}}

	function reg (){	//{{{
		$keys = array('username', 'passwd');
		$user_data = $this->_get_more_non_empty($keys);

		$return = $this->User_Model->regasReader($user_data['username'],$user_data['passwd']);
		$return = array_merge(array('apiver' => $this->apiver), $return);
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{

	    if(!$this->session->checkAndRead()){
			return $this->_no_session_result();
		}
		$key = $this->session->flashdata('key');
		$username = $this->_get_non_empty('username');
		$passwd = $this->_get_non_empty('passwd');
		$return = $this->User_Model->login($username, $passwd, $key);
		if(isset($return['user_id'])){
			$this->session->set_userdata('user_id',$return['user_id']);
		}
		$return['session_id'] = $this->session->get_session_id();
		$this->_json_output($return);
	}	//}}}

	function getKey (){	//{{{
		$key = $this->_generate_key();
		$this->session->initSession();
		$this->session->set_flashdata('key',$key);
		$return['session_id'] = $this->session->get_session_id();
		$return['key'] = $key;
		$this->_json_output($return);
	}	//}}}

	function _generate_key(){	//{{{
		return random_string('alnum',7);
	}	//}}}

	function _passwd_encryption ($passwd){	//密码加密{{{
		return $passwd;
	}	//}}}

	function search (){	//搜索{{{
		$keys = array('keywords', 'start', 'limit');
		$items = $this->_get_more_non_empty($keys);
		$return = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $this->search_model->search($items, 'data'),
				'extra' => $this->search_model->search_extra($items),
				);
		$this->_json_output($return);
	}	//}}}

	function category (){	//{{{
		$category = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $this->Mag_Model->_get_category_list(),
				);
		$this->_json_output($category);
	}	//}}}

	function _get_user_id(){		//获取user_id {{{
		$this->session->initSession();
		$userdata = $this->session->userdata;
		if(isset($userdata['user_id']) && $userdata['user_id'] != ''){
			return $userdata['user_id'];
		}
		else {
			$err = array(
					'apiver' => $this->apiver,
					'errcode' => '4',
					'msg' => "session_id error",
					);
			//$this->_json_output($err);
			echo "session_id error";exit;
		}
	}//}}}

	function mag_list(){	//{{{
		$key = array('start', 'limit');
		$from_url = $this->_get_more_non_empty($key);
		$from_url['type'] = $this->input->get('type');
		if ($from_url['type']){
			$where = array('mag_category' => $from_url['type']);
		}
		else {
			$where = array();
			$from_url['type'] = null;
		}
		$mag_list = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $this->Mag_Model->_get_mag_list($where, $from_url),
				'extra' => $this->Mag_Model->_mag_list_extra($where, $from_url),
				);
		$this->_json_output($mag_list);
	}	//}}}

	function detail(){	//{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$data = $this->Mag_Model->_get_mag_list($where);
		$detail = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $data[0],
				);
		$this->_json_output($detail);
	}	//}}}

	function download(){	//获取杂志下载地址{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$mag = $this->Mag_Model->_get_mag_list($where);
		if ($mag != array())
			$return = $mag[0]['download'];
		else
			$return = null;
		$this->_json_output($return);
	}	//}}}

	function ads(){		//广告接口{{{
		$keys = array('start', 'limit');
		$items = $this->_get_more_non_empty($keys);
		$items['position'] = $this->input->get('position');
		$return = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->Ads_Model->_get_ads_links($items),
				);
		$this->_json_output($return);
	}	//}}}

	function love(){					//喜欢{{{
		$user_id = $this->_get_user_id();
		$loved_id = $this->_get_non_empty('loved_id');
		$loved_type = $this->_get_non_empty('loved_type');
		$result = $this->Love_Model->_loved_handle($loved_id, $user_id, $loved_type);
		$return['apiver'] = $this->apiver;
		$return['errcode'] = $result['errcode'];
		$return['data'] = $result['data'];
		$this->_json_output($return);
	}		//}}}

	function get_loved_nums(){			//个人喜欢数量取得	{{{
//		$user_id = $this->_get_user_id();
		$user_id =1;
		$data = $this->Love_Model->_loved_nums($user_id);
		$return = array(
					'apiver' => $this->apiver,
					'errcode' => '0',
					'data' => $data,
					);
		$this->_json_output($return);
	}	//}}}

	function get_loved_data(){				//喜欢数据取得	{{{
		$user_id = $this->_get_user_id();
		$type = $this->input->get('type');
		$mag_category = $this->input->get('mag_category');
		$element_type = $this->input->get('element_type');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		$item = $this->Love_Model->_loved_data($user_id, $limit, $start, $type, $mag_category, $element_type);
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => $item['errcode'],
						'data' => $item['data'],
						);
		$this->_json_output($return);
	}	//}}}

	function comment(){ //{{{		杂志评论
		$now = new DateTime;
		$date = $now->format("Y-m-d H:i:s");
//		$user_id = $this->_get_user_id();
		$user_id = 1;
		$com_data = array(
			'type' => $this->_get_non_empty('type'),
			'object_id' => $this->_get_non_empty('object_id'),
			'comment' => $this->_get_non_empty('comment'),
			'parent_id' => $this->_get_non_empty('parent_id'),
			'user_id' => $user_id,
			'send_time' => date('Y-m-d H:i:s'),
		);
		$data = json_encode($com_data, true);
		$item = $this->User_comment_Model->comment($com_data);
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $item,
						);
		$this->_json_output($return);
	}	//}}}

	function get_user_comment(){//{{{		杂志评论取得
//		$user_id = $this->_get_user_id();
		$user_id = 1;
		$type = $this->_get_non_empty('type');
		$object_id = $this->_get_non_empty('object_id');
		$limit = $this->_get('limit');
		$start = $this->_get('start');
		if($limit){
			$item = $this->User_comment_Model->_get_user_comment($user_id, $type, $object_id, $limit, $start);
		}else{
			$item = $this->User_comment_Model->_get_user_comment($user_id, $type, $object_id);
		}
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => $item['errcode'],
						'data' => $item['data'],
						);
		$this->_json_output($return);
	}	//}}}

	function get_mag_element(){		//{{{		元素取得接口
		$for = $this->_get_non_empty('for');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		$type = $this->input->get('type');
		$item = $this->mag_element_model->_get_mag_element($for, $limit, $start, $type);
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => $item['errcode'],
						'data' => $item['data'],
						);
		$this->_json_output($return);
	}//}}}

	function nums_of_loved(){		//获取对象被喜欢的次数{{{
		$loved_id = $this->_get_non_empty('loved_id');
		$loved_type = $this->_get_non_empty('loved_type');
		$where = array('loved_id' => $loved_id, 'loved_type' => $loved_type);
		$nums = $this->Love_Model->_nums_loved($where);
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $nums,
						);
		$this->_json_output($return);
	}//}}}

	function judge_loved(){		//判断是否喜欢过对象{{{
		//$user_id = $this->_get_user_id();
		$user_id = 1;
		$loved_id = $this->input->get('loved_id');
		$loved_type = $this->input->get('loved_type');
		$where = array('user_id' => $user_id, 'loved_id' => $loved_id, 'loved_type' => $loved_type);
		$nums = $this->mag_db->total(USER_LOVE_TABLE, $where);
		$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $nums,
						);
		$this->_json_output($return);
	}//}}}
	
	function user_info () {	//获取个人信息{{{
		$user_id = $this->_get_user_id();		
		$user_info = $this->User_Model->get_user_info($user_id);
		$this->_json_output($user_info);
	}	//}}}

	function set_user_info () {	//设置个人信息{{{
		$user_id = $this->_get_user_id();
		$user_info = $this->input->post('user_info');
		$return = $this->User_Model->set_user_info($user_id, $user_info);
		$this->_json_output($return);
	}	//}}}

	function uploadfile(){
		if(!$this->session->checkAndRead()){
			return $this->_no_session_result();
		}
		$user_id = $this->session->userdata('user_id');
		$data = $this->input->post('data');
		$file_data = json_decode($data, true);

		$res = $this->mag_file_model->save_mag_file($file_data, $user_id);
//		$pubstr = file_get_contents("http://api.1001s.cn/msgpub/mgtransform?userid=".$user_id."&filename_ftp=".$file_data['filename_ftp']);
		$this->_json_output($res);

	}

	function get_index_mag_list(){	//{{{
		$limit = 13;
		$start = 0;
		$table = USER_TABLE;
		$field1 = $field2 = 'user_id';
		$where = array();
		$mag = $this->Mag_Model->_get_index_mag_list($table, $field1, $field2, $limit, $start, $where);
		$this->_json_output($mag);
	}	//}}}

	function get_nickname (){	//通过user_id获取username{{{
		$user_id = $this->_get_non_empty('user_id');
		$data = $this->User_Model->get_nickname($user_id);
		$this->_json_output($data);
	}	//}}}

	function userindex(){		//用户个人首页{{{
		$user_id = $this->_get_non_empty('user_id');
		$data = $this->User_Model->user_index($user_id);
		$this->_json_output($data);
	}	//}}}

	function pwd (){	//登录测试用{{{
		$usr = $this->input->get('u');
		$pwd = $this->input->get('p');
		$getkey = json_decode(file_get_contents($this->config->item('api_hosts')."/magazine/getkey"), TRUE);
		$key = $getkey['key'];
		$sid = $getkey['session_id'];
		$pwd = md5(md5($pwd).$key);
		$url = $this->config->item('api_hosts')."/magazine/login?username=$usr&passwd=$pwd&session_id=$sid";
		echo "<a href=$url>$url</a>";
	}	//}}}

}
