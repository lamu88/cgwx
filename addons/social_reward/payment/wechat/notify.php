<?php
ini_set ( "display_errors", "On" );
error_reporting ( E_ALL ^ E_NOTICE );
define ( "IN_MOBILE", true );
$input = file_get_contents ( "php://input" );
if (! empty ( $input ) && empty ( $_GET ["out_trade_no"] )) {
	$obj = simplexml_load_string ( $input, "SimpleXMLElement", LIBXML_NOCDATA );
	$data = json_decode ( json_encode ( $obj ), true );
	if (empty ( $data )) {
		print ("fail") ;
	}
	if ($data ["result_code"] != "SUCCESS" || $data ["return_code"] != "SUCCESS") {
		print ("fail") ;
	}
	$get = $data;
} else {
	$get = $_GET;
}
require "../../../../framework/bootstrap.inc.php";

$strs = explode ( ":", $get ["attach"] );
$_W ["uniacid"] = $_W ["weid"] = intval ( $strs [0] );
$type = intval ( $strs [1] );
$total_fee = $get ['total_fee'] / 100;

$setting = uni_setting ( $_W ["uniacid"], array (
		"payment" 
) );
if (is_array ( $setting ["payment"] )) {
	$wechat = $setting ["payment"] ["wechat"];
	if (! empty ( $wechat )) {
		ksort ( $get );
		$string1 = '';
		foreach ( $get as $k => $v ) {
			if ($v != '' && $k != "sign") {
				$string1 .= "{$k}={$v}&";
			}
		}
		$wechat ["signkey"] = ($wechat ["version"] == 1) ? $wechat ["key"] : $wechat ["signkey"];
		$sign = strtoupper ( md5 ( $string1 . "key={$wechat["signkey"]}" ) );
		if ($sign == $get ["sign"]) {
			if (empty ( $type )) {
				$tid = $get ["out_trade_no"];
				if (strexists ( $tid, "GJ" )) {
					$tids = explode ( "GJ", $tid );
					$tid = $tids [0];
				}
				$sql = "SELECT * FROM " . tablename ( "core_paylog" ) . " WHERE `module`=:module AND `tid`=:tid  limit 1";
				$params = array ();
				$params [":tid"] = $tid;
				$params [":module"] = "social_reward";
				$log = pdo_fetch ( $sql, $params );
				if (! empty ( $log ) && $log ["status"] == '0' && $log ['fee'] == $total_fee) {
					m ( "common" )->paylog ( "corelog: ok" );
					$site = WeUtility::createModuleSite ( $log ["module"] );
					if (! is_error ( $site )) {
						$method = "payResult";
						if (method_exists ( $site, $method )) {
							$ret = array ();
							$ret ["weid"] = $log ["weid"];
							$ret ["uniacid"] = $log ["uniacid"];
							$ret ["result"] = "success";
							$ret ["type"] = $log ["type"];
							$ret ["from"] = "return";
							$ret ["tid"] = $log ["tid"];
							$ret ["user"] = $log ["openid"];
							$ret ["fee"] = $log ["fee"];
							$ret ["tag"] = $log ["tag"];
							$result = $site->$method ( $ret );
							if (is_array ( $result ) && $result ["result"] == "success") {
								$log ["tag"] = iunserializer ( $log ["tag"] );
								$log ["tag"] ["transaction_id"] = $get ["transaction_id"];
								$record = array ();
								$record ["status"] = "1";
								$record ["tag"] = iserializer ( $log ["tag"] );
								pdo_update ( "core_paylog", $record, array (
										"plid" => $log ["plid"] 
								) );
								print ("success") ;
							}
						}
					}
				}
			}
		}
	}
}
print ("fail") ;
?>