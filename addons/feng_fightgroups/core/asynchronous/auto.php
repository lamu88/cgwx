<?php
require '../../../../framework/bootstrap.inc.php';
error_reporting(0);
set_time_limit(0);
global $_GPC;
$uniacid = $_GPC['uniacid'];
$received_time = !empty($_GPC['received_time'])?$_GPC['received_time']:5;
$cancle_time = !empty($_GPC['cancle_time'])?$_GPC['cancle_time']:1;
$sql = "select  * from " . tablename('tg_order') . " where is_hexiao=0 and uniacid = {$uniacid} and status = 0 ";
$sql2 = "select  * from " . tablename('tg_order') . " where is_hexiao=0 and uniacid = {$uniacid} and status = 3 ";

$list = pdo_fetchall($sql);
foreach($list as $key=>$value){
	$tm=time();	
	if( ($tm - $value['createtime']) > $cancle_time*60  ){
		$r = pdo_query("UPDATE".tablename('tg_order')."SET status ='5' WHERE orderno= :orderno and uniacid = {$uniacid}" , array(':orderno' => $value['orderno'] ));
	}
}
$list2 = pdo_fetchall($sql2);
foreach($list2 as $key=>$value){
	$tm=time();	
	if( ($tm - $value['sendtime']) > $received_time*24*3600  ){
	$r = pdo_query("UPDATE".tablename('tg_order')."SET status ='4' WHERE orderno= :orderno and uniacid = {$uniacid}" , array(':orderno' => $value['orderno'] ));
	}
}	
die(json_encode($list));
exit('fail');