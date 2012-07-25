<?php 
class Msg extends MY_Controller {

	var $apiver;

	function Msg (){	
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('msg_model');
		$this->load->model('check_session_model');
		$this->load->library('session');
		$this->apiver = $this->config->item('api_version');
	}	
	function msg_add($json_msg_ctt){
		$res_add=$this->msg_model->msg_add($json_msg_ctt);
		if($res_add===TRUE)
		{
			//add sucess
			echo "add ok !";
			header('HTTP/1.1 200');
			exit();

		}
		else{
			//add falture
		header('HTTP/1.1 500');
		exit();

}



}

	function msg_list(){	
	
			//check user start
		$user_info=$this->check_signin();
		if($user_info===FALSE){
		header('HTTP/1.1 401');
		exit();
		}
		else{
		$u_id=$user_info['id'];

		}

			//check user end


$key_unread='keys_unread'.$u_id;
//get from db
$num_unread=$this->msg_model->get_unread($u_id);

$key_all='keys_all'.$u_id;
//get from db
$num_all=$this->msg_model->get_msgnum($u_id);


		$filter=array();
		//format and check param verb and status
		foreach(array('verb','status') as $k=>$v){
			//if(!empty($this->input->get($v))){
			if((!is_null(@$_GET[$v]))){
				//check if param was configed value
				$filter[$v]=$_GET[$v];
			}
		}
		//format param start and limit,if it's not set,set default value
		foreach(array('start','limit') as $k => $v){
			if(!is_null(@$_GET[$v])){
				//get form url and format
				$filter[$v]=(int)$_GET[$v];
			}
			else{
				//get from config file
				$filter[$v]=$this->config->item($v);
}
	
	}
		//add u_id into filter array	
		$filter['uid']=$u_id;
	$key_req='list_'.implode('__',$filter);

	//query from db and set into redis
	$arr_list=$this->msg_model->msg_list($filter);
	$res_arr=array();
	$res_arr['kind']="magazine#activities";
	//$res_arr['totalResults']=$arr_list['num'];
	$res_arr['unreadmsgnum']=$num_unread;
	$res_arr['totalResults']=$num_all;
	$res_arr['start']=$filter['start'];
	foreach($arr_list['content'] as $k => $v){
		$arr_list['content'][$k]->id = $v->msg_id;
		$arr_list['content'][$k]->OwnerId = $v->user_id;
		$arr_list['content'][$k]->occurredAt = $v->occur_time;
		$arr_list['content'][$k]->createdAt = $v->create_time;
		unset($arr_list['content'][$k]->msg_id);
		unset($arr_list['content'][$k]->user_id);
		unset($arr_list['content'][$k]->occur_time);
		unset($arr_list['content'][$k]->create_time);
		$arr_list['content'][$k]->actor = json_decode($v->actor);
		$arr_list['content'][$k]->object = json_decode($v->object);
		$arr_list['content'][$k]->message = json_decode($v->msg_content);
		 unset($arr_list['content'][$k]->msg_content);
	}
	$res_arr['items']=$arr_list['content'];
//	foreach($arr_list['content'] as $k => $v){
//	$res=$this->msg_model->msg_up((int)$v->msg_id,$u_id);
//}
	$json_res=json_encode($res_arr);
	header('Content-type: application/json');
	echo $json_res;
	exit();

		
	
	}	
	/*
	 *Get a redis connection
	 */
	function check_signin(){
		$userid=$this->check_session_model->check_session();
		if(!$userid===FALSE){
			return array(
					'id'=>$userid,
				    );
		}
		else{

			return false;
		}



	}
	/*
	 *Delete an activity or change status of it
	 *@param $id int
	 */
	function msg_delput($id){	
		//strtoupper to change delete into DELETE
		$req_method=strtoupper($_SERVER['REQUEST_METHOD']);

		$user_check=$this->check_signin();
		if($user_check==FALSE){
//unsigned user
		header('HTTP/1.1 401');
		exit();
}

		if($req_method=="PUT")
			//update user's activity status from 0 to 1
	{
		$msg_id=(int)$id;
		$user_id=(int)$user_check['id'];
		$res=$this->msg_model->msg_up($msg_id,$user_id);
		if($res==1)
{
//update ok ,return 202
$key_unread='key_unread'.$user_id;
		header('HTTP/1.1 202');
		exit();

}
else{
//update readed activity or other people's activity',return which http status
		echo str_repeat('tt',50);
		header('HTTP/1.1 406');
		exit();


}

	}
		elseif($req_method=="DELETE")
		//delete user's activity 
	{
		$msg_id=(int)$id;
		$user_id=(int)$user_check['id'];
		$res=$this->msg_model->msg_del($msg_id,$user_id);
		if($res==1)
{
//delete ok
$key_unread='key_unread'.$user_id;
header('HTTP/1.1 200');
exit();

}
else{
//delete falture
header('HTTP/1.1 400');
exit();

}

	}
	else{
		//unsupport method
		header('HTTP/1.1 400');
		exit();

	}
	}	


	
}

