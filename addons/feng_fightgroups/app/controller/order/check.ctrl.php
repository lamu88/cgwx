<?php
$orderno = $_GPC['mid'];
$result = $_GPC['result'];
$order = pdo_fetch("select * from".tablename('tg_order')."where uniacid='{$_W['uniacid']}' and orderno = '{$orderno}'");
$goods = pdo_fetch("select *from".tablename('tg_goods')."where id = '{$order['g_id']}' and uniacid='{$_W['uniacid']}'");
$all_stores = pdo_fetchall("select id from" . tablename('tg_store') . "where uniacid='{$_W['uniacid']}' and status=1");
$is_hexiao_member = FALSE;
$store = array();
$store_ids=array();
if(!empty($goods['hexiao_id'])){
	$store_ids = explode(',',substr($goods['hexiao_id'],0,strlen($goods['hexiao_id'])-1)); 
}else{
	foreach($all_stores as $key=>$value){
		$store_ids[] = $value['id'];
	}
}
$con = '';
if(!empty($goods['merchantid'])){
	$con .=  " and merchantid={$goods['merchantid']}";
}
 //*判断是否是核销人员*/
$hexiao_member = pdo_fetch("select * from".tablename('tg_saler')."where openid='{$_W['openid']}' and  uniacid='{$_W['uniacid']}' and status=1  {$con} ");
if($hexiao_member){
	if($hexiao_member['storeid']==''){
		$store = $store_ids;
		$is_hexiao_member = TRUE;
	}else{
		$hexiao_ids = explode(',', substr($hexiao_member['storeid'],0,strlen($hexiao_member['storeid'])-1)); //核销员属于门店的id
		foreach($hexiao_ids as$key=> $value){
			if(in_array($value,$store_ids)){
				$store[] = $value;
				$is_hexiao_member = TRUE;
			}
		}
	}
	if(!empty($hexiao_member['merchantid']) && !empty($goods['merchantid'])){
		if($hexiao_member['merchantid'] != $goods['merchantid']){
			$is_hexiao_member = FALSE;
		}
	}
}
//门店信息
foreach($store as$key=>$value){
	if($value){
		$stores[$key] =  pdo_fetch("select * from".tablename('tg_store')."where id ='{$value}' and uniacid='{$_W['uniacid']}'");
	}
}

if($_W['isajax']){
	$storeid = $_GPC['storeid'];
	if($order['is_hexiao']==2){
		die(json_encode(array('errno'=>1,'message'=>'该订单已核销！')));
	}else{
		if(pdo_update('tg_order',array('status'=>4,'is_hexiao'=>2,'veropenid' => $_W['openid'],'sendtime'=>TIMESTAMP,'gettime'=>TIMESTAMP,'storeid'=>$storeid),array('orderno'=>$orderno))){
			die(json_encode(array('errno'=>0,'message'=>'核销成功！')));
		}else{
			die(json_encode(array('errno'=>2,'message'=>'核销失败！')));
		}
	}
	
}
include wl_template('order/check');