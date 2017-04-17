<?php 
/**
 * [weliam] Copyright (c) 2016/4/4
 * 阶梯团
 */

defined('IN_IA') or exit('Access Denied');

$ops = array('list', 'create', 'edit', 'ajax');
$op_names = array('阶梯团列表','新建阶梯团', '编辑阶梯团', '选择商品');
//foreach($ops as$key=>$value){
//	permissions('do', 'ac', 'op', 'application', 'special', $ops[$key], '应用与营销', '阶梯团', $op_names[$key]);
//}
$op = in_array($op, $ops) ? $op : 'list';
wl_load()->model('goods');
if ($op == 'list') {
	$_W['page']['title'] = '应用和营销  - 特殊商品列表';
	$data = pdo_fetchall("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']} and (credits >1 or give_coupon_id<>'' or give_gift_id<>'')");
	include wl_template('application/special/special_list');
}
if ($op == 'create' || $op == 'edit') {
	$id = $_GPC['id'];
	if($id){
		$goods = pdo_fetch("select * from".tablename('tg_goods')."where id={$id}");
	}
	/*优惠券*/
	$sql = "select * from".tablename('tg_coupon_template')." WHERE uniacid = {$_W['uniacid']} ";
	$tg_coupon_templates = pdo_fetchall($sql);
	/*赠品*/
	$time= TIMESTAMP;
	$sql = "select * from".tablename('tg_gift')." WHERE uniacid = {$_W['uniacid']} and starttime<'{$time}' and endtime>'{$time}'";
	$gift = pdo_fetchall($sql);
	$data =   pdo_fetchall("select * from".tablename('tg_goods')."where uniacid={$_W['uniacid']}");
	if (checksubmit('submit')) {
		$special_type = $_GPC['special_type'];
		$goodsid = $_GPC['goodsid'];
		$goods = $_GPC['goods'];
		if($special_type==1){
			$credit=$goods['credits'];$give_coupon_id='';$give_gift_id='';
		}elseif($special_type==2){
			$give_coupon_id = $goods['coupon_id'];$credit='';$give_gift_id='';
		}elseif($special_type==3){
			$give_gift_id = $goods['gift_id'];$credit='';$give_coupon_id='';
		}
		$data = array(
			'give_coupon_id'=>$give_coupon_id,
			'give_gift_id'=>$give_gift_id,
			'credits'=>$credit
		);
		if (empty($id)) {
			if(!empty($goodsid)){
				if(pdo_update('tg_goods',$data, array('id' => $goodsid)))
				message('创建成功', web_url('application/special/list'), 'success');exit;
			}else{
				message('创建失败', web_url('application/special/list'), 'success');exit;
			}
			
		} else {
			pdo_update('tg_goods',$data, array('id' => $id));
			message('修改成功', web_url('application/special/list'), 'success');exit;
		}
	}
	
	include wl_template('application/special/special_edit');
}

if ($op == 'ajax') {
	$id = $_GPC['id'];
	$goods = goods_get_by_params(" id={$id} ");
	die(json_encode($goods));
}
