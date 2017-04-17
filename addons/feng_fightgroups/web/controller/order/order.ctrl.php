<?php
defined('IN_IA') or exit('Access Denied');
wl_load()->model('merchant');
$ops = array('sign','delete','summary','received','detail','output','remark','address','confrimpay','confirmsend','cancelsend','refund');
$op_names = array('更新订单','删除订单','订单概况','订单列表','订单详情','导出','卖家备注','修改收货地址','确认付款','发货','取消发货','退款');
foreach($ops as$key=>$value){
	permissions('do', 'ac', 'op', 'order', 'order', $ops[$key], '订单', '订单管理', $op_names[$key]);
}
$op = in_array($op, $ops) ? $op : 'summary';
$uniacid = $_W['uniacid'];
$merchants = pdo_fetchall("SELECT * FROM " . tablename('tg_merchant') . " WHERE uniacid = '{$_W['uniacid']}' ".TG_ID." ORDER BY id DESC");
$allgoods = pdo_fetchall("select gname,id from".tablename('tg_goods')."where uniacid=:uniacid and isshow=:isshow ".TG_MERCHANTID."",array(':uniacid'=>$_W['uniacid'],':isshow'=>1));

if($op == 'summary' ){
	$seven_orders =  0;
	$obligations =  0;
	$undelivereds =  0;
	$incomes =  0;
	$yesterday_orders =  0;
	$yesterday_payorder =  0;
	$yesterday_obligation = 0;
	$stime = strtotime(date('Y-m-d')) - 6 * 86400;
	$etime = strtotime(date('Y-m-d')) + 86400;
	$ytime = strtotime(date('Y-m-d'));
	$seven_orders = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename('tg_order') . " WHERE uniacid = {$_W['uniacid']} and mobile<>'虚拟'   ".TG_MERCHANTID." and createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $stime, ':endtime' => $etime));
	
	$obligations = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟'  ".TG_MERCHANTID." and status=0 ");
	$undelivereds = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟' ".TG_MERCHANTID." and status=2 ");
	$seven = pdo_fetchall("select pay_price  from" . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟'  ".TG_MERCHANTID."and status in(1,2,3,4,6)  AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $stime, ':endtime' => $etime));
	foreach($seven as$key=>$value){
		$incomes += $value['pay_price'];
	}
	
	$yesterday_orders=pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟'  ".TG_MERCHANTID."  AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $ytime, ':endtime' => $etime));
	$yesterday_payorder = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟' ".TG_MERCHANTID." and status in(1,2,3,4,6,7)   AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $ytime, ':endtime' => $etime));
	$yesterday_obligation = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename('tg_order') . " WHERE uniacid = '{$_W['uniacid']}' and mobile<>'虚拟'  ".TG_MERCHANTID."and status = 3   AND sendtime >= :createtime AND sendtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $ytime, ':endtime' => $etime));

	$con =  "uniacid = '{$_W['uniacid']}' and mobile<>'虚拟'  ".TG_MERCHANTID.""  ;
	$starttime = empty($_GPC['time']['start']) ? strtotime(date('Y-m-d')) - 7 * 86400 : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? strtotime(date('Y-m-d'))+86400 : strtotime($_GPC['time']['end'])+86400;
	$s = $starttime;
	$e = $endtime;
	$list = array();
	$j=0;
	
	while($e >= $s){
		$listone = pdo_fetchall("SELECT id  FROM " . tablename('tg_order') . " WHERE $con   AND createtime >= :createtime  AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $e-86400, ':endtime' => $e));
		$status1 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE $con and status<>0   AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $e-86400, ':endtime' => $e));
		$status4 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE $con and status=4   AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array( ':createtime' => $e-86400, ':endtime' => $e));
		$list[$j]['gnum'] = count($listone);
		$list[$j]['status4'] = $status4;
		$list[$j]['status1'] = $status1;
		$list[$j]['createtime'] =  $e-86400;
		$j++;
		$e = $e-86400;
	}
	
	$day = $hit = $status4 = $status1 = array();
	if (!empty($list)) {
		foreach ($list as $row) {
			$day[] = date('m-d', $row['createtime']);
			$hit[] = intval($row['gnum']);
			$status4[] = intval($row['status4']);
			$status1[] = intval($row['status1']);
		}
	}
	
	for ($i = 0; $i = count($hit) < 2; $i++) {
		$day[] = date('m-d', $endtime);
		$hit[] = $day[$i] == date('m-d', $endtime) ? $hit[0] : '0';
	}
	include wl_template('order/summary');
	exit;
}

