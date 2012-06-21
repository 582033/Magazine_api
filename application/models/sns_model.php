<?php
class Sns_Model extends mag_db {
	
	const TABLE_BIND = 'account_bind';
	
	/**
	 * 通过sisid与uid获取绑定信息
	 * @param $snsid
	 * @param $uid
	 */
	public function getBindBySns($snsid,$uid) {
		$where = array(
			'snsid' => $snsid,
			'uid' => $uid,
			'status'=>1
				);
		return $this->row(self::TABLE_BIND,$where);
	}
	/**
	 * 获取绑定信息
	 * @param $accountId
	 */
	public function getBindByUser($accountId,$snsid=null) {
		$return = array();
		$accountId = (int)$accountId;
		if($accountId) {
			$where = array('account_id'=>$accountId);
			if($snsid) {
				$where['snsid'] = $snsid;
			}
			$where['status'] = 1;
			$return = $this->rows(self::TABLE_BIND,$where);
		}
		return $return;
	}

	/**
	 * 绑定第三方用户
	 */
	public function bind($accountId,$snsid,$uid,array &$oauth,$refresh=true) {
		try  {
			$query = 'REPLACE INTO `'.self::TABLE_BIND.'` (`account_id`,`snsid`,`uid`,`access_auth`,`updated_at`';
			if(!$refresh) {
				$query.=',`created_at`';
			}
			$accountId = (int)$accountId;
			$time = time();
			$query.=",`status`) VALUES ({$accountId},{$this->db->escape($snsid)},{$this->db->escape($uid)},{$this->db->escape(json_encode($oauth))},{$time}";
			if(!$refresh) {
				$query.=','.$time;
			}
			$query.=',1)';
			$this->db->query($query);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}
	/**
	 * 取消绑定
	 * @return boolean
	 */
	public function unbind($userId,$snsid) {
		if(!$userId || !$snsid) {
			return false;
		}
		try {
			$this->update_row(self::TABLE_BIND, array('status'=>0), array('account_id'=>$userId,'snsid'=>$snsid));
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}
}
