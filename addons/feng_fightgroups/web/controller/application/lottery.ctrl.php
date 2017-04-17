<?php 
/**
 * [weliam] Copyright (c) 2016/4/4
 * 阶梯团
 */

defined('IN_IA') or exit('Access Denied');

$ops = array('list', 'create', 'edit', 'ajax','prize','initsync','message','delete');
$op_names = array('抽奖团列表','新建抽奖团', '编辑抽奖团', '选择商品');
foreach($ops as$key=>$value){
	permissions('do', 'ac', 'op', 'application', 'lottery', $ops[$key], '应用与营销', '抽奖团', $op_names[$key]);
}
$op = in_array($op, $ops) ? $op : 'list';
wl_load()->model('goods');
if ($op == 'list') {
	$_W['page']['title'] = '应用和营销  - 抽奖团列表';
	$lottery = pdo_fetchall("select * from".tablename('tg_lottery')."where uniacid={$_W['uniacid']} and status=1");
	foreach($lottery as$key=>&$value){
		$value['goods'] = pdo_fetch("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']} and id={$value['goodsid']}");
	}
	include wl_template('application/lottery/lottery_list');
}
if ($op == 'create' || $op == 'edit') {
	$lotteryid = $_GPC['id'];
	$data = goods_get_list(array(''));
	if($lotteryid){
		$lottery = pdo_fetch("select * from".tablename('tg_lottery')."where uniacid={$_W['uniacid']} and id={$lotteryid}");
		$goods = pdo_fetch("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']} and id={$lottery['goodsid']}");
	}
	if (checksubmit('submit')) {
		$goods = $_GPC['goods'];
		$goods['uniacid'] = $_W['uniacid'];
		$goods['status'] = 1;
		if (empty($lotteryid)) {
			pdo_insert('tg_lottery',$goods);
			message('创建成功', web_url('application/lottery/list'), 'success');exit;
		} else {
			pdo_update('tg_lottery',$goods, array('id' => $lotteryid));
			message('更新成功', web_url('application/lottery/list'), 'success');exit;
			
		}
	}
	include wl_template('application/lottery/lottery_edit');
}
if ($op == 'delete') {
	$lotteryid = $_GPC['id'];
	pdo_delete('tg_lottery',array('id' => $lotteryid));
	message('删除成功', web_url('application/lottery/list'), 'success');exit;
}
if ($op == 'ajax') {
	$id = $_GPC['id'];
	$goods = goods_get_by_params(" id={$id} ");
	die(json_encode($goods));
}

if ($op == 'prize') {
		$level = $_GPC['level'];
		$order_id = $_GPC['order_idd'];
		if($level =="one" && $order_id ){
			foreach($order_id as$key =>$value){
				pdo_update('tg_order',array('ordertype'=>4),array('id'=>$value));
			}
			die(json_encode(array('errno'=>0)));
		}elseif ($level =="two" && $order_id ) {
			foreach($order_id as$key =>$value){
				pdo_update('tg_order',array('ordertype'=>5),array('id'=>$value));
			}
			die(json_encode(array('errno'=>0)));
			
		}elseif ($level =="three" && $order_id ) {
			foreach($order_id as$key =>$value){
				pdo_update('tg_order',array('ordertype'=>6),array('id'=>$value));
			}
			die(json_encode(array('errno'=>0)));
			
		}elseif ($level =="join" && $order_id ) {
			foreach($order_id as$key =>$value){
				pdo_update('tg_order',array('ordertype'=>7),array('id'=>$value));
			}
			die(json_encode(array('errno'=>0)));
		}elseif ($level =="no" && $order_id ) {
			foreach($order_id as$key =>$value){
				pdo_update('tg_order',array('ordertype'=>3),array('id'=>$value));
			}
			die(json_encode(array('errno'=>0)));
		}
		
	}

if ($op == 'initsync') {
	$type = $_GPC['type'];
	switch($type){
		case 'first_num' : $status=4;break;
		case 'second_num' : $status=5;break;
		case 'third_num' : $status=6;break;
		case 'forth_num' : $status=7;break;
		default:$status=3;
						  
	}
	$groupnumber = $_GPC['groupnumber'];
	$group = pdo_fetch("select  lottery_id,neednum from " . tablename('tg_group') . " where groupnumber='{$groupnumber}' and uniacid={$_W['uniacid']}");
	$lottery = pdo_fetch("select  * from " . tablename('tg_lottery') . " where id={$group['lottery_id']} and uniacid={$_W['uniacid']}");
	$log = $_GPC['log'];
	$all = $_GPC['all'];
	$success = $_GPC['success'];
	if($log == '') {
		$all = pdo_fetchcolumn("select  COUNT(id) from " . tablename('tg_order') . " where tuan_id='{$groupnumber}' and ordertype='{$status}' and status in(1,2,3,4,6)");
		$all = $lottery[$type]-$all;
		message('正在抽取'.$all.'个中奖订单,请不要关闭浏览器', web_url('application/lottery/initsync', array('log' => 0,'groupnumber'=>$groupnumber,'all'=>$all,'success'=>0,'type'=>$type)), 'success');
	}
	$now_num = $all - $log;
	if($now_num>=33){
		$now_num = 33;
	}
	$list = pdo_fetchall("select  id from " . tablename('tg_order') . " where tuan_id='{$groupnumber}' and ordertype not in(4,5,6,7) and status in(1,2,3,4,6) ORDER BY rand()  LIMIT ".$now_num);
	foreach($list as$key=>$value){
		pdo_update('tg_order',array('ordertype'=>$status),array('id'=>$value['id']));
		$success++;
	}
	
	$log += count($list);
	$level_num = $all - $success;
	if($level_num<=0) {
		message('全部抽奖操作完成,总共抽取了'.$all."个订单", web_url('order/group/group_detail', array('groupnumber' => $groupnumber,'lottery_id'=>$lottery['id'])), 'success');
	} else {
		message('正在抽奖,请不要关闭浏览器,已抽取 ' . $log . ' 个订单', web_url('application/lottery/initsync', array('log' => $log,'all'=>$all,'success'=>$success,'groupnumber'=>$groupnumber,'type'=>$type)));
	}
}
if ($op == 'message') {
	$type = $_GPC['type'];
	$level='';
	switch($type){
		case 'first_num' : $status=4;$level='一等奖';break;
		case 'second_num' : $status=5;$level='二等奖';break;
		case 'third_num' : $status=6;$level='三等奖';break;
		case 'forth_num' : $status=7;$level='参与奖';break;
		default:$status=3;
						  
	}
	$groupnumber = $_GPC['groupnumber'];
	$group = pdo_fetch("select  lottery_id,neednum from " . tablename('tg_group') . " where groupnumber='{$groupnumber}' and uniacid={$_W['uniacid']}");
	$lottery = pdo_fetch("select  * from " . tablename('tg_lottery') . " where id={$group['lottery_id']} and uniacid={$_W['uniacid']}");
	$orders = pdo_fetchall('select id,g_id,openid from'.tablename('tg_order')."where uniacid={$_W['uniacid']} and mobile<>'虚拟' and tuan_id= '{$groupnumber}' and status in(1,2,3,4,6,7) and ordertype = '{$status}' ");
	foreach($orders as $key=>$value){
		$url = app_url('goods/detail', array('id' => $value['g_id']));
		activity_lottery($value['openid'], $level, $lottery['name'], $level,$url);
	}
	message("发送成功!",referer(),'success');
}