<?php
class User_Model extends mag_db {

	function  __construct(){
		parent::__construct();
		$this->load->library('PicThumb');
	}

	function regasReader ($username, $passwd, $nickname=NULL){   //注册为读者{{{
		$user_is_exist = $this-> _get_user_by_accountname($username);
		if (!$user_is_exist){
			$account_info = array(
							'account_name' => $username,
							'passwd' => $this->_passwd_encryption($passwd),
							'created_at' => time(),
							'rmsalt' => random_string('alnum', 7).uniqid(),
							);
			$user_id = $this->insert_row(ACCOUNT_TABLE, $account_info);
			if (!$nickname) $nickname = preg_replace('/@.+/', '', $username);
			$this->insert_row(USER_TABLE, array('user_id' => $user_id, 'nickname' => $nickname));
			$this->session->initSession();
			$this->session->set_userdata('user_id', $user_id);
			$return = array(
							'status' => 'OK',
							'session_id' => $this->session->get_session_id(),
							'expires_in' => 7200,
							'nickname' => $nickname,
							'id' => $user_id,
							);
		}
		else {
			$return = array(
							'status' => 'USER_EXISTS',
							);
		}
		return $return;
	} //}}}

	function  remember_signin ($username, $rmsalt) {	//记住用户名密码时用此方法检测登录{{{
		$user_is_exist = $this->_get_user_by_accountname($username);
		if(!$user_is_exist){
		return array(
					'status' => 'AUTH_FAIL',
					);
		}else{
			if ($rmsalt == $this->_rmsalt_encryption($user_is_exist['passwd'], $user_is_exist['rmsalt'])){
				$user_info = $this->get_user_info($user_is_exist['account_id']);
				$this->session->initSession();
				$this->session->set_userdata('user_id', $user_info['id']);
				$session_id = $this->session->get_session_id();
				$return = array(
						'status' => 'OK',
						'session_id' => $session_id,
						'expires_in' => '200',
						'nickname' => $user_info['nickname'],
						'id' => $user_info['id'],
						);
				return $return;
			 }
			 else {
				return  array(
					'status' => 'AUTH_FAIL',
					);
			}
		}
	}	//}}}

	function get_avatar_url($user_id, $avatar_id) {
		if (!$avatar_id) $avatar = '0/default.jpg';
		else $avatar = "$user_id/$avatar_id.jpg";
		return $this->config->item('img_host') . "/avatar/$avatar";
	}
	function mapping_user_info ($user_info, $projection='full') { //数据库用户信息映射{{{
		$projection2parts = array(
				'short' => array('short'),
				'basic' => array('short', 'basic'),
				'full' => array('short', 'basic', 'full'),
				);
		$parts = $projection2parts[$projection];

		$user = array();
		if (in_array('short', $parts)) {
			$user_short = array(
					'id' => $user_info['user_id'],
					'nickname' => $user_info['nickname'],
					'image' => $this->get_avatar_url($user_info['user_id'], $user_info['avatar']),
					'url' => $this->config->item('www_host') . "/user/$user_info[user_id]",
					);
			$user = array_merge($user, $user_short);
		}
		if (in_array('basic', $parts)) {
			$tags = $user_info['tag'] ? explode(",", $user_info['tag']) : null;
			$user_basic = array(
					'birthday' => $user_info['birthday'],
					'url' => $this->config->item('www_host') . "/user/$user_info[user_id]",
					'gender' => $user_info['sex'],
					'intro' => $user_info['intro'],
					'tags' => $tags,
					'role' => $user_info['user_type'],
					'province' => $user_info['province'],
					'city' => $user_info['city'],
					);
			$user = array_merge($user, $user_basic);
		}
		if (in_array('full', $parts)) {
			$user_full = array(
					'followers' => '999',
					'followees' => '999',
					'magazines' => '999',
					);
			$user = array_merge($user, $user_full);
		}

		return $user;
	} //}}}

