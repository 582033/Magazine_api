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
}
