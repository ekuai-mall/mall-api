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
		return $this->query("SELECT `id`,`user`,`login_time`,`cookie`,`wechat` FROM `ekm_auth_user` WHERE `id` = ? AND `cookie` =
		?", [$userId, $cookie]);
	}
	
	private function checkUser($user, $cookie) {
		$resUser = $this->selectCookie($user, $cookie);
		if ($resUser === false) {
			$ret = Utils::ret(-400003, Utils::ERR_DB);
		} else if (empty($resUser)) {
			$ret = Utils::ret(-400004, 'invalid cookie');
		} else if ($resUser[0]['login_time'] + $this->cookieValid < time()) {
			$ret = Utils::ret(-400005, 'cookie expired');
		} else if (empty($resUser[0]['wechat'])) {
			$ret = Utils::ret(-400006, 'wechat error');
		} else {
			$ret = Utils::ret(0, $resUser[0]);
		}
		return $ret;
	}
	
	private function doNewProj($params) {
		if (Utils::isEmpty($params['user'], $params['cookie'], $params['order'], $params['proj'])) {
			$ret = $this->empty;
		} else {
			$resUser = $this->checkUser($params['user'], $params['cookie']);
			if ($resUser['status'] !== 0) {
				$ret = $resUser;
			} else {
				$res = $this->query("SELECT * from `ekm_order` WHERE `user` = ? AND `order` = ?", [$params['user'],
					$params['order']]);
				if ($res === false) {
					$ret = Utils::ret(-410001, Utils::ERR_DB);
				} else if (empty($res)) {
					$ret = Utils::ret(-410002, 'order and user not match');
				} else {
					$ret = parent::newProj($params['user'], $params['proj'], str_replace(' ', '+', $resUser['ret']['user'] . '-' . $res[0]['name']), $params['order'], $params['remark'] ? $params['remark'] : '/');
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
			case 'getProj':
				if (Utils::isEmpty($params['user'], $params['cookie'], $params['order'])) {
					$ret = $this->empty;
				} else {
					$resUser = $this->checkUser($params['user'], $params['cookie']);
					if ($resUser['status'] !== 0) {
						$ret = $resUser;
					} else {
						$res = $this->query("SELECT * from `ekm_cos_proj` WHERE `user` = ? AND `order` = ?", [$params['user'], $params['order']]);
						if ($res === false) {
							$ret = Utils::ret(-420001, Utils::ERR_DB);
						} else {
							$ret = Utils::ret(0, $res[0] ? $res[0] : null);
						}
					}
				}
				break;
			case 'getAuth':
				if (Utils::isEmpty($params['user'], $params['cookie'], $params['order'])) {
					$ret = $this->empty;
				} else {
					$resUser = $this->checkUser($params['user'], $params['cookie']);
					if ($resUser['status'] !== 0) {
						$ret = $resUser;
					} else {
						$res = $this->query("SELECT * from `ekm_cos_proj` WHERE `user` = ? AND `order` = ?", [$params['user'], $params['order']]);
						if ($res === false) {
							$ret = Utils::ret(-430001, Utils::ERR_DB);
						} else if (empty($res)) {
							$ret = Utils::ret(-430002, 'project not found');
						} else {
							$ret = $this->getAuth($res[0]['name']);
						}
					}
				}
				break;
			case 'finish':
				if (Utils::isEmpty($params['user'], $params['cookie'], $params['order'])) {
					$ret = $this->empty;
				} else {
					$resUser = $this->checkUser($params['user'], $params['cookie']);
					if ($resUser['status'] !== 0) {
						$ret = $resUser;
					} else {
						$res = $this->query("SELECT * from `ekm_cos_proj` WHERE `user` = ? AND `order` = ?", [$params['user'], $params['order']]);
						if ($res === false) {
							$ret = Utils::ret(-440001, Utils::ERR_DB);
						} else if (empty($res)) {
							$ret = Utils::ret(-440002, 'project not found');
						} else {
							$ret = $this->updateProj($params['order'],'SUBMIT');
						}
					}
				}
				break;
			default:
				$ret = Utils::ret(-400001, 'request denied');
				break;
		}
		return $ret;
	}
}