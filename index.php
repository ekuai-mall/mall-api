<?php
define('ROOT_PATH', dirname(__FILE__));
include ROOT_PATH.'/config/user.config.php';
include_once ROOT_PATH.'/core/utils.php';
$do = explode("/", $_GET['_']);
switch ($do[0]) {
	case 'auth':
		include ROOT_PATH.'/core/auth.php';
		$auth = new DoAuth($GLOBALS['_CONFIG']);
		$ret = $auth->get(array_slice($do, 1 - count($do)), $_POST);
		break;
	case 'product':
		
		break;
	default:
		$ret = Utils::ret(-100000, 'request denied');
		break;
}
header('Content-Type:application/json');
echo json_encode($ret, JSON_UNESCAPED_UNICODE);