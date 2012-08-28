<?php
class pctool_vcode_model {

	function verinfo ($vcode, $ver_code){	//{{{
		header('HTTP/1.1 200');
		if ($vcode == $ver_code) {
			$config_1 = array('status' => 'OK');
			$config_2 = array(
					'pctool' => $this->pctool(),
					'components' => array(
						'aapt' => $this->aapt(),
						),
					);
			$config = array_merge($config_1, $config_2);
		}
		else {
			$config = array('status' => 'NOT_FOUND');
		}
		return $config;
	}	//}}}

	function pctool() {	// pctool_verinfo{{{
		return array(
				'vcode' => 1,
				'vname' => 'pctool',
				'size' => 204000,
				'url' => null,
				'checksum' => "990971958dcebd1ec1bf0c0ec95123d4",
				'released_at' => date("Y-m-d H:i:s"),
				'relnote' => null,
				);
	}	//}}}

	function aapt (){	//aapt_verinfo{{{
		return array(
				'vcode' => 0.2,
				'vname' => 'aapt',
				'size' => 204000,
				'url' => null,
				'checksum' => "430971958dcebd1ec1bf0c0ec95123d4",
				'released_at' => date("Y-m-d H:i:s"),
				'relnote' => null,
				);
	}	//}}}
}
