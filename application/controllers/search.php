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
}
