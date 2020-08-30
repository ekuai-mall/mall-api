<?php
define('ROOT_PATH', dirname(__FILE__));
include ROOT_PATH . '/config/user.config.php';
$config = $GLOBALS['_CONFIG'];
if (empty($_GET['code'])) {
	header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=" . $config['WX_BIZ'] . "#wechat_redirect");
	exit;
}
include ROOT_PATH . '/core/utils.php';
$mysql = new PDO('mysql:dbname=' . $config['DB_NAME'] . ';host=localhost', $config['DB_USR'], $config['DB_PWD'], array
(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
$ret = Utils::httpRequest('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $config['APP_ID'] . '&secret='
	. $config['APP_SECRET'] . '&code=' . $_GET['code'] . '&grant_type=authorization_code');
$ret = Utils::httpRequest('https://api.weixin.qq.com/sns/userinfo?access_token=' . $ret['access_token'] . '&openid=' . $ret['openid'] . '&lang=zh_CN');
$a = $mysql->prepare('INSERT INTO `wechat` (`openid`, `user`, `phone`) VALUES (?, ?, ?)');
$a = $mysql->prepare('SELECT * FROM `wechat` WHERE `phone` = ?');
$a->execute(array($_GET['state']));
$res = $a->fetchAll();
if (empty($res)) {
	$a = $mysql->prepare('INSERT INTO `wechat` (`openid`, `user`, `phone`) VALUES (?, ?, ?)');
	$a->execute(array($ret['openid'], $ret['nickname'], $_GET['state']));
} else {
	$a = $mysql->prepare('UPDATE `wechat` SET `openid` = ?,`user` = ?, `phone` = ? WHERE `id` = ?');
	$a->execute(array($ret['openid'], $ret['nickname'], $_GET['state'], $res[0]['id']));
}
header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=" . $config['WX_BIZ'] . "#wechat_redirect");