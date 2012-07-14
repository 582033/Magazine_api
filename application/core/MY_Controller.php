<?php

class MY_Controller extends CI_Controller {
	var $pseudo_input = array();

	function MY_Controller() { //{{{
		parent::__construct();
		$enable_profiler = $this->config->item('enable_profiler');
		$this->output->enable_profiler($enable_profiler);
	} //}}}

	function _get_non_empty($name) { //{{{
		// get and ensure not empty
		$value = $this->_get($name, FALSE);
		if ($value == '') show_error("Param missing: $name");
		return $value;
	} //}}}
	function _get($name, $default=FALSE) { //{{{
		// get and return default value if empty
		if (array_key_exists($name, $this->pseudo_input)) {
			$value = $this->pseudo_input[$name];
		}
		else {
			$value = $this->input->get($name);
		}
		if ($value === '' || $value === FALSE) $value = $default;

		return $value;
	} //}}}
	function _check_required($keys) { //{{{
		/**
		 * check required params
		 * @param keys: str or array
		 */
		if (is_string($keys)) {
			$keys = array($keys);
		}
		$missing_keys = array();
		foreach ($keys as $k) {
			$v = $this->input->get($k);
			if ($v === FALSE || $v == '') $missing_keys[] = $k;
		}

		if ($missing_keys) {
			show_error("Param missing: " . implode(', ', $missing_keys));
		}
	} //}}}

	function _config_non_empty($name) { //{{{
		$value = $this->config->item($name);
		if ($value === FALSE) show_error("Config missing: $name");
		return $value;
	} //}}}

	function _set_content_type($output_format) { //{{{
		$format_content_type = array(
			'xml' => 'application/xml',
			'html' => 'text/html',
			'pad' => 'text/html',
			'wml' => 'text/vnd.wap.wml',
			'json' => 'application/json',
			'javascript' => 'application/javascript',
		);

		$content_type = $format_content_type[$output_format];
		$this->output->set_header("Content-type: $content_type;charset=utf-8");
	} //}}}

	/**
	  json or jsonp outptu
	  */
	function _json_output($data) { // {{{
		$callback = $this->_get('callback');
		$output = json_encode($data);
		$format = 'json';
		if ($callback) {
			$format = 'javascript';
			$output = "$callback($output);";
		}
		$this->_set_content_type($format);
		$this->output->set_output($output);
	} //}}}
}