	function login($username, $passwd, $key){ //{{{
		$user_is_exist = $this->_get_user_by_accountname($username);
		if(!$user_is_exist){
			return array(
						'status' => 'AUTH_FAIL',
						);
		}
		else {
			if ($passwd == $this->_passwd_encryption($user_is_exist['passwd'].$key)){
				$user_info = $this->get_user_info($user_is_exist['account_id']);
				$this->session->initSession();
				$this->session->set_userdata('user_id', $user_info['id']);
				$session_id = $this->session->get_session_id();
				$return = array(
						'status' => 'OK',
						'session_id' => $session_id,
						'expires_in' => '200',
						'nickname' => $user_info['nickname'],
						'id' => $user_info['id'],
						);
				return $return;
			 }
			 else {
				return  array(
					'status' => 'AUTH_FAIL',
					);
			}
		}
	} //}}}

	function _get_user_by_accountname($username){       //检测用户名是否存在{{{
		$where = array('account_name' => $username);
		$user_result = $this->row(ACCOUNT_TABLE, $where);
		if ($user_result == array())
			return  false;
		else
			return $user_result;
	}       //}}}

	function _passwd_encryption ($passwd){  //密码加密{{{
		return md5($passwd);
	}       //}}}

	function _rmsalt_encryption ($passwd, $rmsalt){  //记住密码时返回的永久key加密{{{
		return md5(md5($passwd) . $rmsalt);
	}       //}}}

	function get_nickname ($user_id){	//获取用户昵称{{{
		$where = array('user_id' => $user_id);
		$userdata = $this->mag_db->row(USER_TABLE, $where);
		if ($userdata == array()) {
			$return = array(
					'apiver' => $this->apiver,
					'errcode' => '1',	//代表无此用户
					);
		}
		else {
			$return = array(
					'apiver' => $this->apiver,
					'errcode' => '0',
					'nickname' => $userdata['nickname'],
					);
		}
		return $return;
	}	//}}}

	function get_ftp_info ($user_id) {	//{{{
		$user_info = $this->get_user_info($user_id);
		$ftpinfo = array(
				'user' => 'internet', // ftp用户名
				'passwd' => 'ltinternet', // ftp密码
				'host' => $this->config->item('ftp_host'), // ftp host, 如 ftp.1001s.cn
				'port' => $this->config->item('ftp_port'), // ftp端口
				'path' => '/', // 上传的路径, 如 "/users/{userId$user_info['']"
				'spaceQuota' => '99999999', // 用户空间配额 (Bytes)
				'spaceLeft' => '99999999', // 剩余空间, (Byt

				);
		return $ftpinfo;
	}	//}}}

	function get_user_info ($user_id) {	//{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->mapping_user_info($this->row(USER_TABLE, $where));
		return $user_info;
	}	//}}}

	function get_all_users ($start, $limit, $keyword=null) {	//{{{
		$where = $keyword ? "where nickname like'%$keyword%'" : null;
		$items = $this->db->query("select * from user $where limit $start,$limit;")->result('array');
		if ($items == array()) {
			$return = null;
		}
		else {
			$return = array();
			foreach ($items as $item) {
				$return[] = $this->mapping_user_info($item);
			}
		}
		$total = $this->db->query("select * from user $where;")->num_rows();
		$users= array(
				'kind' => "magazine#persons",
				'start' => $start, // 结果总数
				'totalResults' =>  $total, // 返回的初始元素索引
				'items' => $return,
				);
		return $users;
	}	//}}}

	function set_user_info ($user_id, $user_info){	//{{{
		$where = array('user_id' => $user_id);
		$user_is_exists = $this->get_user_info($user_id);
		$return = array('apiver' => $this->config->item('api_version'));
		if($user_is_exists != array()){
			$data = json_decode($user_info, TRUE);
			$this->update_row(USER_TABLE, $data, $where);
			$return['errcode'] = '0';
			$return['data'] = $this->get_user_info($user_id);
		}
		return $return;
	}	//}}}

	function _get_user_mag($user_id){	//{{{
		$sql = "select * from magazine where user_id='$user_id'";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		foreach($result as $k => $v){
			$result[$k]['index_img'] = $this->picthumb->pic_thumb($this->config->item('pub_host').'/'.$user_id.'/'.$result[$k]['magazine_id'].'/web/'.$result[$k]['index_img'], '180x276');
			$edit_index_img = explode(',', $result[$k]['edit_index_img']);
			foreach($edit_index_img as $key => $val){
				$edit_index_img[$key] = $this->picthumb->pic_thumb($this->config->item('pub_host').'/'.$user_id.'/'.$result[$k]['magazine_id'].'/web/'.$edit_index_img[$key], '180x276');
			}
			$result[$k]['edit_index_img'] = $edit_index_img;
		}
		return $result;
	}	//}}}

