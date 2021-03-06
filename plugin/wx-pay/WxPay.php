<?php
require_once __DIR__ . "/lib/WxPay.Api.php";
require_once "WxPay.Config.php";

class WxPayBase {
	public function getPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		$this_page = $_SERVER["REQUEST_URI"];
		if (strpos($this_page, "?") !== false) {
			$this_pages = explode("?", $this_page);
			$this_page = reset($this_pages);
		}
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $this_page;
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $this_page;
		}
		return $pageURL;
	}
	
	public function newTradeNo($attach) {
		return date("YmdHis") . $attach . mt_rand(10000, 65535);
	}
	
	private function ret($status, $ret) {
		return ['status' => $status, 'ret' => $ret];
	}
	
	/**
	 * 生成直接支付url，支付url有效期为2小时,模式二
	 * @param WxPayUnifiedOrder $input
	 */
	protected function GetPayUrl($input) {
		try {
			$config = new WxPayConfig();
			return WxPayApi::unifiedOrder($config, $input);
		} catch (Exception $e) {
			return false;
		}
	}
	
	public function NativeGetPayUrl($body, $attach, $price, $productId) {
		$trade_no = $this->newTradeNo($attach);
		$input = new WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($trade_no);
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($productId);
		$ret = $this->GetPayUrl($input);
		$ret['out_trade_no'] = $trade_no;
		return $ret;
	}
	
	public function JsGetPayParams($body, $attach, $price, $trade_no, $openId) {
		$input = new WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($trade_no);
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$ret = $this->GetPayUrl($input);
		if ($ret['return_code'] !== 'SUCCESS') {
			$ret = $this->ret(340001, $ret['return_msg']);
		} else if ($ret['result_code'] !== 'SUCCESS') {
			$ret = $this->ret(340002, $ret['err_code']);
		} else {
			$jsapi = new WxPayJsApiPay();
			$jsapi->SetAppid($ret["appid"]);
			$timeStamp = time();
			$jsapi->SetTimeStamp("$timeStamp");
			$jsapi->SetNonceStr(WxPayApi::getNonceStr());
			$jsapi->SetPackage("prepay_id=" . $ret['prepay_id']);
			$config = new WxPayConfig();
			$jsapi->SetPaySign($jsapi->MakeSign($config));
			$ret = $this->ret(0, $jsapi->GetValues());
		}
		return $ret;
	}
	
	public function H5GetPayUrl($body, $attach, $price) {
		$trade_no = $this->newTradeNo($attach);
		$input = new WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($trade_no);
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetTrade_type("MWEB");
//		$input->SetScene_info('{"h5_info": {"type":"Wap","wap_url": ' . $this->getPageURL() . ',"wap_name": "商城"}}');
		$ret = $this->GetPayUrl($input);
		$ret['out_trade_no'] = $trade_no;
		return $ret;
	}
	
	public function orderQuery($out_trade_no) {
		try {
			$input = new WxPayOrderQuery();
			$input->SetOut_trade_no($out_trade_no);
			$config = new WxPayConfig();
			return WxPayApi::orderQuery($config, $input);
		} catch (Exception $e) {
			return false;
		}
	}
}