<?php
class Mag_Model extends mag_db {
	
	function Mag_model(){
		parent::__construct();
	}

	function _get_category_list (){	//获取杂志分类{{{
		$where = array('dic_name' => 'mag_category');
		$category_list = $this->rows(DICTIONARY_TABLE, $where);
		$category_return = array();
		foreach ($category_list as $cat){
			$category_return[] = array(
					'id' => $cat['dic_key'],
					'name' => $cat['dic_value'],
					);
		}
		return $category_return;
	}	//}}}

	function _get_mag_list($where, $from_url = null){	//获取杂志列表结果{{{
		if ($from_url){
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where, $from_url['limit'], $from_url['start']);
		}
		else{
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where);
		}
		$mag_list = $this->_get_mag_download($mag_list);
		foreach ($mag_list as &$mag){
			if ($mag['edit_index_img']){
				$mag['edit_index_img'] = explode(',', trim($mag['edit_index_img']));
			}
		}
		if ($mag_list == array()) $mag_list = null;
		return $mag_list;
	}	//}}}	

	function _mag_list_extra($where, $from_url){	//杂志列表附加值{{{
		$total = $this->mag_db->total(MAGAZINE_TABLE, $where);
		return array(
				'type' => $from_url['type'],
				'start' => $from_url['start'],
				'limit' => $from_url['limit'],
				'total' => $total,
				);
	}	//}}}

	function _get_mag_download($mag_list){	//拼出杂志下载地址{{{
		foreach($mag_list as &$mag){
			if ($mag['filepath'] && $mag['filename_ftp'])
				$mag['download'] = $this->config->item('file_hosts').$mag['filepath'].$mag['filename_ftp'];
			else
				$mag['download'] = null;
		}
		return $mag_list;
	}	//}}}
	
	function _get_index_mag_list($table2, $field1, $field2, $limit, $start, $where){		//获取首页杂志列表{{{
		$result = $this->db
						->select ('m.*, u.nickname')
						->from("magazine as m")
						->join("$table2 as u", "m.".$field1."= u.".$field2)
						->where($where)
						->order_by('m.weight desc')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		return $result;
	}//}}}
	
	function _get_same_author_mag($magazine_id, $limit, $start, $status = '2'){		//获得该作者的其他杂志{{{
		$result = $this->db
						->select('user_id')
						->from(MAGAZINE_TABLE)
						->where(array('magazine_id' => $magazine_id))
						->get()
						->row_array();
		$user_id = $result['user_id'];
		$row = $this->db
					->from(MAGAZINE_TABLE)
					->where(array('user_id' => $user_id, 'magazine_id <>' => $magazine_id, 'status' => $status))
					->order_by('weight desc')
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
		return $row;
	}//}}}
	
	function _get_same_category_mag($magazine_id, $limit, $start, $status = '2'){		//获得同类型杂志{{{
		$result = $this->db
						->select('mag_category')
						->from(MAGAZINE_TABLE)
						->where(array('magazine_id' => $magazine_id))
						->get()
						->row_array();
		$mag_cat = $result['mag_category'];
		$row = $this->db
						->from(MAGAZINE_TABLE)
						->where(array('mag_category' => $mag_cat, 'magazine_id <>' => $magazine_id, 'status' => $status))
						->order_by('weight desc')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		return $row;
	}//}}}
	
	function _get_mag_for_list($style, $where, $limit, $start){		//杂志列表页数据{{{
		//tag mag_category order_by
		if ($style == 'new'){
			$result = $this->db
							->select ('m.*,u.nickname, u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.publish_time desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}else if ($style == 'hot'){
			$result = $this->db
							->select ('m.*,u.nickname, u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.num_loved desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}else if ($style == 'common'){
			$result = $this->db
							->select ('m.*,u.nickname,u.avatar')
							->from(MAGAZINE_TABLE . ' as m')
							->join('user as u', "m.user_id = u.user_id")
							->where($where)
							->order_by('m.weight desc')
							->limit($limit)
							->offset($start)
							->get()
							->result_array();
		}
		$mag_list = array();
		for ($i = 0; $i<count($result); $i++){
			$mag_list[$i]['id'] = $result[$i]['magazine_id'];
			$mag_list[$i]['name'] = $result[$i][''];
		}
		return $mag_list;
	}//}}}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function _get_magazine_list($where, $limit, $start){		//获取杂志列表(new){{{
		$result = $this->db
						->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
						->from(MAGAZINE_TABLE . ' as mg')
						->join('user as us', "mg.user_id = us.user_id")
						->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
						->where($where)
						->order_by('mg.weight desc')
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		if ($result == array()){
			$mag_list = null;
			$num_rows = 0;
		}else{
			$num_rows =	$this->db
							->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
							->from(MAGAZINE_TABLE . ' as mg')
							->join('user as us', "mg.user_id = us.user_id")
							->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
							->where($where)
							->order_by('mg.weight desc')
							->get()
							->num_rows();
			for ($i = 0; $i < count($result); $i++){
				if (strpos($result[$i]['edit_index_img'], '，')){
					str_replace('，', ',', $result[$i]['edit_index_img']);
				}
				if (strpos($result[$i]['tag'], '，')){
					str_replace('，', ',', $result[$i]['tag']);
				}
				$tag = explode(',', trim($result[$i]['tag']));
				for ($y = 0; $y < count($tag); $y++){
					$tags[$y] = $tag[$y];
				}
				$edit_index_img = explode(',', trim($result[$i]['edit_index_img']));
				for ($x = 0; $x < count($edit_index_img); $x++){
					$edit_index_img[$x] = $this->config->item('thumb_host'). $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x];
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => "http://ping.service.wowpad.cn/thumb?size=180x276&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
									'pageThumbs' => $pageThumbs,
									'likes' => $result[$i]['num_loved'],
									'shares' => $result[$i]['shares'],
									'downloads' => $result[$i]['downloads'],
									'views' => $result[$i]['views'],
									'status' => $result[$i]['status'],
									'author' => array(
													'id' => $result[$i]['user_id'],
													'nickname' => $result[$i]['nickname'],
													//'image' => $result[$i]['avatar'],
													'image' => 'http://misc.360buyimg.com/lib/img/e/logo.png',
													),
									'file' => array(
													'size' => $result[$i]['filesize'],
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		$item = array(
					'kind' => 'magazine#magazines',
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $mag_list,
					);
		return $item;
	}//}}}
	
	function _get_magazine($magazine_id){		//获取单本杂志信息{{{
		$where = array('mg.magazine_id' => $magazine_id);
		$result = $this->db
						->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
						->from(MAGAZINE_TABLE . ' as mg')
						->join('user as us', "mg.user_id = us.user_id")
						->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
						->where($where)
						->order_by('mg.weight desc')
						->get()
						->row_array();
		if ($result == array()) {
			$mag = null;
		}
		else {
			if (strpos($result['edit_index_img'], '，')){
				str_replace('，', ',', $result['edit_index_img']);
			}
			if (strpos($result['tag'], '，')){
				str_replace('，', ',', $result['tag']);
			}
			$tag = explode(',', trim($result['tag']));
			for ($y = 0; $y < count($tag); $y++){
				$tags[$y] = $tag[$y];
			}
			$edit_index_img = explode(',', trim($result['edit_index_img']));
			for ($x = 0; $x < count($edit_index_img); $x++){
				$edit_index_img[$x] = $this->config->item('thumb_host'). $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$edit_index_img[$x];
			}
			$pageThumbs = $edit_index_img;
			$mag = array(
						'id' => $result['magazine_id'],
						'name' => $result['name'],
						'cate' => $result['mag_category'],
						'tag' => $tags,
						'intro' => $result['description'],
						'publishedAt' => $result['publish_time'],
						'cover' => "http://ping.service.wowpad.cn/thumb?size=180x276&fit=c&src=" . $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['index_img'],//$result[$i]['index_img'],
						'pageThumbs' => $pageThumbs,
						'likes' => $result['num_loved'],
						'shares' => $result['shares'],
						'downloads' => $result['downloads'],
						'views' => $result['views'],
						'status' => $result['status'],
						'author' => array(
										'id' => $result['user_id'],
										'nickname' => $result['nickname'],
										//'image' => $this->config->item('api_hosts').$result['avatar'],
										'image' => 'http://t2.baidu.com/it/u=3487571830,2553945247&fm=3&gp=0.jpg',
										),
						'file' => array(
										'size' => $result['filesize'],
										'downloadUrl' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/".$result['magazine_id'].".mag",
										),
					);
		}
		return $mag;
	}//}}}
	
	function _get_user_magazines($userId, $limit, $start, $collection){		//获取用户杂志列表{{{
		if ($collection == 'published'){
			$where = array('mg.user_id' => $userId);
			$result = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by('mg.weight desc')
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
					->order_by('mg.weight desc')
					->get()
					->num_rows();
		}else if ($collection == 'unpublished'){
			$where = array('mg.user_id' => $userId);
			$result = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->where($where)
					->order_by('mg.weight desc')
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
					->order_by('mg.weight desc')
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
					->order_by('mg.weight desc')
					->limit($limit)
					->offset($start)
					->get()
					->result_array();
			$num_rows = $this->db
					->select('mg.*,us.nickname,us.avatar,mf.filesize,mf.filepath,mf.filename_ftp')
					->from(MAGAZINE_TABLE . ' as mg')
					->join('user as us', "mg.user_id = us.user_id")
					->join('mag_file as mf', "mg.magazine_id = mf.magazine_id")
					->join('user_love as ul', "mg.magazine_id = ul.loved_id")
					->where($where)
					->order_by('mg.weight desc')
					->get()
					->num_rows();
		}
		if ($result == array()) {
				$mag_list = null;
		}
		else {
			for ($i = 0; $i < count($result); $i++){
				if (strpos($result[$i]['edit_index_img'], '，')){
					str_replace('，', ',', $result[$i]['edit_index_img']);
				}
				if (strpos($result[$i]['tag'], '，')){
					str_replace('，', ',', $result[$i]['tag']);
				}
				$tag = explode(',', trim($result[$i]['tag']));
				for ($y = 0; $y < count($tag); $y++){
					$tags[$y] = $tag[$y];
				}
				$edit_index_img = explode(',', trim($result[$i]['edit_index_img']));
				for ($x = 0; $x < count($edit_index_img); $x++){
					$edit_index_img[$x] = $this->config->item('thumb_host'). $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x];
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => "http://ping.service.wowpad.cn/thumb?size=180x276&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
									'pageThumbs' => $pageThumbs,
									'likes' => $result[$i]['num_loved'],
									'shares' => $result[$i]['shares'],
									'downloads' => $result[$i]['downloads'],
									'views' => $result[$i]['views'],
									'status' => $result[$i]['status'],
									'author' => array(
													'id' => $result[$i]['user_id'],
													'nickname' => $result[$i]['nickname'],
													//'image' => $result[$i]['avatar'],
													'image' => 'http://t2.baidu.com/it/u=3487571830,2553945247&fm=3&gp=0.jpg',
													),
									'file' => array(
													'size' => $result[$i]['filesize'],
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",													),
								);
			}
		}
		$item = array(
					'kind' => 'magazine#magazines',
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $mag_list,
					);
		return $item;
	}//}}}

	function _get_element($elementId){		//获取单个杂志元素{{{
		$where = array('mag_element_id' => $elementId);
		$type = array('img', 'video');
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
						->where($where)
						->where_in('element_type', $type)
						->get()
						->row_array();
		if ($result == array()){
			$element = null;
		}else{
			$element = array(
						'id' => "$elementId",
						'type' => $result['element_type'],
						'magId' => $result['magazine_id'],
						'page' => $result['parent'],
						'size' => $result['filesize'],
						);
			if ($result['element_type'] == 'image'){
				$element['thumbSize'] = '1x1';
				$element['image'] = array(
									'128' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['url'],
									'180' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['url'],
									'url' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['url'],
									);
			}else if ($result['element_type'] == 'video'){
				$element['thumbSize'] = '2x1';
				$element['image'] = array(
										'128' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['poster'],
										'180' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['poster'],
										'url' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['poster'],
										);
				$element['video'] = array(
									'format' => 'mp4',
									'fileurl' => $this->config->item('file_hosts')."/".$result['user_id']."/".$result['magazine_id']."/web/".$result['url'],
									);
			}
			$element['likes'] = $result['num_loved'];
			$element['shares'] = $result['shares'];
		}
		return $element;
	}//}}}
	
	function _get_element_list($limit, $start){		//获取杂志元素列表{{{
		$where = array();
		$type = array('image', 'video');
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id')
						->from(MAG_ELEMENT_TABLE . ' as me')
						->join('magazine as mz', "mz.magazine_id = me.magazine_id")
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
						->where($where)
						->where_in('element_type', $type)
						->get()
						->num_rows();
		for ($i = 0; $i < count($result); $i++){
			$element_list[$i] = array(
							'id' => $result[$i]['mag_element_id'],
							'type' => $result[$i]['element_type'],
							'magId' => $result[$i]['magazine_id'],
							'page' => $result[$i]['parent'],
							'size' => $result[$i]['filesize'],
							'thumbSize' => '1x1',
							);
			if ($result[$i]['element_type'] == 'image'){
				$element_list[$i]['thumbSize'] = '1x1';
				$element_list[$i]['image'] = array(
									'128' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
									'180' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
									'url' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
									);
			}else if ($result[$i]['element_type'] == 'video'){
				$element_list[$i]['thumbSize'] = '2x1';
				$element_list[$i]['image'] = array(
									'128' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
									'180' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
									'url' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
									);
				$element_list[$i]['video'] = array(
											'format' => 'mp4',
											'fileurl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
											);
			}
			$element_list[$i]['likes'] = $result[$i]['num_loved'];
			$element_list[$i]['shares'] = $result[$i]['shares'];
		}
		$item = array(
					'kind' => 'magazine#elements',
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $element_list,
					);
		return $item;
	}//}}}
	
	function _user_liked_elements($userId, $limit, $start){		//用户喜欢的元素{{{
		$where = array('ul.user_id' => $userId);
		$type = array('image', 'video');
		$result = $this->db
						->select ('me.*,mz.magazine_id,mz.user_id')
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
		if ($result == array()){
			$element_list = null;
		}else{
			for ($i = 0; $i < count($result); $i++){
				$element_list[$i] = array(
								'id' => $result[$i]['mag_element_id'],
								'type' => $result[$i]['element_type'],
								'magId' => $result[$i]['magazine_id'],
								'page' => $result[$i]['parent'],
								'size' => $result[$i]['filesize'],
								);
				if ($result[$i]['element_type'] == 'image'){
					$element_list[$i]['thumbSize'] = '1x1';
					$element_list[$i]['image'] = array(
										'128' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
										'180' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
										'url' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
										);
				}else if ($result[$i]['element_type'] == 'video'){
					$element_list[$i]['thumbSize'] = '2x1';
					$element_list[$i]['image'] = array(
										'128' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
										'180' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
										'url' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['poster'],
										);
					$element_list[$i]['video'] = array(
												'format' => 'mp4',
												'fileurl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['url'],
												);
				}
				$element_list[$i]['likes'] = $result[$i]['num_loved'];
				$element_list[$i]['shares'] = $result[$i]['shares'];
			}
		}
		$item = array(
					'kind' => 'magazine#elements',
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $element_list,
					);
		return $item;
	}//}}}
	
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
		$num_rows = count($tags);
		$item = array(
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $result_item
					);
		return $item;
	}//}}}
	
	function _get_user_tags($userId, $limit, $start){		//用户标签{{{
		$new_result = array();
		$tag_temp = array();
		$tags = array();
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
}
