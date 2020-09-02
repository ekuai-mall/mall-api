<?php
include ROOT_PATH . '/plugin/auth-sys/index.php';
include_once ROOT_PATH . '/core/utils.php';

class Info extends Auth {
	public function __construct($config) {
		parent::__construct($config['DB_NAME'], $config['DB_USR'], $config['DB_PWD']);
		$this->cookieValid = $config['COOKIE_VALID'];
	}
	
	public function get($e, $params) {
		$d = $e[0] ? $e[0] : '';
		$empty = Utils::ret(-100002, Utils::ERR_EMPTY_PARAM);
		switch ($d) {
			case 'getAll':
				$ret = $this->query('SELECT * FROM `ekm_sys_info`', []);
				$ret = Utils::ret(0, $ret);
				break;
			
			default:
				$ret = Utils::ret(-100001, 'request denied');
				break;
		}
		return $ret;
	}
}