if ($op == 'received') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = "  uniacid = :uniacid  ".TG_MERCHANTID."";
	$paras = array(':uniacid' => $_W['uniacid']);

	$status = $_GPC['status'];
	$transid = $_GPC['transid'];
	$pay_type = $_GPC['pay_type'];
	$keyword = $_GPC['keyword'];
	$member = $_GPC['member'];
	$time = $_GPC['time'];

	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}
	if (!empty($_GPC['time'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']) ;
		$condition .= " AND  createtime >= :starttime AND  createtime <= :endtime ";
		$paras[':starttime'] = $starttime;
		$paras[':endtime'] = $endtime;
	}
	if(trim($_GPC['goodsid'])!=''){
		$condition .= " and g_id like '%{$_GPC['goodsid']}%' ";
	}
	if(trim($_GPC['goodsid2'])!=''){
		$condition .= " and g_id like '%{$_GPC['goodsid2']}%' ";
	}
	if (!empty($_GPC['merchantid'])) {
		$condition .= " AND  merchantid={$_GPC['merchantid']} ";
	}
	if (!empty($_GPC['transid'])) {
		$condition .= " AND  transid =  '{$_GPC['transid']}'";
	}
	if (!empty($_GPC['pay_type'])) {
		$condition .= " AND  pay_type = '{$_GPC['pay_type']}'";
	} elseif ($_GPC['pay_type'] === '0') {
		$condition .= " AND  pay_type = '{$_GPC['pay_type']}'";
	}
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND  orderno LIKE '%{$_GPC['keyword']}%'";
	}
	if (!empty($_GPC['member'])) {
		$condition .= " AND (addname LIKE '%{$_GPC['member']}%' or mobile LIKE '%{$_GPC['member']}%')";
	}
	if ($status != '') {
		$condition .= " AND  status = '" . intval($status) . "'";
		if($status==4){
			$allnogettime = pdo_fetchall("select * from".tablename('tg_order')."where gettime='' and uniacid='{$_W['uniacid']}' and status=3");
			if(empty($gettime)){
				$gettime = 5;
			}
			$now = time();
			foreach($allnogettime as $key =>$value){
				$shouldgettime = $value['sendtime']+$gettime*24*3600;
				if($shouldgettime<$now){
					pdo_update('tg_order',array('gettime'=>$shouldgettime,'status'=>4),array('id'=>$value['id']));
				}
			}
			
		}
	}
	$sql = "select  * from " . tablename('tg_order') . " where $condition and mobile<>'虚拟' and is_hexiao=0   ORDER BY createtime DESC " . "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql, $paras);
	$paytype = array('0' => array('css' => 'default', 'name' => '未支付'), '1' => array('css' => 'info', 'name' => '余额支付'), '2' => array('css' => 'success', 'name' => '在线支付'), '3' => array('css' => 'warning', 'name' => '货到付款'));
	$orderstatus = array('0' => array('css' => 'default', 'name' => '待付款'), '1' => array('css' => 'info', 'name' => '已付款'), '2' => array('css' => 'warning', 'name' => '待发货'), '3' => array('css' => 'success', 'name' => '已发货'), '4' => array('css' => 'success', 'name' => '已签收'), '5' => array('css' => 'default', 'name' => '已取消'), '6' => array('css' => 'danger', 'name' => '待退款'), '7' => array('css' => 'default', 'name' => '已退款'));
	foreach ($list as $key => $value) {
		$s = $value['status'];
		$list[$key]['statuscss'] = $orderstatus[$value['status']]['css'];
		$list[$key]['status'] = $orderstatus[$value['status']]['name'];
		$list[$key]['css'] = $paytype[$value['pay_type']]['css'];
		if ($value['pay_type'] == 2) {
			if (empty($value['transid'])) {
				$list[$key]['paytype'] = '微信支付';
			} else {
				$list[$key]['paytype'] = '微信支付';
			}
		} else {
			$list[$key]['paytype'] = $paytype[$value['pay_type']]['name'];
		}
		$goodsss = pdo_fetch("select id,gname,gimg,merchantid,unit from" . tablename('tg_goods') . "where id = '{$value['g_id']}'");
		$list[$key]['unit'] = $goodsss['unit'];
		$list[$key]['gid'] = $goodsss['id'];
		$list[$key]['gname'] = $goodsss['gname'];
		$list[$key]['gimg'] = $goodsss['gimg'];
		$list[$key]['merchant'] = pdo_fetch("select name from" . tablename('tg_merchant') . "where id = '{$value['merchantid']}' and uniacid={$_W['uniacid']}");
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tg_order') . " WHERE $condition and mobile<>'虚拟' and is_hexiao=0", $paras);
	$pager = pagination($total, $pindex, $psize);
	
	$all = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and mobile<>'虚拟' and is_hexiao=0 ");
	$status0 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=0 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status1 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=1 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status2 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=2 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status3 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=3 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status4 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=4 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status5 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=5 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status6 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=6 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
	$status7 = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('tg_order') . " WHERE uniacid='{$_W['uniacid']}' and status=7 and mobile<>'虚拟' and is_hexiao=0  ".TG_MERCHANTID."");
} 

