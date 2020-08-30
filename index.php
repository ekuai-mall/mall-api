<?php
define('ROOT_PATH', dirname(__FILE__));
include ROOT_PATH . '/config/user.config.php';
include_once ROOT_PATH . '/core/utils.php';
$do = explode("/", $_GET['_']);
$config = $GLOBALS['_CONFIG'];
switch ($do[0]) {
	
	case 'auth':
		include ROOT_PATH . '/core/auth.php';
		$auth = new DoAuth($config);
		$ret = $auth->get(array_slice($do, 1 - count($do)), $_POST);
		break;
	case 'pay':
		include ROOT_PATH . '/core/payment.php';
		$order = new Order($config);
		$ret = $order->get(array_slice($do, 1 - count($do)), $_POST);
		break;
	case 'item':
		include ROOT_PATH . '/core/item.php';
		$item = new DoItem($config);
		$ret = $item->get(array_slice($do, 1 - count($do)), $_POST);
		break;
	default:
		$ret = Utils::ret(-100000, 'request denied');
		break;
}
header('Content-Type:application/json');
echo json_encode($ret, JSON_UNESCAPED_UNICODE);