<?php
class Ads_Model extends mag_db {

	function Ads_Model(){
		 parent::__construct();
	}

    function _get_ads_links($items){    //获取广告内容{{{
		if (isset($items['position'])){
			$where = array('position' => $items['position']);
		}else{
			$where = array();
		}
        $return = $this->mag_db->rows(AD_TABLE, $where, $items['limit'], $items['start']);
        if ($return == array()) $return = null;
        return $return;
    }   //}}}

    function ad($ad_id){
		$where = array('ad_id' => $ad_id);
		$item = $this->mag_db->row(AD_TABLE, $where);
		return $item;
	}

	function ads($type, $slot, $start=0, $limit=10){
		$where = array('type' => $type, 'position' => $slot);
		$result = $this->mag_db->rows(AD_TABLE, $where, $limit, $start);
		foreach($result as $k => $v){
			$item[] = array(
				'kind' => 'magazine#ads',
				'id' => $v['ad_id'],
				'type' => $v['type'],
				'weight' => $v['weight'],
				'url' => $v['url'],
				'title' => $v['title'],
				$type => $type == 'image' ? array('size' => $v['image_size'], 'url' => $v['image_url']) : $v['text_detail']
			);
		}
		$res = array(
			'kind' => 'magazine#ads',
			'totalResult' => sizeof($result),
			'start' => $start,
			'item' => $item,
		);
		return $res;
	}
}
