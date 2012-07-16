<?php
class Msg_Model extends mag_db {

	function Msg_Model(){
		parent::__construct();
	}
	//function to add activity 
	function msg_add($json_ctt){
		$arr_ins=json_decode($json_ctt,TRUE);
		$act_type = $arr_ins['verb'];

		if($act_type =='signup'){
			//check column
			if((!isset($arr_ins['user_id']) ||(!isset($arr_ins['actor'])) ||(!isset($arr_ins['msg_content']))))
			{
				echo "column missing";
				header('HTTP/1.1 400');exit;

			}
		}
		elseif($act_type == 'love_author')
		{
			if((!isset($arr_ins['user_id']) ||(!isset($arr_ins['actor'])) ||(!isset($arr_ins['msg_content']))))
			{
				echo "column missing";
				header('HTTP/1.1 400');exit;

			}

		}
		else{
			// unknown  type	
			echo "unknown activity type";
			header('HTTP/1.1 400');exit;

		}



	$res=$this->db->insert(MSG_TABLE,$arr_ins);
	return $res;

}

	//function to update status from 0 to 1 ,means unread to read 
	function msg_up($m_id,$u_id){
		$data=array(
				'status'=>'1',
			   );
		$where="msg_id = $m_id and user_id = $u_id";
		$res=$this->db->update(MSG_TABLE,$data,$where);
		$nums_up=mysql_affected_rows();
		return $nums_up;
	}
	
	//function to delete activity of user,check user id and msg id 
	function msg_del($m_id,$u_id){
		$where="msg_id = $m_id and user_id = $u_id";
		$res=$this->db->delete(MSG_TABLE,$where);
		$nums_del=mysql_affected_rows();
		return $nums_del;

	}
	//function to list activity of special user
	function msg_list($filter){
	$filter['user_id']=$filter['uid'];
	$arr_where=array();
	foreach(array('verb','status','user_id') as $k => $v){
	if(array_key_exists($v,$filter)){
	$arr_where[$v]=$filter[$v];
}

}
	//$res_list=$this->db->get_where(MSG_TABLE,$arr_where,$filter['start'],$filter['limit']);

$req_sql='select * from `'.MSG_TABLE.'` where';
$req_where='';
if(count($arr_where)>0)
{
	foreach($arr_where as $k => $v){
$req_where.=' and '.'`'.$k.'`'.'='."'".$v."'";

}
}
$req_where=substr($req_where,4,strlen($req_where)-1);
$req_sql=$req_sql.$req_where;
$req_sql.=' limit '.$filter['start'].','.$filter['limit'];
	$res_list=$this->db->query($req_sql);
	$arr_ret=array();
	foreach($res_list->result() as $row){
	$arr_ret[]=$row;

}
	$arr_all=array();
	$arr_all['num']=$res_list->num_rows();
	$arr_all['content']=$arr_ret;
	return $arr_all;
	
	}
	//function to count unread nums of a user
	function get_unread($uid){
	$query=$this->db->get_where(MSG_TABLE,array('status'=>'0','user_id'=>$uid));
	return (int)$query->num_rows;
	}
	function get_msgnum($uid){
	$query=$this->db->get_where(MSG_TABLE,array('user_id'=>$uid));
	return (int)$query->num_rows;
	}
}
