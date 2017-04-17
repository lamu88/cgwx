<?php 
/**
 * [weliam] Copyright (c) 2016/4/4
 * 阶梯团
 */

defined('IN_IA') or exit('Access Denied');

$ops = array('list', 'create', 'edit', 'ajax');
$op_names = array('阶梯团列表','新建阶梯团', '编辑阶梯团', '选择商品');
//foreach($ops as$key=>$value){
//	permissions('do', 'ac', 'op', 'application', 'gift', $ops[$key], '应用与营销', '阶梯团', $op_names[$key]);
//}
$op = in_array($op, $ops) ? $op : 'list';
wl_load()->model('goods');
if ($op == 'list') {
	$_W['page']['title'] = '应用和营销  - 赠品列表';
	$data  =   pdo_fetchall("select * from".tablename('tg_gift')."where uniacid={$_W['uniacid']}");
	foreach($data  as$key=>&$value){
		$value['goods'] = pdo_fetch("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']} and id={$value['goodsid']}");
		if($value['starttime']>TIMESTAMP){
			$value['status'] = '未开始';
		}elseif($value['endtime']<TIMESTAMP){
			$value['status'] = '已结束';
		}else{
			$value['status'] = '进行中';
		}
	}
	include wl_template('application/gift/gift_list');
}
if ($op == 'create' || $op == 'edit') {
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}
	$id = $_GPC['id'];
	$data =   pdo_fetchall("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']}");
	if($id){
		$gift =   pdo_fetch("select * from".tablename('tg_gift')."where uniacid={$_W['uniacid']} and id={$id}");
		$gift['goods'] = pdo_fetch("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']} and id={$gift['goodsid']}");
	}
	if (checksubmit('submit')) {
		$time = $_GPC['time'];
		$goods = $_GPC['goods'];
		$goods['uniacid'] = $_W['uniacid'];
		$goods['starttime'] = strtotime($time['start']);
		$goods['endtime'] = strtotime($time['end']);
		if (empty($id)) {
			if(pdo_insert('tg_gift',$goods))
				message('创建成功', web_url('application/gift/list'), 'success');exit;
			
		} else {
			pdo_update('tg_gift',$goods);
			message('修改成功', web_url('application/gift/list'), 'success');exit;
		}
	}
	
	include wl_template('application/gift/gift_edit');
}

if ($op == 'ajax') {
	$id = $_GPC['id'];
	$goods = goods_get_by_params(" id={$id} ");
	die(json_encode($goods));
}
