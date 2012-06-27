<?php
class Recommendation_Model extends mag_db {
	
	function Recommendation_model(){
		parent::__construct();
	}
	
	function _get_by_category($where, $limit, $start){		//按照分类推荐{{{
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
						->limit($limit)
						->offset($start)
						->get()
						->num_rows();		
		if ($result == array()){
			$mag_list = null;
		}else{
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
					$edit_index_img[$x] = "http://ping.service.wowpad.cn/thumb?size=104x160&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x];
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => "http://ping.service.wowpad.cn/thumb?size=104x160&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
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
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		$item = array(
					'kind' => 'magazine#magazines',
					'totalResults' => "$num_rows",
					'start' => $start,
					'items' => $mag_list,
					);
		return $item;
	}//}}}
	
	function _get_maylike($where, $limit, $start){		//猜你喜欢{{{
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
						->limit($limit)
						->offset($start)
						->get()
						->num_rows();		
		if ($result == array()){
			$mag_list = null;
		}else{
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
					$edit_index_img[$x] = "http://ping.service.wowpad.cn/thumb?size=104x160&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x];
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => "http://ping.service.wowpad.cn/thumb?size=104x160&fit=c&src=" . $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
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
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		$item = array(
					'kind' => 'magazine#magazines',
					'totalResults' => "$num_rows",
					'start' => $start,
					'items' => $mag_list,
					);
		return $item;
	}//}}}
	
	function _get_authors($where, $limit, $start){
		$result = $this->db
						->from(USER_TABLE)
						->where($where)
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
		$num_rows = $this->db
						->from(USER_TABLE)
						->where($where)
						->limit($limit)
						->offset($start)
						->get()
						->num_rows();
		for ($i = 0; $i < count($result); $i++){
			switch ($result[$i]['user_type']) {
				case '0':
					$role = "reader";
					break;
				case '1':
					$role = "author";
					break;
			}
			$user_infos[$i] = array(
							'id' => $result[$i]['user_id'],
							'nickname' => $result[$i]['nickname'],
							'birthday' => $result[$i]['birthday'],
							'gender' => $result[$i]['sex'],
							'image' => $result[$i]['avatar'],
							'intro' => $result[$i]['intro'],
							'tags' => $result[$i]['tag'],
							'role' => $role,
							'followers' => '999',
							'followees' => '999',
							'magazines' => '999',
							);
		}
		$item = array(
					'kind' => 'magazine#persons',
					'totalResults' => "$num_rows",
					'start' => "$start",
					'items' => $user_infos,
					);
		return $item;
	}
}
