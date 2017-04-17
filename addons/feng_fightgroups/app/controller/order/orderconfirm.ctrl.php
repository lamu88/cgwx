<?php
/**
 * [weliam] Copyright (c) 2016/3/23
 * goods.ctrl
 * 商品详情控制器
 */
defined('IN_IA') or exit('Access Denied');
wl_load()->model('goods');
wl_load()->model('merchant');
wl_load()->model('order');
wl_load()->model('account');
wl_load()->model('activity');
load()->func('communication');

$ops = array('display', 'post');
$op = in_array($op, $ops) ? $op : 'display';

$pagetitle = !empty($config['tginfo']['sname']) ? '订单提交 - '.$config['tginfo']['sname'] : '订单提交';
session_start();
$id = $_SESSION['goodsid'] =  isset($_GPC['id']) ? intval($_GPC['id']) : $_SESSION['goodsid'];
$tuan_id = $_SESSION['tuan_id'] = isset($_GPC['tuan_id']) ? intval($_GPC['tuan_id']) : $_SESSION['tuan_id'];
$groupnum = $_SESSION['groupnum'] = isset($_GPC['groupnum']) ? intval($_GPC['groupnum']) : $_SESSION['groupnum'];
$optionid = $_SESSION['optionid'] = isset($_GPC['optionid']) ? intval($_GPC['optionid']) : $_SESSION['optionid'];
if($_GPC['newtuan']=='newtuan'){
	$tuan_id=0;
}
	//规格及规格项
	$allspecs = pdo_fetchall("select * from " . tablename('tg_spec') . " where goodsid=:id order by displayorder asc", array(':id' => $id));
	foreach ($allspecs as &$s) {
		$s['items'] = pdo_fetchall("select * from " . tablename('tg_spec_item') . " where  `show`=1 and specid=:specid order by displayorder asc", array(":specid" => $s['id']));
	}
	unset($s);
	$options = pdo_fetchall("select id,title,thumb,marketprice,productprice,costprice,specs,stock from " . tablename('tg_goods_option') . " where goodsid=:id order by id asc", array(':id' => $id));
	$specs = array();
	if (count($options) > 0) {
		$specitemids = explode("_", $options[0]['specs'] );
		foreach($specitemids as $itemid){
			foreach($allspecs as $ss){
				$items = $ss['items'];
				foreach($items as $it){
					if($it['id']==$itemid){
						$specs[] = $ss;
						break;
					}
				}
			}
		}
	}
	
/*规格*/
//wl_debug($_SESSION);
if(empty($_GPC['num'])){
	if($_SESSION['num']){
		$_GPC['num'] = $_SESSION['num'];
	}else{
		$_GPC['num'] = 1;
	}
}
$_SESSION['num'] = $num = isset($_GPC['num']) ? intval($_GPC['num']) : $_SESSION['num'];
$addrid = isset($_GPC['addrid']) ? intval($_GPC['addrid']) : 0;
$is_usecard = 0;
/*判断抽奖团*/
$lottery = pdo_fetch("select id from".tablename('tg_lottery')."where uniacid={$_W['uniacid']} and goodsid='{$id}'");
if($lottery){
	$lottery_tuan = pdo_fetch("select * from" . tablename('tg_group') . "where lottery_id = '{$lottery['id']}' and uniacid='{$_W['uniacid']}' and groupstatus=3 ");
}
//查询这个团是否支付成功参加
if($tuan_id && empty($lottery_tuan)){
	$nowtuan = group_get_by_params(" groupnumber = '{$tuan_id}'");
	if($nowtuan['groupstatus'] != 3){
		wl_message("该团已结束，请重新开团");exit;
	}
	$myorder = order_get_by_params(" tuan_id = {$tuan_id} and openid = '{$openid}' and status in(1,2,3,4,6,7) ");
	if(!empty($myorder)){
		$tuan_id = '';
	}
	if($id != $nowtuan['goodsid']){
		$id = $_SESSION['goodsid'] = $nowtuan['goodsid'];
	}
}
//判断商品id
if (!empty($id)) {
	$goods = goods_get_by_params(" id = {$id} ");
}else{
	header("Location: ".app_url('goods/list'));exit;
}

