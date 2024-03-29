<?php
require_once APPPATH . 'models/mag_db.php';

class Mag_Model extends mag_db {
	
	function Mag_model(){
		parent::__construct();
		$this->load->library('PicThumb');
	}

	function _get_magazines_orderby ($orderby) {	//{{{
		switch ($orderby) {
			case 'newest':
				$order_by_field = 'publish_time';
				break;
			case 'likes':
				$order_by_field = 'num_loved';
				break;
			case 'views':
				$order_by_field = 'views';
				break;
			case 'downloads':
				$order_by_field = 'downloads';
				break;
			case 'relevance':
				$order_by_field = 'publish_time';
				break;
		}
		return $order_by_field;
	}	//}}}
	
	function _select_magazines ($tag, $cate, $keyword, $limit, $start, $orderby, $action) { //{{{
		$this->db
			->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
			->from(MAGAZINE_TABLE . ' as mg')
			->join('user as us', "mg.user_id = us.user_id")
			->join('mag_file as mf', "mg.magazine_id = mf.magazine_id");

		$this->db->where(array('mg.status' => '4', 'mg.onoffdel' => '0'));
		if ($tag) $this->db->where("mg.tag like '%$tag%'");
		if ($cate) $this->db->where('mg.mag_category',  $cate);
		if ($keyword) {
			$this->db->where("(mg.name like '%$keyword%' OR mg.tag like '%$keyword%' OR mg.description like '%$keyword%')");
		}
		$this->db->order_by('mg.' . $this->_get_magazines_orderby($orderby) . ' desc');
		$result = NULL;
		switch ($action) {
			case 'result_array':	
				$result = $this->db
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
				break;
				$result['tag'] = str_replace("，", ",", $result['tag']);
			case 'num_rows':	
				$result = $this->db
					->get()
					->num_rows();
				break;
			default:
				show_error("Bug: bad action $action");
		}

		return $result;

	} //}}}

	function _get_magazine_list($tag, $cate, $keyword, $limit, $start, $orderby) { // {{{
		$num_rows = $this->_select_magazines($tag, $cate, $keyword, $limit, $start, $orderby, 'num_rows');
		$result = $this->_select_magazines($tag, $cate, $keyword, $limit, $start, $orderby, 'result_array');
		return $this->magazine_rows2resource($result, $start, $num_rows);
	}//}}}

	function _get_magazine($magazine_id){		//获取单本杂志信息{{{
		$where = array('mg.magazine_id' => $magazine_id, 'mg.onoffdel' => '0');
		$result = $this->db
						->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
						->from(MAGAZINE_TABLE . ' as mg')
						->join('user as us', "mg.user_id = us.user_id")
						->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
						->where($where)
						->order_by('mg.weight desc')
						->get()
						->row_array();
		$mag = $result ? $this->magazine_row2resource($result) : NULL;
		return $mag;
	}//}}}

	function _get_user_magazines($userId, $limit, $start, $collection, $orderby) { //获取用户杂志列表{{{
		$order_by = "mg." . $this->_get_magazines_orderby($orderby) . " desc";
		if ($userId == 'me') $userId = $this->check_session_model->check_session();
		if ($collection == 'published'){
			$where = array('mg.user_id' => $userId, 'mg.status' => '4', 'mg.onoffdel' => '0');
			$result = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by($order_by)
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
			$num_rows = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by($order_by)
					->get()
					->num_rows();
		}else if ($collection == 'unpublished'){
			$where = array('mg.user_id' => $userId, 'mg.status <>' => '4', 'mg.onoffdel' => '0');
			$result = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by($order_by)
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
			$num_rows = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by($order_by)
					->get()
					->num_rows();
		}else if ($collection == 'like'){
			$where = array('ul.user_id' => $userId, 'ul.loved_type' => 'magazine');
			$result = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->join('user_love as ul', "mg.magazine_id = ul.loved_id")
					->where($where)
					->limit($limit)
					->offset($start)
					->order_by($order_by)
					->get()
					->result_array();
			$num_rows = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->join('user_love as ul', "mg.magazine_id = ul.loved_id")
					->where($where)
					->order_by($order_by)
					->get()
					->num_rows();
		}
		return $this->magazine_rows2resource($result, $start, $num_rows);
	}//}}}

	function get_magz_url($magazine_id) { //{{{
		$read_mag_id = substr($magazine_id, 0, 3);
		return $this->config->item('api_host') . "/v". $this->config->item('api_version') . "/magdl/$magazine_id/dist/mag.magz";
	} //}}}

	function get_mag_asset_url($magazine_id, $path) { //{{{
		$read_mag_id = substr($magazine_id, 0, 3);
		return $this->config->item('pub_host') . "/$read_mag_id/$magazine_id/web/$path";
	} //}}}