	/*
	function _get_user_loved_user($user_id){	//{{{
		$sql = "select * from user_love as L,user as U where L.user_id='$user_id' and L.loved_type='author' and U.user_id=L.loved_id";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		return $result;
	}	//}}}
	*/

	function _get_response ($kind, $total, $start, $items) {
		$response = array(
				'kind' => $kind,
				'totalResults' => $total,
				'start' => $start,
				'items' => $items,
				);
		return $response;	
	}

	function get_user_love($where, $start, $limit, $return_type) {
		$this->db
						->from ("user_love")	
						->join("user", "user.user_id = user_love.user_id")
						->where($where);
		switch ($return_type) {
			case 'result_array':
				$result = $this->db
						->limit($limit)
						->offset($start)
						->get()
						->result_array();
				break;	
			case 'num_rows':
				$result = $this->db->get()->num_rows();
				break;	
		}
		return $result;
	}

	function user_loved ($userId, $type, $start, $limit) {	//{{{
		if ($type == 'followees') {
			$join_user_id = 'loved_id';
			$where_user_id = 'user_id';
		}
		else {
			$join_user_id = 'user_id';
			$where_user_id = 'loved_id';
		}

		$total = $this->db
			->select ('u.*')
			->from(USER_LOVE_TABLE . ' as ul')
			->join(USER_TABLE . ' as u', "ul.$join_user_id = u.user_id")
			->where("ul.$where_user_id", $userId)
			->where('loved_type', 'author')
			->count_all_results();
		$result = $this->db
			->select ('u.*')
			->from(USER_LOVE_TABLE . ' as ul')
			->join(USER_TABLE . ' as u', "ul.$join_user_id = u.user_id")
			->where("ul.$where_user_id", $userId)
			->where('loved_type', 'author')
			->limit($limit)
			->offset($start)
			->get()
			->result_array();
		$user_infos = array();
		foreach ($result as $user_info) {
			$user_infos[] = $this->mapping_user_info($user_info);
		}
		return $this->_get_response("magazine#persons", $total, $start, $user_infos);
	}	//}}}

	function to_be_author($user_id){	//{{{
		$where = array('user_id' => $user_id);
		$data = array('user_type' => 1);
		$this->db->update(USER_TABLE, $data, $where);
	}	//}}}

	function edit_user($user_id, $user_info){	//{{{
		$tags = trim(implode(",", array_unique(array_filter(explode(",", $user_info['tags'])))));
		$tags = trim(preg_replace('/[\s,]+/', ',', $tags), ",");
		$items = array(
				'nickname' => $user_info['nickname'],
				'birthday' => $user_info['birthday'],
				'sex' => $user_info['gender'],
				'province' => $user_info['province'],
				'city' => $user_info['city'],
				'tag' => $tags,
				);
		foreach ($items as $key => $item){
			if ($item != '') {
				$items[$key] = trim($item);
			 }
			else {
				unset($items[$key]);			
			}	
		}
		$where = array('user_id' => $user_id);
		$this->db->update(USER_TABLE, $items, $where);
		header("HTTP/1.1 202");
	}	//}}}
	
	function _change_password($user_id, $item){
		$where = array('account_id' => $user_id);
		$data = array('passwd' => md5($item['new_pwd']));
		$user_info = $this->mag_db->row(ACCOUNT_TABLE, $where);
//		$this->db->update(USER_TABLE, $item, $where);
		if ($user_info['passwd'] == md5($item['old_pwd'])){
			$this->db->update(ACCOUNT_TABLE, $data, $where);
			$return = array(
							'status' => 'OK',
							'session_id' => $this->session->get_session_id(),
							'expires_in' => 7200,
							);
		}else{
			$return = array(
							'status' => 'INVALID_PASSWD',
							);
		}
		header("HTTP/1.1 200");
		return $return;
	}

}
