<?php
class Recommendation_Model extends CI_Model {
	
	function Recommendation_model(){
		parent::__construct();
		$this->load->library('PicThumb');
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
				if (strlen($result[$i]['magazine_id']) <= 3){
					$read_mag_id[$i] = $result[$i]['magazine_id'];
				}else{
					$read_mag_id[$i] = substr($result[$i]['magazine_id'], 0, 3);
				}
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
					$edit_index_img[$x] = $this->picthumb->pic_thumb($this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x], '104x160');
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => $this->picthumb->pic_thumb($this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'], '104x160'),//$result[$i]['index_img'],
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
													'downloadUrl' => $this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/dist/".$result[$i]['magazine_id'].".magz",
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
				if (strlen($result[$i]['magazine_id']) <= 3){
					$read_mag_id[$i] = $result[$i]['magazine_id'];
				}else{
					$read_mag_id[$i] = substr($result[$i]['magazine_id'], 0, 3);
				}
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
					$edit_index_img[$x] = $this->picthumb->pic_thumb($this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/web/".$edit_index_img[$x], '104x160');
				}
				$pageThumbs = $edit_index_img;
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'tag' => $tags,
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => $this->picthumb->pic_thumb($this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'], '104x160'),//$result[$i]['index_img'],
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
													'downloadUrl' => $this->config->item('pub_host')."/".$read_mag_id[$i]."/".$result[$i]['magazine_id']."/dist/".$result[$i]['magazine_id'].".magz",
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