	function magazine_row2resource($result) { //{{{
		$result['edit_index_img'] = str_replace('，', ',', $result['edit_index_img']);
		$result['tag'] = str_replace('，', ',', $result['tag']);
		$tags = explode(',', trim($result['tag']));
		$images = explode(',', trim($result['edit_index_img']));
		$pageThumbs = array();
		$magazine_id = $result['magazine_id'];
		foreach ($images as $image) {
			$pageThumbs[] = $this->picthumb->pic_thumb($this->get_mag_asset_url($magazine_id, $image), '104x160');
		}
		$CI = & get_instance();
		$CI->load->model('user_model');
		$mag = array(
				'id' => $result['magazine_id'],
				'name' => $result['name'],
				'cate' => $result['mag_category'],
				'tag' => $tags,
				'intro' => $result['description'],
				'publishedAt' => $result['publish_time'],
				'cover' =>   $this->picthumb->pic_thumb($this->get_mag_asset_url($magazine_id, $result['index_img']), '180x276'),
				'pageThumbs' => $pageThumbs,
				'likes' => $result['num_loved'],
				'shares' => $result['shares'],
				'downloads' => $result['downloads'],
				'views' => $result['views'],
				'status' => $result['status'],
				'author' => $this->user_model->mapping_user_info($result, 'short'),
				'file' => array(
					'size' => (int) $result['filesize'],
					'downloadUrl' => $this->get_magz_url($result['magazine_id']),
					),
				);
		switch ($this->input->get('projection')){
			case 'fuller':
				$mag['promotionImages'] = json_decode($result['promotion_images']);		
				break;
		}
		return $mag;
	} //}}}

	function magazine_rows2resource($rows, $start, $num_rows) { //{{{
		/**
		  rows - from db
		 */
		if (!$rows) {
			$magazine_list = array();
		}
		else {
			foreach ($rows as $row) {
				$magazine_list[] = $this->magazine_row2resource($row);
			}
		}

		$item = array(
					'kind' => 'magazine#magazines',
					'totalResults' => $num_rows,
					'start' => (int)$start,
					'items' => $magazine_list,
					);
		return $item;
	} //}}}

	function element_row2resource($result) { // {{{ convert from db row to element resource
		$element = array(
				'id' => $result['mag_element_id'],
				'type' => $result['element_type'],
				'magId' => $result['magazine_id'],
				'magName' => $result['magazine_name'],
				'title' => $result['title'],
				'description' => '',
				'page' => $result['parent'],
				);
		$url ='';
		if (strlen($result['magazine_id']) <= 3){
			$read_mag_id = $result['magazine_id'];
		}else{
			$read_mag_id = substr($result['magazine_id'], 0, 3);
		}

		$url_prefix = $this->config->item('pub_host')."/".$read_mag_id."/".$result['magazine_id']."/web/";
		if ($result['element_type'] == 'image') {
			$url = $url_prefix . $result['url'];
		}
		elseif ($result['element_type'] == 'video') {
			$url = $url_prefix . $result['poster'];
		}
		$w = (int)$result['width'];
		$h = (int)$result['height'];
		$element['image'] = array(
				'original' => array(
					'width' => $w,
					'height' => $h,
					'url' => $url,
					),
				);
		foreach (array(128, 180) as $thumbw) {
			$thumbh = (int)($h * $thumbw / $w);
			$element['image'][$thumbw] = array(
					'width' => $thumbw,
					'height' => $thumbh,
					'url' => $this->picthumb->pic_thumb($url, "${thumbw}x$thumbh"),
					);
		}

		if ($result['element_type'] == 'video') {
			if (strpos($result['url'], ".mp4")) {
				$element['video'] = array(
						'format' => 'mp4',
						'fileurl' => $url_prefix . $result['url'],
						);
			}
			else {
				$element['video'] = array(
						'format' => 'web',
						'weburl' => $result['url'],
						);
			}
		}
		$element['likes'] = (int)$result['num_loved'];
		$element['shares'] = (int)$result['shares'];

		return $element;
	} //}}}

	function element_rows2resource($rows, $start, $num_rows) { //{{{
		/**
		  rows - from db
		 */
		if (!$rows) {
			$element_list = array();
		}
		else {
			foreach ($rows as $row) {
				$element_list[] = $this->element_row2resource($row);
			}
		}

		$item = array(
					'kind' => 'magazine#elements',
					'totalResults' => $num_rows,
					'start' => (int)$start,
					'items' => $element_list,
					);
		return $item;
	} //}}}

	function _get_element($elementId) {		//获取单个杂志元素{{{
		$where = array('me.mag_element_id' => $elementId, 'me.onoffdel' => '0');
		$type = array('image', 'video');
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id, mz.name as magazine_name')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->where($where)
						->where_in('element_type', $type)
						->get()
						->row_array();
		if (!$result) {
			$element = null;
		}else{
			$element = $this->element_row2resource($result);
		}
		return $element;
	}//}}}
	
	function _get_element_list($limit, $start, $order_by, $type=null) {		//获取杂志元素列表{{{
		$where = array('me.onoffdel' => '0');
		$type = !$type ? array('image', 'video') : $type;
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id, mz.name as magazine_name')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->where($where)
						->where_in('element_type', $type)
						->limit($limit)
						->offset($start)
						->order_by($order_by)
						->get()
						->result_array();
		$num_rows = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->where($where)
						->where_in('element_type', $type)
						->get()
						->num_rows();

		return $this->element_rows2resource($result, $start, $num_rows);
	} //}}}

