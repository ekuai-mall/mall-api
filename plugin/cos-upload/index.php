<?php

/**
 * Cos-Upload
 *
 * 一个phpCOS上传插件（类）
 * @author kuai
 * @copyright ekuai 2020
 * @version 1.0
 */
class CosUp {
	const ERR_DB = 'database error';
	const ERR_SVR = 'server error';
	protected $mysql;
	public $error = '';
	private $config;
	
	public function __construct($dbName, $dbUser, $dbPwd, $cfg) {
		try {
			$this->mysql = new PDO('mysql:dbname=' . $dbName . ';host=localhost;', $dbUser, $dbPwd, array
			(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
			$this->error = '';
			$this->config = $cfg;
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
	
	private function queryByOrder($order) {
		return $this->query("SELECT * FROM `ekm_cos_proj` WHERE `order` = ?", [$order]);
	}
	
	public function newProj($user, $proj, $name, $order, $remark) {
		$res = $this->query("SELECT * FROM `ekm_cos_proj` WHERE `user` = ? AND `proj` = ?", [$user, $proj]);
		if ($res === false) {
			$ret = $this->ret(410001, self::ERR_DB);
		} else if (!empty($res)) {
			$ret = $this->ret(410002, 'project name used');
		} else {
			$res = $this->query("INSERT INTO `ekm_cos_proj` (`user`, `time`, `status`, `order`, `proj`, `name`, `remark`) VALUES (?, ?, 'INIT', ?, ?, ?, ?);", [$user, time(), $order, $proj, $name, $remark]);
			if ($res === false) {
				$ret = $this->ret(410003, self::ERR_DB);
			} else {
				$res = $this->queryByOrder($order);
				if ($res === false) {
					$ret = $this->ret(410004, self::ERR_DB);
				} else if (empty($res)) {
					$ret = $this->ret(410005, self::ERR_SVR);
				} else {
					$ret = $this->ret(0, $res[0]);
				}
			}
		}
		return $ret;
	}
	
	public function getProj($order) {
		$res = $this->queryByOrder($order);
		if ($res === false) {
			$ret = $this->ret(420001, self::ERR_DB);
		} else if (empty($res)) {
			$ret = $this->ret(420002, 'project not found');
		} else {
			$ret = $this->ret(0, $res[0]);
		}
		return $ret;
	}
	
	public function getAuth($name) {
		include "qcloud-sts-sdk.php";
		$sts = new STS();
		$config = array(
			'url' => 'https://sts.tencentcloudapi.com/',
			'domain' => 'sts.tencentcloudapi.com',
			'proxy' => '',
			'secretId' => $this->config['COS_ID'], // 固定密钥
			'secretKey' => $this->config['COS_KEY'], // 固定密钥
			'bucket' => $this->config['COS_BUCKET'], // 换成你的 bucket
			'region' => $this->config['COS_REGION'], // 换成 bucket 所在园区
			'durationSeconds' => 1800, // 密钥有效期
			'allowPrefix' => $name . '/*',
			'allowActions' => array(
				'name/cos:PutObject',
				'name/cos:PostObject',
			),
		);
		$tempKeys = $sts->getTempKeys($config);
		return $this->ret(0, $tempKeys);
	}
	
	public function updateProj($order, $status) {
		$res = $this->queryByOrder($order);
		if ($res === false) {
			$ret = $this->ret(430001, self::ERR_DB);
		} else if (empty($res)) {
			$ret = $this->ret(430002, 'project not found');
		} else {
			$r1 = $this->query("UPDATE `ekm_cos_proj` SET `status` = ? WHERE `id` = ?;", [$status, $res[0]['id']]);
			$r2 = $this->query("UPDATE `ekm_order` SET `extra` = ? WHERE `order` = ?;", [$status, $order]);
			if (!$r1 || !$r2) {
				$ret = $this->ret(430003, self::ERR_DB);
			} else {
				$ret = $this->ret(0, 'success');
			}
		}
		return $ret;
	}
	
}