if ($op == 'detail') {
	wl_load()->model('activity');
	wl_load()->model('goods');
	load()->model('mc');
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
	$coupon_template = coupon_template($item['couponid']);
	$gift = pdo_fetch("SELECT * FROM " . tablename('tg_gift') . " WHERE id = :id", array(':id' => $item['giftid']));
	$getcoupon = coupon_template($item['getcouponid']);
	if ($item['status'] == 7) {
		$refund_record = pdo_fetch("SELECT * FROM " . tablename('tg_refund_record') . " WHERE orderid = :id", array(':id' => $id));
	}
	if (empty($item)) {
		message("抱歉，订单不存在!", referer(), "error");
	}
	$uid = mc_openid2uid($item['openid']);
	$result = mc_fansinfo($uid, $_W['acid'], $_W['uniacid']);
	$item['fanid'] = $result['fanid'];
	$item['user'] = pdo_fetch("SELECT * FROM " . tablename('tg_address') . " WHERE id = {$item['addressid']}");
	$goods = goods_get_by_params(" id={$item['g_id']} ");
	$item['goods'] = $goods;
	if($item['status']==7){
		$refund = pdo_fetch("select createtime from".tablename('tg_refund_record')."where orderid={$item['id']}");
		$refund_time = $refund['createtime'];
	}
	include wl_template('order/order_detail');
	exit;
} 
if($op=='confrimpay'){
		$id = $_GPC['id'];
		$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
		pdo_update('tg_order', array('status' => 1, 'pay_type' => 2,'ptime'=>TIMESTAMP), array('id' => $id));
		$oplogdata = serialize($item);
		oplog('admin', "后台确认付款", web_url('order/order/confrimpay'), $oplogdata);
		message('确认订单付款操作成功！', referer(), 'success');
}
if($op=='confirmsend'){
	$id = $_GPC['id'];
	$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
	$r=pdo_update('tg_order', array('status' => 3, 'express' => $_GPC['express'], 'expresssn' => $_GPC['expresssn'], 'sendtime' => TIMESTAMP), array('id' => $id));
	if($r){
		/*更新可结算金额*/
		if(!empty($item['merchantid'])){$f=merchant_update_no_money($item['price'], $item['merchantid']);}
		/*记录操作*/
		$oplogdata = serialize($item);
		oplog('admin', "后台确认发货", web_url('order/order/confirmsend'), $oplogdata);
		/*发货成功消息提醒*/
		$url = app_url('order/order/detail', array('id' => $item['id']));
		send_success($item['orderno'], $item['openid'], $_GPC['express'], $_GPC['expresssn'], $url);
		message('发货操作成功！', referer(), 'success');
	}else{
		message('发货操作失败！', referer(), 'error');
	}
	
}
if($op=='cancelsend'){
	$id = $_GPC['id'];
	$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
	if($item['issettlement']==1){
		message('取消发货操作失败，该订单金额已结算到指定商家。', referer(), 'error');
	}
	$r=pdo_update('tg_order', array('status' => 2, 'express' => '', 'expresssn' => '', 'sendtime' => ''), array('id' => $id));
	if($r){
		/*更新可结算金额*/
		if(!empty($item['merchantid'])){merchant_update_no_money(0-$item['price'], $item['merchantid']);}
		/*记录操作*/
		$oplogdata = serialize($item);
		oplog('admin', "后台取消发货", web_url('order/order/cancelsend'), $oplogdata);
		message('取消发货操作成功！', referer(), 'success');
	}else{
		message('取消发货操作失败！', referer(), 'error');
	}
	
}
if($op=='refund'){
	$id = $_GPC['id'];
	$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
	$orderno = $item['orderno'];
	$res=refund($orderno,2);
	if($res=='success'){
		$oplogdata = serialize($item);
		oplog('admin', "后台订单详情退款", web_url('order/order/refund'), $oplogdata);
		/*退款成功消息提醒*/
		$url = app_url('goods/list');
		refund_success($item['orderno'],$item['openid'],  $item['price'], $url);
		message('退款成功了！', referer(), 'success');
	} else {
		message('退款失败，服务器正忙，请稍等等！', referer(), 'fail');
	}
}
if($op == 'output'){
	$status = $_GPC['status'];
	$keyword = $_GPC['keyword'];
	$member = $_GPC['member'];
	$time = $_GPC['time'];
	$transid = $_GPC['transid'];
	$paytype = $_GPC['pay_type'];
	
	$starttime = strtotime($_GPC['time']['start']);
	$endtime = strtotime($_GPC['time']['end']);
	$condition = " uniacid='{$_W['uniacid']}' and is_hexiao = 0  ".TG_MERCHANTID."";
	if(trim($_GPC['goodsid'])!=''){
		$condition .= " and g_id like '%{$_GPC['goodsid']}%' ";
	}
	if(trim($_GPC['goodsid2'])!=''){
		$condition .= " and g_id like '%{$_GPC['goodsid2']}%' ";
	}
	if (!empty($_GPC['merchantid'])) {
		$condition .= " AND  merchantid={$_GPC['merchantid']} ";
	}
	if ($status != '') {
		$condition .= " AND status= '{$status}' ";
	}
	if ($keyword != '') {
		$condition .= " AND g_id = '{$keyword}'";
	}
	if (!empty($member)) {
		$condition .= " AND (addname LIKE '%{$member}%' or mobile LIKE '%{$member}%')";
	}
	if (!empty($time)) {
		$condition .= " AND  createtime >= $starttime AND  createtime <= $endtime  ";
	}
	if (!empty($transid)) {

		$condition .= " AND  transid =  '{$transid}'";
	}
	if (!empty($paytype)) {
		$condition .= " AND  pay_type = '{$paytype}'";
	}
	$orders = pdo_fetchall("select * from" . tablename('tg_order') . "where $condition and mobile<>'虚拟' ");
	switch($status){
		case NULL: 
		$str = '全部订单_' . time();
		break;
		case 1: 
		$str = '已支付订单_' . time();
		break;
		case 2: 
		$str = '待发货订单' . time();
		break;
		case 3: 
		$str = '已发货订单' . time();
		break;
		case 4:
		$str = '已签收订单' . time();
		break;
		case 5:
		$str = '已取消订单' . time();
		break;
		case 6: 
		$str = '待退款订单' . time();
		break;
		case 7:
		$str = '已退款订单' . time();
		break;
		default:
		$str = '待支付订单' . time();break;
	}
	/* 输入到CSV文件 */
	$html = "\xEF\xBB\xBF";
	/* 输出表头 */
	$filter = array('aa' => '订单编号', 'bb' => '姓名', 'cc' => '电话', 'dd' => '总价(元)', 'ee' => '状态', 'ff' => '下单时间', 'gg' => '商品名称', 'hh' => '收货地址', 'ii' => '微信订单号', 'jj' => '快递单号', 'kk' => '快递名称','ll'=>'地址类型','mm'=>'商品规格','nn'=>'购买数量','oo'=>'客户留言','pp'=>'我的备注');
	foreach ($filter as $key => $title) {
		$html .= $title . "\t,";
	}
	$html .= "\n";
	foreach ($orders as $k => $v) {
		if ($v['status'] == '0') {
			$thisstatus = '未支付';
		}
		if ($v['status'] == '1') {
			$thisstatus = '已支付';
		}
		if ($v['status'] == '2') {
			$thisstatus = '待发货';
		}
		if ($v['status'] == '3') {
			$thisstatus = '已发货';
		}
		if ($v['status'] == '4') {
			$thisstatus = '已签收';
		}
		if ($v['status'] == '5') {
			$thisstatus = '已取消';
		}
		if ($v['status'] == '6') {
			$thisstatus = '待退款';
		}
		if ($v['status'] == '7') {
			$thisstatus = '已退款';
		}
		if ($v['status'] == '') {
			$thisstatus = '全部订单';
		}
		$thistatus = '待发货';
		$goods = pdo_fetch("select * from" . tablename('tg_goods') . "where id = '{$v['g_id']}' and uniacid='{$_W['uniacid']}'");
		$time = date('Y-m-d H:i:s', $v['createtime']);
		if($v['addresstype']==1){
			$addresstype='公司';
		}else{
			$addresstype='家庭';
		}
		if(empty($v['optionname'])){
			$v['optionname'] = '不限';
		}
		$orders[$k]['aa'] = $v['orderno'];
		$orders[$k]['bb'] = $v['addname'];
		$orders[$k]['cc'] = $v['mobile'];
		$orders[$k]['dd'] = $v['price'];
		$orders[$k]['ee'] = $thisstatus;
		$orders[$k]['ff'] = $time;
		$orders[$k]['gg'] = $goods['gname'];
		$orders[$k]['hh'] = $v['address'];
		$orders[$k]['ii'] = $v['transid'];
		$orders[$k]['jj'] = $v['expresssn'];
		$orders[$k]['kk'] = $v['express'];
		$orders[$k]['ll'] = $addresstype;
		$orders[$k]['mm'] = $v['optionname'];
		$orders[$k]['nn'] = $v['gnum'];
		$orders[$k]['oo'] = $v['remark'];
		$orders[$k]['pp'] = $v['adminremark'];
		foreach ($filter as $key => $title) {
			$html .= $orders[$k][$key] . "\t,";
		}
		$html .= "\n";
	}
	/* 输出CSV文件 */
	header("Content-type:text/csv");
	header("Content-Disposition:attachment; filename={$str}.csv");
	echo $html;
	exit();
}
if ($op == 'remark') {
	$orderid = intval($_GPC['id']);
	$remark = $_GPC['remark'];
	if (pdo_update('tg_order', array('adminremark' => $remark),array('id'=>$orderid))) {
		die(json_encode(array('errno'=>0)));
	} else {
		die(json_encode(array('errno'=>1)));
	}
}
if ($op == 'address') {
	$orderid = intval($_GPC['id']);
	$address = $_GPC['address'];
	$realname = $_GPC['realname'];
	$mobile = $_GPC['mobile'];
	$item = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $orderid));
	if (pdo_update('tg_order', array('address' => $address,'addname'=>$realname,'mobile'=>$mobile),array('id'=>$orderid))) {
		$oplogdata = serialize($item);
		oplog('admin', "后台修改地址", web_url('order/order/confrimpay'), $oplogdata);
		die(json_encode(array('errno'=>0)));
	} else {
		die(json_encode(array('errno'=>1)));
	}
}
if($op == 'delete'){
	if(!empty($_GPC['id'])){
	$id = $_GPC['id'];
	$data = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $id));
	$data = serialize($data);
	$result = pdo_delete('tg_order', array('id' => $id));
	if($result){
		oplog('admin',"删除订单",web_url('order/order/delete'), $data);
		die(json_encode(array('errno'=>0)));
		
	} else {
		die(json_encode(array('errno'=>1)));
	}	
	}else {
	$ids = $_GPC['ids'];
	foreach($ids as $key=>$value){
			$data = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id = :id", array(':id' => $value));
			$data = serialize($data);
		
		$result = pdo_delete('tg_order', array('id' => $value));
		if($result){
			oplog('admin',"删除订单",web_url('order/order/delete'), $data);
		}
		} 
		die(json_encode(array('errno'=>0)));
	}
	}	