	function _user_liked_elements($userId, $limit, $start){		//用户喜欢的元素{{{
		$where = array('ul.user_id' => $userId);
		$type = array('image', 'video');
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id,mz.name as magazine_name')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->join('user_love as ul', "ul.loved_id = me.mag_element_id")
						->where($where)
						->where_in('element_type', $type)
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		$num_rows = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->join('user_love as ul', "ul.loved_id = me.mag_element_id")
						->where($where)
						->where_in('element_type', $type)
						->get()
						->num_rows();
		return $this->element_rows2resource($result, $start, $num_rows);
	} //}}}
	
	function _get_mag_cates(){		//获取杂志类型{{{
		$where = array('dic_name' => 'mag_category');
		$category_list = $this->rows(DICTIONARY_TABLE, $where);
		$category_return = array();
		foreach ($category_list as $cat){
			$category_return[] = array(
					'key' => $cat['dic_key'],
					'name' => $cat['dic_value'],
					);
		}
		$num_rows = count($category_return);
		$item = array(
					'totalResults' => $num_rows,
					'items' => $category_return,
					);
		return $item;
	}//}}}
	
	function _get_mag_tags($limit, $start){		//杂志标签{{{
		$result = $this->db
						->select ('tag')
						->from(MAGAZINE_TABLE)
						->group_by('tag')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		for ($i =0; $i < count($result); $i++){
			$tags[$i] = $result[$i]['tag'];
		}
		for ($j = 0; $j < count($tags); $j++){
			$where = array('tag like' => "%$tags[$j]%");
			$rows = $this->db
							->from(MAGAZINE_TABLE)
							->where($where)
							->get()
							->num_rows();
			$result_item[$j] = array('name' => "$tags[$j]", 'count' => "$rows");
		}
		$total = $this->db
						->select ('tag')
						->from(MAGAZINE_TABLE)
						->group_by('tag')
						->get()
						->num_rows();
		$item = array(
					'totalResults' => "$total",
					'start' => "$start",
					'items' => $result_item
					);
		return $item;
	}//}}}
	
	function _get_user_tags($userId, $limit, $start){		//用户标签{{{
		$new_result = array();
		$tag_temp = array();
		$tags = array();
		$item = array();
		$where = array('user_id' => $userId);
		$result = $this->db
						->select ('tag')
						->from(MAGAZINE_TABLE)
						->where($where)
						->group_by('tag')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		if ($result == array()){
			$tags = null;
		}else{
			for ($i = 0; $i < count($result); $i++){
				if ($result[$i]['tag'] != ''){
					array_push($new_result, $result[$i]['tag']);
				}
			}
			for ($j = 0; $j < count($new_result); $j++){
				$tag[$j] = explode(',', $new_result[$j]);
			}
			for ($k = 0; $k < count($tag); $k++){
				for ($l = 0; $l < count($tag[$k]); $l++){
					array_push($tag_temp, $tag[$k][$l]);
				}
			}
			$tags_temp = array_unique($tag_temp);
			$nums = array_keys($tags_temp);
			$max = $nums[(count($nums)-1)];
			for ($m = 0; $m <= $max; $m++){
				if (array_key_exists($m,$tags_temp)){
					array_push($tags, $tags_temp[$m]);
				}
			}
		}
		$num_rows = count($tags);
		for ($x = 0; $x < $num_rows; $x++){
			$count = $this->db
							->from(MAGAZINE_TABLE)
							->where(array('tag like' => "%$tags[$x]%"))
							->get()
							->num_rows();
			$item[$x] = array('name' => $tags[$x], 'count' => $count);
		}
		$items = array(
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $item,
					);
		return $items;
	}//}}}

	function incr_magazine($magazineId, $type, $num_times) {	//杂志次数加1{{{
		$sql = 'UPDATE ' . MAGAZINE_TABLE . ' SET ' . $type . ' = ' . $type . ' + ' . $num_times . ' WHERE magazine_id = ' . $this->db->escape($magazineId);
		$this->db->query($sql);
	}	//}}}

	function edit_mag_info ($user_id, $mag_info) {	//编辑并发布杂志{{{
		$where = array('user_id' => $user_id, 'magazine_id' => $mag_info['magazine_id']);
		$update_data = array(
				'name' => isset($mag_info['name']) ? $mag_info['name'] : null,
				'tag' => isset($mag_info['tag']) ? $mag_info['tag'] : null,
				'mag_category' => isset($mag_info['mag_category']) ? $mag_info['mag_category'] : null,
				'description' => isset($mag_info['description']) ? $mag_info['description'] : null,
				'status' => '4',
				);
		$this->db->update(MAGAZINE_TABLE, $update_data, $where);	
		header("HTTP/1.1 202");
	}	//}}}
/*	
	function pub_mag ($user_id, $magazine_id) {	//发布杂志{{{
		$where = array('user_id' => $user_id, 'magazine_id' => $magazine_id, 'status' => '2');
		$update_data = array('status' => '4');
		$this->db->update(MAGAZINE_TABLE, $update_data, $where);
		header("HTTP/1.1 202");
	}	//}}}
*/
}

