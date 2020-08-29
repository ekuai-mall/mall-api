<?php
include ROOT_PATH . '/plugin/wx-pay/index.php';
include_once ROOT_PATH . '/core/utils.php';

class Order extends WxPay {
	private $empty;
	private $cookieValid;
	
	public function __construct($config) {
		parent::__construct($config['DB_NAME'], $config['DB_USR'], $config['DB_PWD']);
		$this->cookieValid = $config['COOKIE_VALID'];
	}
	
	private function buy($params) {
		if (Utils::isEmpty($params['user'], $params['product'], $params['cookie'])) {
			$ret = $this->empty;
		} else {
			$resUser = $this->query('SELECT * FROM `ekm_auth_user` WHERE `cookie`=?', [$params['cookie']]);
			if (empty($resUser)) {
				$ret = Utils::ret(-310001, 'invalid cookie');
			} else if ($resUser[0]['login_time'] + $this->cookieValid < time()) {
				$ret = Utils::ret(-310004, 'cookie expired');
			} else {
				$resItem = $this->query('SELECT * FROM `ekm_item_info` WHERE `id`=?', [$params['product']]);
				if (empty($resItem)) {
					$ret = Utils::ret(-310002, 'undefined product');
				} else {
					$resItem = $resItem[0];
					$resProduct = $this->query('SELECT * FROM `ekm_item_main` WHERE `id`=?',
						[$resItem['sort']]);
					if (empty($resProduct)) {
						$ret = Utils::ret(-310003, 'undefined sort');
					} else {
						$resProduct = $resProduct[0];
						$ret = $this->newOrder($resItem['name'] . '-' . $resProduct['name'], $params['user'],
							$params['product'], $resItem['price'], $params['remark'] ? $params['remark'] : '/');
					}
				}
			}
		}
		return $ret;
	}
	
	public function get($e, $params) {
		$this->empty = Utils::ret(-300002, Utils::ERR_EMPTY_PARAM);
		switch ($e[0] ? $e[0] : '') {
			case 'buy':
				$ret = $this->buy($params);
				break;
			case 'checkOrder':
				if (Utils::isEmpty($params['order'])) {
					$ret = $this->empty;
				} else {
					$ret = $this->checkOrder($params['order']);
				}
				break;
			case 'getOrder':
				if (Utils::isEmpty($params['order'])) {
					$ret = $this->empty;
				} else {
					$order = $this->query('SELECT * FROM `ekm_order` WHERE `order` = ?', [$params['order']]);
					if (empty($order)) {
						$ret = Utils::ret(-320001, 'undefined order');
					} else {
						$order = $order[0];
						$resUser = $this->query('SELECT `id`,`user` FROM `ekm_auth_user` WHERE `id`=?', [$order['user']]);
						$resUser = $resUser[0];
						$order['user'] = $resUser;
						$ret = Utils::ret(0, $order);
					}
				}
				break;
			default:
				$ret = Utils::ret(-300001, 'request denied');
				break;
		}
		return $ret;
	}
}