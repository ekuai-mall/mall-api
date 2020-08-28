<?php
include ROOT_PATH . '/plugin/item-api/index.php';
include_once ROOT_PATH . '/core/utils.php';

class DoItem extends Item {
	private $empty;
	
	public function __construct($config) {
		parent::__construct($config['DB_NAME'], $config['DB_USR'], $config['DB_PWD']);
	}
	
	public function get($e, $params) {
		$this->empty = Utils::ret(-200002, Utils::ERR_EMPTY_PARAM);
		switch ($e[0] ? $e[0] : '') {
			case 'get':
				if (Utils::isEmpty($e[1])) {
					$ret = $this->empty;
				} else {
					$ret = parent::getItem($e[1]);
				}
				break;
			case 'search':
				$ret = parent::searchItem($params['key'] ? $params['key'] : '');
				break;
			default:
				$ret = Utils::ret(-200001, 'request denied');
				break;
		}
		return $ret;
	}
}