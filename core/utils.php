<?php

class Utils {
	const ERR_EMPTY_PARAM = 'refuse empty params';
	const ERR_DB = 'database error';
	const ERR_SVR = 'server error';
	const ERR_COOKIE_INVALID = 'cookie invalid';
	
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
	
	static function httpRequest($url = '', $data = '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$res = curl_exec($ch);
		if (!$res) {
			$data['return_code'] = 'FAIL';
			$data['return_msg'] = 'curl出错，错误码: ' . curl_errorno($ch) . '详情: ' . curl_error($ch);
		} else {
			$data = json_decode($res, true);
		}
		curl_close($ch);
		return $data;
	}
}