//阶梯团和规格的判断
if($goods['group_level_status']==2){
	$param_level = unserialize($goods['group_level']);
	foreach($param_level as$k=>$v){
		if($groupnum == $v['groupnum']){
			$goods['gprice'] = $v['groupprice'];
			break;
		}
	}		
}elseif($goods['hasoption']==1){
	if (!empty($optionid)) {
		$option = pdo_fetch("select title,productprice,marketprice from" . tablename("tg_goods_option") . " where id=:id limit 1", array(":id" => $optionid));
	} else {
		wl_message('抱歉出错了哦，请返回重试！');
	}
	$goods['gprice'] = $option['marketprice'];
	$goods['oprice'] = $option['productprice'];
	$goods['optionname'] = $option['title'];
}

//判断团长优惠
if($goods['is_discount'] == 1){
	if(empty($goods['firstdiscount'])){
		$goods['firstdiscount'] = 0;
	}
	$firstdiscount = $goods['firstdiscount'];
}
if(!empty($groupnum)){
	if($groupnum > 1){
		$price = $goods['gprice'];
		$is_tuan = 1;
		if(empty($tuan_id)){
			$tuan_first = 1;
		}else{
			$tuan_first = 0;
			$firstdiscount = 0;
		}
	}else{
		$price = $goods['oprice'];
		$is_tuan = 0;
		$tuan_first = 0;
		$firstdiscount=0;
	}
} else {
	wl_message('缺少组团人数，请返回重试！');
}

//判断购买数量
$times = pdo_fetchcolumn("select COUNT(*) from".tablename('tg_order')."where uniacid={$_W['uniacid']} and openid='{$_W['openid']}' and status in(1,2,3,4) and g_id={$id}");
if($groupnum > 1){
	if(!empty($goods['one_limit'])){
		if($num > $goods['one_limit']){
			wl_message('超过单次购买数量');
		}
	}
}else {
	if(!empty($goods['op_one_limit'])){
		if($num > $goods['op_one_limit']){
			wl_message('超过单次购买数量');
		}
	}
}	
if(($times+$num)>$goods['many_limit'] && !empty($goods['many_limit'])){
	wl_message('超过限购数量');
}

/*判断地区邮费*/
$freight=0;
if($addrid){
	$adress_fee = pdo_fetch("select * from ".tablename('tg_address')."where id = '{$addrid}'");
}else{
	$adress_fee = pdo_fetch("select * from ".tablename('tg_address')."where openid = '$openid' and status = 1");
}
if(empty($adress_fee) && $goods['is_hexiao']!=2){
	header("location:".app_url('address/createadd'));
}
$p = mb_substr($adress_fee['province'], 0, 2,'utf-8');
$c = mb_substr($adress_fee['city'], 0, 2,'utf-8');
$d = mb_substr($adress_fee['county'], 0, 2,'utf-8');

$province_fee = pdo_fetch("select first_fee from " . tablename("tg_delivery_price") . " WHERE  province like '%{$p}%' and template_id = {$goods['yunfei_id']}");
$city_fee = pdo_fetch("select first_fee from " . tablename("tg_delivery_price") . " WHERE  province like '%{$p}%' and  city like '%{$c}%'  and template_id = {$goods['yunfei_id']}");
$district_fee = pdo_fetch("select first_fee from " . tablename("tg_delivery_price") . " WHERE  province like '%{$p}%' and  city like '%{$c}%' and district like '%{$d}%' and template_id = {$goods['yunfei_id']} ");
if(!empty($province_fee['first_fee'])){
	$freight = $province_fee['first_fee'];
}
if(!empty($city_fee['first_fee'])){
	$freight = $city_fee['first_fee'];
}
if(!empty($district_fee['first_fee'])){
	$freight = $district_fee['first_fee'];
}

//支付价格
$pay_price = sprintf("%.2f", $price * $num + $freight - $firstdiscount);

//获取可用优惠券
$coupon = coupon_canuse($openid, $pay_price);

