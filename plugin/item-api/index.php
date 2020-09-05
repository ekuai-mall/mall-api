<?php

/**
 * Item-API
 *
 * 一个php商品插件（类）
 * @author kuai
 * @copyright ekuai 2020
 * @version 1.2
 */
class Item {
	const ERR_DB = 'database error';
	const ERR_SVR = 'server error';
	protected $mysql;
	public $error = '';
	
	public function __construct($dbName, $dbUser, $dbPwd) {
		try {
			$this->mysql = new PDO('mysql:dbname=' . $dbName . ';host=localhost;', $dbUser, $dbPwd, array
			(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
			$this->error = '';
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}
	
	protected function query($sql, $para, $fetch = PDO::FETCH_ASSOC) {
		if ($this->error !== '') {
			return false;
		} else {
			$a = $this->mysql->prepare($sql);
			if ($a->execute($para)) {
				return $a->fetchAll($fetch);
			} else {
				return false;
			}
		}
	}
	
	private function ret($status, $ret) {
		return ['status' => $status, 'ret' => $ret];
	}
	
	public function searchItem($key) {
		$ret = $this->query('SELECT * FROM `ekm_item_main` WHERE `name` LIKE ? LIMIT 50', array('%' . $key . '%'));
		return $this->ret(0, $ret);
	}
	
	public function getItem($id) {
		$ret = $this->query('SELECT * FROM `ekm_item_main` WHERE `id` = ?', array($id));
		if (empty($ret)) {
			$ret = $this->ret(200001, 'undefined product');
		} else {
			$retChildren = $this->query('SELECT * FROM `ekm_item_info` WHERE `sort` = ?', array($id));
			$ret = $ret[0];
			$ret['child'] = $retChildren;
			$ret = $this->ret(0, $ret);
		}
		return $ret;
	}
}