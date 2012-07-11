<?php
class Ad_Model extends mag_db {

	function Ad_Model(){
		parent::__construct();
		$this->load->model('mag_model');
	}

	
	//function to list activity of special user
	function ad_list($type,$slot){
		if($type == 'elem'){
			return $this->ad_list_elem($slot);
		
		}
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
	//list elements
	function ad_list_elem($slot){
		if(isset($_GET['limit'])){
			$limit=(int)$_GET['limit'];
		}
		else{
			$limit=5;	
		}

		$result=$this->db->query('select * from `ad_ads` where `type` = \'elem\' and `slot`=\''.$slot.'\' limit '.$limit)->result_array();
		//echo "<pre>";print_r($result);exit;
		$ret=array();
		foreach($result as $k =>$v){

			$elem_result=$this->db->query('select * from `mag_element` where `mag_element_id` = \''.$v['resource_id'].'\'')->row_array();

			$result[$k]['ret'] = $this->mag_model->element_row2resource($elem_result);
			$result[$k]['ret']['title']=$result[$k]['title'];
			$result[$k]['ret']['text']=$result[$k]['text'];
			$result[$k]['ret']['url']=$result[$k]['url'];
			$result[$k]['ret']['mag_read_url']=$result[$k]['url'];
			array_push($ret,$result[$k]['ret']);
		
		}




	$arr_all=array();
	$arr_all['num']=$limit;
	$arr_all['items']=$ret;
	return $arr_all;
	}

	//list  magazines
	function  ad_list_indextopmaga($type,$slot,$limit){
		$query=$this->db->query('select * from `ad_ads` where `type` = \''.$type.'\' and `slot`=\''.$slot.'\' limit '.$limit);
		$arr_title=array();
		$arr_text=array();
		foreach($query->result_array() as $row){
			$arr_title[]=$row['resource_id'];
			$arr_text[]=$row['text'];
		

		}
		$arr_id=$this->db
			->select('magazine_id')
			->from('magazine')
			->where_in('magazine.magazine_id',$arr_title)
			->limit($limit)
			->get()
			->result_array();
		//format
		$ret = array();

		foreach($arr_id as $k => $v){
			$arr_pu=$this->mag_model->_get_magazine($v['magazine_id']);
			$arr_pu['intro'] = $arr_text[$k];

			array_push($ret,$arr_pu);
		
		}
		
		return $ret;
	}
}
