<?php
include ROOT_PATH.'/plugin/auth-sys/index.php';
include_once ROOT_PATH.'/core/utils.php';

class DoAuth extends Auth {
	public function __construct($config) {
		parent::__construct($config['DB_NAME'], $config['DB_USR'], $config['DB_PWD']);
	}
	
	public function get($e, $params) {
		$d = $e[0] ? $e[0] : '';
		$empty = Utils::ret(-100002, Utils::ERR_EMPTY_PARAM);
		switch ($d) {
			case 'login':
				if (Utils::isEmpty($params['user'], $params['pass'])) {
					$ret = $empty;
				} else {
					$ret = parent::login($params['user'], $params['pass']);
				}
				break;
			case 'register':
				if (Utils::isEmpty($params['user'], $params['pass'])) {
					$ret = $empty;
				} else {
					$ret = parent::reg($params['user'], $params['pass']);
				}
				break;
			case 'heartbeat':
				if (Utils::isEmpty($params['cookie'])) {
					$ret = $empty;
				} else {
					$ret = parent::heartbeat($params['cookie']);
				}
				break;
			case 'cPass':
				if (Utils::isEmpty($params['user'], $params['pass'], $params['nPass'])) {
					$ret = $empty;
				} else {
					$ret = parent::changePwd($params['user'], $params['pass'], $params['nPass']);
				}
				break;
			default:
				$ret = Utils::ret(-100001, 'request denied');
				break;
		}
		return $ret;
	}
}