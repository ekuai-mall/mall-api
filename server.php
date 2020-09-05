<?php
define('ROOT_PATH', dirname(__FILE__));
include ROOT_PATH . '/config/user.config.php';
$config = $GLOBALS['_CONFIG'];
$params = explode("|", $_GET['state']);
if (empty($_GET['code']) || count($params) !== 2) {
	header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=" . $config['WX_BIZ'] . "#wechat_redirect");
	exit;
}
if ($params[0] === 'jsapi') {
	header('Location:' . base64_decode($params[1]) . '/' . $_GET['code']);
	exit;
}
include_once ROOT_PATH . '/core/utils.php';
$ret = Utils::httpRequest('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $config['APP_ID'] . '&secret='
	. $config['APP_SECRET'] . '&code=' . $_GET['code'] . '&grant_type=authorization_code');
if ($ret['errcode']) {
	//40029
	echo $ret['errcode'] . '微信服务器错误，请重试';
	exit;
}
$ret = Utils::httpRequest('https://api.weixin.qq.com/sns/userinfo?access_token=' . $ret['access_token'] . '&openid=' . $ret['openid'] . '&lang=zh_CN');
if ($ret['errcode']) {
	//40003
	echo $ret['errcode'] . '微信服务器错误，请重试';
	exit;
}
include ROOT_PATH . '/core/auth.php';
$auth = new DoAuth($config);
$ret = $auth->setWechat($params[0], $params[1], $ret['nickname'], $ret['openid']);
header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=" . $config['WX_BIZ'] . "#wechat_redirect");