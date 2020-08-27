<?php

class Utils {
	const ERR_EMPTY_PARAM = 'refuse empty params';
	
	static function ret($status, $ret) {
		return ['status' => $status, 'ret' => $ret];
	}
	
	static function isEmpty() {
		$arr = func_get_args();
		foreach ($arr as $arg) {
			if (empty($arg)) {
				return true;
			}
		}
		return false;
	}
}