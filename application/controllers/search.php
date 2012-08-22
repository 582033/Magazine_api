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
		$this->load->model('search_model');	
		$result = array();
		switch ($type) {
			case 'magazine':
				$result['items'] = array('旅游','美食');
			break;

			case 'user':
				$result['items'] = array('ycwang','雨溪');
			break;

			case 'all':
				$result['items'] = array('雨溪','美食');
			break;

			case '':
				$result['items'] = array('雨溪','美食');
			break;
		}
		return $this->_json_output($result);
	}

}
