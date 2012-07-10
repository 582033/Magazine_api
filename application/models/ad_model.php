<?php
class Ad_Model extends mag_db {

	function Ad_Model(){
		parent::__construct();
	}

	
	//function to list activity of special user
	function ad_list($type,$slot){
		$arr_filter=array();
		foreach(array('limit') as $v){
			if(isset($_GET[$v])){
				$arr_filter[$v]=(int)$_GET[$v];
			}
			else{
				$arr_filter[$v]='999';
			
			}

		
		}
	$arr_where=array(
	'type'=>$type,
	'slot'=>$slot,
	);

$req_sql='select * from `'.AD_TABLE.'` where';
$req_where='';
if(count($arr_where)>0)
{
	foreach($arr_where as $k => $v){
$req_where.=' and '.'`'.$k.'`'.'='."'".$v."'";

}
}
$req_where=substr($req_where,4,strlen($req_where)-1);
$req_sql=$req_sql.$req_where.' order by `weight` desc  limit '.$arr_filter['limit'];
	$res_list=$this->db->query($req_sql)->result_array();
	$arr_ret=array();
	foreach($res_list as $row){
	$arr_ret[]=$row;

}
	$arr_all=array();
	$arr_all['num']=mysql_affected_rows();
	$arr_all['content']=$arr_ret;
	return $arr_all;
	
	}


	//list  magazines
	function  ad_list_indextopmaga($type,$slot,$limit){
		$query=$this->db->query('select * from `ad_ads` where `type` = \''.$type.'\' and `slot`=\''.$slot.'\' limit '.$limit);
		$arr_title=array();
		foreach($query->result_array() as $row){
			$arr_title[]=$row['title'];
		

		}
		$arr_id=$this->db
			->select('magazine_id')
			->from('magazine')
			->where_in('magazine.name',$arr_title)
			->limit($limit)
			->get()
			->result_array();
		//format
		$ret = array();
		foreach($arr_id as $v){
			array_push($ret,$this->mag_model->_get_magazine($v['magazine_id']));
		
		}
		return $ret;


	
	
	
	}
}