if ($_W['isajax']) {
	$typeid = intval($_GPC['typeid']);
	$couponid = intval($_GPC['couponid']);
	$str='';
	$discount_fee = 0.00;
	
	if($typeid == 1 || $typeid==3){
		$is_hexiao = 0;
	}elseif($typeid == 2 || $typeid==4){
		$is_hexiao = 1;
		$chars = '0123456789';
		for ($i = 0; $i < 8; $i++) {
			$str .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		if(empty($_GPC['name'])){wl_json(0,"未填写提货人姓名");}
		if(empty($_GPC['mobile'])){wl_json(0,"未填写提货人电话");}
		$adress_fee['cname'] = $_GPC['name'];
		$adress_fee['tel'] = $_GPC['mobile'];
		$bdeltime = strtotime($_GPC['gettime']);
		$pay_price = $pay_price - $freight;
		$freight = 0;
	}
	
	if($couponid){
		$coutp = coupon_handle($openid, $couponid, $pay_price);
		if(!is_array($coutp)){
			$pay_price = currency_format($pay_price - $coutp);
			$is_usecard = 1;
			$discount_fee = $coutp;
		}else{
			wl_json(0,$coutp['message']);
		}
	}
	/*判断是否为抽奖团*/
	$lottery = pdo_fetch("select id from".tablename('tg_lottery')."where uniacid={$_W['uniacid']} and goodsid={$goods['id']} and status=1");
	if($lottery){
		$lottery_id = $lottery['id'];
		$ordertype = 3;
	}else{
		$lottery_id='';
		$ordertype = 0;
	}
	$data = array(
		'uniacid'     => $_W['uniacid'],
		'gnum'        => $num,
		'openid'      => $openid,
        'ptime'       => '',//支付成功时间
		'orderno'     => date('Ymd').substr(time(), -5).substr(microtime(), 2, 5).sprintf('%02d', rand(0, 99)),
		'pay_price'   => $pay_price,
		'goodsprice'  => $price*$num,
		'freight'     => $freight,
		'first_fee'   => $firstdiscount,
		'status'      => 0,//订单状态：0未支,1支付，2待发货，3已发货，4已签收，5已取消，6待退款，7已退款
		'addressid'   => $adress_fee['id'],
		'addresstype' => $adress_fee['type'],//1公司2家庭
		'addname'     => $adress_fee['cname'],
		'mobile'      => $adress_fee['tel'],
		'address'     => $adress_fee['province'].$adress_fee['city'].$adress_fee['county'].$adress_fee['detailed_address'],
		'g_id'        => $id,
		'tuan_id'     => $tuan_id,
		'is_tuan'     => $is_tuan,
		'tuan_first'  => $tuan_first,
		'starttime'   => TIMESTAMP,
		'remark'      => $_GPC['remark'],
		'endtime'     => $goods['endtime'],
		'is_hexiao'   => $is_hexiao,
		'hexiaoma'    => $str,
		'credits'     => $goods['credits'],
		'optionname'  => $goods['optionname'],
		'optionid'    => $optionid,
		'couponid'    => $couponid,
		'is_usecard'  => $is_usecard,
		'discount_fee'=> $discount_fee,
		'createtime'  => TIMESTAMP,
		'bdeltime'    => $bdeltime,
		'merchantid'  => $goods['merchantid'],
		'giftid'=> $goods['give_gift_id'],
		'getcouponid'=> $goods['give_coupon_id'],
		'ordertype'=>$ordertype
	);
	pdo_insert('tg_order', $data);
	$orderid = pdo_insertid();
	
	if($typeid == 2 || $typeid==4){
		/*二维码*/
		wl_load()->classs('qrcode');
		$createqrcode =  new creat_qrcode();
		$createqrcode->creategroupQrcode($data['orderno']);
	}
	
	if(empty($tuan_id)){
		$groupnumber = $orderid;
		if($data['is_tuan']==1){
			$starttime = time();
			$endtime = $starttime + $goods['endtime']*3600;
			$data2 = array(
				'uniacid'     => $_W['uniacid'],
				'groupnumber' => $groupnumber,
				'groupstatus' => 3,//订单状态,1组团失败，2组团成功，3,组团中
				'goodsid'     => $goods['id'],
				'goodsname'   => $goods['gname'],
				'neednum'     => $groupnum,
				'lacknum'     => $groupnum,
				'starttime'   => $starttime,
				'endtime'     => $endtime,
				'price'       => $price,
				'merchantid'  => $goods['merchantid'],
				'lottery_id'  => $lottery_id
			);
			pdo_insert('tg_group', $data2);
			pdo_update('tg_order',array('tuan_id' => $orderid), array('id' => $orderid));
		}
	}
	
	$status = '0';
	if($goods['first_free']==1 && $is_tuan==1 && empty($tuan_id)){
		pdo_update('tg_group', array('lacknum' => $data2['lacknum'] - 1), array('groupnumber' => $orderid));
		pdo_update('tg_order',array('ptime' => TIMESTAMP,'status'=>1,'first_free'=>1), array('id' => $orderid));
		$status = '2';
	}else{
		$status = '1';
	}
	die(json_encode(array('orderno'=>$data['orderno'],'status'=>$status,'tuan_id'=>$orderid)));
}

include wl_template('order/order_confirm');
