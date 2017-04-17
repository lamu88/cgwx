<?php
/**
 * [weliam] Copyright (c) 2016/3/23
 * index.ctrl
 * 全部商品列表控制器
 */
defined('IN_IA') or exit('Access Denied');
wl_load()->model('goods');
wl_load()->model('merchant');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if($op =='display'){
	$pagetitle = !empty($config['tginfo']['sname']) ? '所有商品 - '.$config['tginfo']['sname'] : '所有商品';
	$cid = intval($_GPC['gid']);
	$category = pdo_fetchall("SELECT * FROM " . tablename('tg_category') . " WHERE uniacid = '{$_W['uniacid']}' and enabled=1 and parentid=0 ORDER BY parentid ASC, displayorder DESC");
	include wl_template('goods/goods_list');
}
if($op =='ajax'){
//	$page = $_GPC['page'];
//	$pagesize = $_GPC['pagesize'];
//	$cid = intval($_GPC['cid']);
//	$recommand = intval($_GPC['recommand']);
//	$data = goods_get_list(array('usepage'=>1,'ishows'=>'1,3','page'=>$page,'pagesize'=>$pagesize,'cid' => $cid,'isrecommand' => $recommand,'orderby' => 'ORDER BY displayorder DESC'));
//	foreach ($data['list'] as $key => $value) {
//		$params = pdo_fetchall("SELECT * FROM" . tablename('tg_goods_param') .  "WHERE goodsid = '{$value['id']}' limit 0,3 ");
//		$data['list'][$key]['params'] = $params;
//	}
//	$goodses = $data;
//	die(json_encode($goodses));
//	
	$condition = " and uniacid={$_W['uniacid']} and isshow in(1,3)";
	$page = $_GPC['page'];
	$pagesize = $_GPC['pagesize'];
	$cid = intval($_GPC['cid']);
	$recommand = intval($_GPC['recommand']);
	if (!empty($recommand)) {
		$condition .= " and isrecommand=1";
	} 
	if (!empty($cid)) {
		$condition .= "  and category_parentid = '{$cid}'";
	}
	if(!empty($_GPC['keyword'])){
		$condition.= " and gname like '%{$_GPC['keyword']}%' ";
	}
	$sql = "SELECT * FROM " . tablename('tg_goods') . " where 1 {$condition} order by displayorder desc LIMIT " . ($page - 1) * $pagesize . ',' . $pagesize;
	$list = pdo_fetchall($sql);
	foreach($list as $key=>&$value){
		$value['gimg'] = tomedia($value['gimg']);
		$value['a'] = app_url('goods/detail/display',array('id'=>$value['id']));
		$params = pdo_fetchall("SELECT * FROM" . tablename('tg_goods_param') .  "WHERE goodsid = '{$value['id']}' limit 0,3 ");
		$value['params'] = $params;
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('tg_goods')."where 1 $condition ");
	$data = array();
	$data['list'] = $list;
	$data['total'] = $total;
	$goodses = $data;
	die(json_encode($goodses));
}
if($op =='search'){
	$keyword = $_GPC['keyword'];
	include wl_template('goods/goods_list');
}
if($op =='merchant'){
	$id = $_GPC['id'];
	$merchant = merchant_get_by_params("id = {$id}");
	include wl_template('goods/merchant_goods');
}
if($op =='merchant_ajax'){
	$id = $_GPC['id'];//商家id
	$page = $_GPC['page'];
	$pagesize = $_GPC['pagesize'];
	$data = goods_get_list(array('usepage'=>1,'ishows'=>'1,3','page'=>$page,'pagesize'=>$pagesize,'merchantid' => $id,'orderby' => 'ORDER BY displayorder DESC'));
	foreach ($data['list'] as $key => $value) {
		$params = pdo_fetchall("SELECT * FROM" . tablename('tg_goods_param') .  "WHERE goodsid = '{$value['id']}' limit 0,3 ");
		$data['list'][$key]['params'] = $params;
	}
	$goodses = $data;
	die(json_encode($goodses));
}