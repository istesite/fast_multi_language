<?php
function trans($str) {
	global $lang;
	if(isset($lang) and is_object($lang)) {
		return $lang->w($str);
	}
	else {
		return $str;
	}
}