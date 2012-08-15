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
		$ad_mode = $this->db->query("select * from `ad_slots` where type = '$type' and `intro` = '$slot'")->row_array();
		$ad_mode = $ad_mode['mode'];
	$arr_where=array(
	'type'=>$type,
	'slot'=>$slot,
	'mode' =>$ad_mode,
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
	//image url process
	if($type == 'image'){
		$res_slot = $this->db->get_where('ad_slots',array('type'=>$type,'intro'=>$slot))->row_array();
		$image_size=$res_slot['size'];
		foreach($arr_all['content'] as $k => $v ){
			$preurl = $arr_all['content'][$k]['image'];
			$arr_all['content'][$k]['image'] = array(
					'size' => $image_size,
					'url'  => $preurl,
					);
		}
	}
	return $arr_all;
	
	}
	//list elements
	function ad_list_elem($slot){
		if(isset($_GET['limit'])){
			$limit=(int)$_GET['limit'];
		}
		else{
			$limit=999;	
		}
		$ad_mode = $this->db->query("select * from `ad_slots` where type = 'elem' and `intro` = '$slot'")->row_array();
		$ad_mode = $ad_mode['mode'];

		$result=$this->db->query("select * from `ad_ads` where `type` = 'elem' and `slot`= '$slot' and `mode` = '$ad_mode' order by `weight` desc limit $limit")->result_array();
		$ret=array();
		foreach($result as $k =>$v){
			$result[$k]['ret'] = $this->mag_model->_get_element($v['resource_id']);
			if(strlen($result[$k]['title'])){
			$result[$k]['ret']['title']=$result[$k]['title'];
			}
			if(strlen($result[$k]['text'])){
			$result[$k]['ret']['text']=$result[$k]['text'];
			}
			$result[$k]['ret']['url']=$result[$k]['url'];
			$result[$k]['ret']['mag_read_url']=$result[$k]['url'];
			@$result[$k]['ret']['image']['180']['url']=$this->config->item('thumb_host')."/thumb?size=".$result[$k]['ret']['image']['original']['width'].'x'.$result[$k]['ret']['image']['original']['height']."&fit=c&src=".$result[$k]['ret']['image']['original']['url'];
			array_push($ret,$result[$k]['ret']);
		
		}




	$arr_all=array();
	$arr_all['kind']='magazine#elements';
	$arr_all['totalResults']=count($ret);
	$arr_all['items']=$ret;
	$arr_all['start']='0';
	return $arr_all;
	}

	//list  magazines
	function  ad_list_indextopmaga($type,$slot,$limit){
		$ad_mode = $this->db->query("select * from `ad_slots` where type = '$type' and `intro` = '$slot'")->row_array();
		$ad_mode = $ad_mode['mode'];
		$query=$this->db->query("select * from `ad_ads` where `type` = '$type' and `slot`= '$slot' and `mode` = '$ad_mode' order by `weight` desc limit $limit");
		$arr_title=array();
		$arr_text=array();
		foreach($query->result_array() as $row){
			$arr_id[]=$row['resource_id'];
			$arr_text[]=$row['text'];
			$arr_title[]=$row['title'];
		

		}
		//format
		$ret = array();
if(count($query->result_array)){
		foreach($arr_id as $k => $v){
			$arr_pu=$this->mag_model->_get_magazine($v);
			if(strlen($arr_title[$k])){
			$arr_pu['name'] = $arr_title[$k];
			}
			if(strlen($arr_text[$k])){
			$arr_pu['intro'] = $arr_text[$k];
			}
			

			array_push($ret,$arr_pu);
		
		}
		
		return $ret;
	}
	}
}