if($op == 'sign'){
//	$data = pdo_fetchall("SELECT * FROM " . tablename('tg_order') . " WHERE uniacid = {$_W['uniacid']} and status = :status", array(':status' => 0 ));
	wl_load()->model('order');
	$log = $_GPC['log'];
	$all = $_GPC['all'];
	$st = $_GPC['flag'];
	if($log == ''){
		$data = pdo_fetchall("SELECT * FROM " . tablename('tg_order') . " WHERE is_hexiao=0 and uniacid = {$_W['uniacid']} and status = :status", array(':status' => 0 ));
		$data2 = pdo_fetchall("SELECT * FROM " . tablename('tg_order') . " WHERE is_hexiao=0 and uniacid = {$_W['uniacid']} and status = :status", array(':status' => 3 ));
		$all2 = count($data2);
		$all1 = count($data);
		$all = $all1+$all2;
		message('正在为'.$all.'个订单更新,请不要关闭浏览器', web_url('order/order/sign', array('log' => 0,'all'=>$all)), 'success');
	}
	if($st == '' ){
		$st=0;
	}
	if( $st+100>$all ){
		$end = $all;
	}else {
		$end = $st+100;
	}
	
	$sql = "select  * from " . tablename('tg_order') . " where is_hexiao=0 and uniacid = {$_W['uniacid']} and status = 0 LIMIT {$st},{$end}";
	$sql2 = "select  * from " . tablename('tg_order') . " where is_hexiao=0 and uniacid = {$_W['uniacid']} and status = 3 LIMIT {$st},{$end}";
	
	$list = pdo_fetchall($sql);
	foreach($list as $key=>$value){
	//	var_dump($value);exit;	
		$tm=time();	
		if( ($tm - $value['createtime']) > 432000  ){
		$r = pdo_query("UPDATE".tablename('tg_order')."SET status ='5' WHERE orderno= :orderno" , array(':orderno' => $value['orderno'] ));
		}
	}
	$list2 = pdo_fetchall($sql2);
	foreach($list2 as $key=>$value){
	//	var_dump($value);exit;	
		$tm=time();	
		if( ($tm - $value['sendtime']) > 432000  ){
		$r = pdo_query("UPDATE".tablename('tg_order')."SET status ='4' WHERE orderno= :orderno" , array(':orderno' => $value['orderno'] ));
		}
	}	
	$log2 = count($list)+count($list2);
	$log += $log2;
	if($end == $all){
		message('全部更新操作完成,总共'.$all."个订单更新！", web_url('order/order/received'), 'success');
	}else {
		message('正在更新,请不要关闭浏览器,已更新' . $log . ' 个订单,总共'.$all.个订单更新, web_url('order/order/sign', array('log' => $log,'all'=>$all,'flag'=>$end)));
		
	}

}

include wl_template('order/order');
