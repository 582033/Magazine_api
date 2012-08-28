<?php class Search extends MY_Controller {
	
	function __construct () {
        parent::__construct();
		$this->load->helper('Url');
    }
	
	function suggestion() {
		$keyword = $this->input->get('q');
		$limit = $this->_get('limit', 10);
		$type = $this->input->get('type');	
		$this->load->model('search_model');	
		$search = $this->search_model->suggest($keyword, $limit, $type);
		return $this->_json_output($search);
	}	
	
	function hotquery() {
		$limit = $this->_get('limit', 10);
		$type = $this->input->get('type');	
		$hot_magazines = array('厦门','三亚','北京','香港','澳门','台湾','丽江','青岛','九寨沟','乌镇','杭州','希腊','缅甸','马德里','意大利','瑞士','斯里兰卡','东南亚','英国','欧洲','日韩','马尔代夫','瑞典','新加坡','购物','美食','温泉','公园','人文','海岛','蜜月','艳遇','潜水','古都','草原','戈壁','雪山');
		$hot_users = array('蓝伯特','sweet-彤彤','乐投','我叫星期二');
	
		switch ($type) {	
			case 'magazine':
				$hot_keywords = $hot_magazines;
			break;

			case 'user':
				$hot_keywords = $hot_users; 
			break;

			case 'all':
			default :
				$hot_keywords = array_merge($hot_magazines, $hot_users);
			break;
		}

		$count = ($limit <= count($hot_keywords)) ? $limit : count($hot_keywords);
		$hotquery = array('items' => array_rand(array_flip($hot_keywords), $count));
		return $this->_json_output($hotquery);
	}

}
