<?php
class PicThumb extends CI_Model{

	function pic_thumb ($src, $size) {
		$thumb_host = $this->config->item('thumb_host') . "/thumb?size=" . $size . "&fit=c&src=" . $src;
		return $thumb_host;
	}
}
