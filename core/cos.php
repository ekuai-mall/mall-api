<?php
include ROOT_PATH . '/plugin/cos-upload/index.php';
include_once ROOT_PATH . '/core/utils.php';

class DoCos extends CosUp {
	private $empty;
	private $cookieValid;
	
	public function __construct($config) {
		parent::__construct($config['DB_NAME'], $config['DB_USR'], $config['DB_PWD'], $config);
		$this->cookieValid = $config['COOKIE_VALID'];
	}
	
	private function selectCookie($userId, $cookie) {
		return $this->query("SELECT `id`,`user`,`login_time`,`cookie` FROM `ekm_auth_user` WHERE `id` = ? AND `cookie` =
		?", [$userId, $cookie]);
	}
	
	private function doNewProj($params) {
		if (Utils::isEmpty($params['user'], $params['cookie'], $params['order'], $params['proj'])) {
			$ret = $this->empty;
		} else {
			$resUser = $this->selectCookie($params['user'], $params['cookie']);
			if ($resUser === false) {
				$ret = Utils::ret(-410001, Utils::ERR_DB);
			} else if (empty($resUser)) {
				$ret = Utils::ret(-410002, 'invalid cookie');
			} else if ($resUser[0]['login_time'] + $this->cookieValid < time()) {
				$ret = Utils::ret(-410003, 'cookie expired');
			} else {
				$res = $this->query("SELECT * from `ekm_order` WHERE `user` = ? AND `order` = ?", [$params['user'],
					$params['order']]);
				if ($res === false) {
					$ret = Utils::ret(-410004, Utils::ERR_DB);
				} else if (empty($res)) {
					$ret = Utils::ret(-410005, 'order and user not match');
				} else {
					$ret = parent::newProj($params['user'], $params['proj'], str_replace(' ', '+', $resUser[0]['user'] . '-' . $res[0]['name']), $params['order'], $params['remark'] ? $params['remark'] : '/');
				}
			}
		}
		return $ret;
	}
	
	public function get($e, $params) {
		$this->empty = Utils::ret(-400002, Utils::ERR_EMPTY_PARAM);
		switch ($e[0] ? $e[0] : '') {
			case 'newProj':
				$ret = $this->doNewProj($params);
				break;
			default:
				$ret = Utils::ret(-400001, 'request denied');
				break;
		}
		return $ret;
	}
}