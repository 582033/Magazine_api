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
		if ($result == array()){
			$mag_list = null;
		}else{
			for ($i = 0; $i < count($result); $i++){
				$edit_index_img = explode(',', trim($result[$i]['edit_index_img']));
				$pageThumbs = json_encode($edit_index_img);
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
									'pageThumbs' => $pageThumbs,
									'likes' => $result[$i]['num_loved'],
									'shares' => $result[$i]['shares'],
									'downloads' => $result[$i]['downloads'],
									'views' => $result[$i]['views'],
									'status' => $result[$i]['status'],
									'author' => array(
													'id' => $result[$i]['user_id'],
													'nickname' => $result[$i]['nickname'],
													'image' => $result[$i]['avatar'],
													),
									'file' => array(
													'size' => $result[$i]['filesize'],
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		return $mag_list;
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
		if ($result == array()){
			$mag_list = null;
		}else{
			for ($i = 0; $i < count($result); $i++){
				$edit_index_img = explode(',', trim($result[$i]['edit_index_img']));
				$pageThumbs = json_encode($edit_index_img);
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
									'pageThumbs' => $pageThumbs,
									'likes' => $result[$i]['num_loved'],
									'shares' => $result[$i]['shares'],
									'downloads' => $result[$i]['downloads'],
									'views' => $result[$i]['views'],
									'status' => $result[$i]['status'],
									'author' => array(
													'id' => $result[$i]['user_id'],
													'nickname' => $result[$i]['nickname'],
													'image' => $result[$i]['avatar'],
													),
									'file' => array(
													'size' => $result[$i]['filesize'],
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		return $mag_list;
	}//}}}
	
	function _get_authors($where, $limit, $start){
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
		}else{
			for ($i = 0; $i < count($result); $i++){
				$edit_index_img = explode(',', trim($result[$i]['edit_index_img']));
				$pageThumbs = json_encode($edit_index_img);
				$mag_list[$i] = array(
									'id' => $result[$i]['magazine_id'],
									'name' => $result[$i]['name'],
									'cate' => $result[$i]['mag_category'],
									'intro' => $result[$i]['description'],
									'publishedAt' => $result[$i]['publish_time'],
									'cover' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/web/".$result[$i]['index_img'],//$result[$i]['index_img'],
									'pageThumbs' => $pageThumbs,
									'likes' => $result[$i]['num_loved'],
									'shares' => $result[$i]['shares'],
									'downloads' => $result[$i]['downloads'],
									'views' => $result[$i]['views'],
									'status' => $result[$i]['status'],
									'author' => array(
													'id' => $result[$i]['user_id'],
													'nickname' => $result[$i]['nickname'],
													'image' => $result[$i]['avatar'],
													),
									'file' => array(
													'size' => $result[$i]['filesize'],
													'downloadUrl' => $this->config->item('file_hosts')."/".$result[$i]['user_id']."/".$result[$i]['magazine_id']."/".$result[$i]['magazine_id'].".mag",
													),
								);
			}
		}
		return $mag_list;
	}
}
