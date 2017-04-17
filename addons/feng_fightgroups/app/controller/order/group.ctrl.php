<?php
/**
 * [weliam] Copyright (c) 2016/3/23
 * goods.ctrl
 * 团详情控制器
 */
defined('IN_IA') or exit('Access Denied');
wl_load()->model('goods');
wl_load()->model('merchant');
wl_load()->model('order');

$tuan_id = intval($_GPC['tuan_id']);
if(!empty($tuan_id)){
    //取得该团所有订单
    $orders = pdo_fetchall("SELECT * FROM " . tablename('tg_order') . " where is_tuan in(1,3) and status in(1,2,3,4,6,7) and tuan_id='{$tuan_id}' and uniacid = {$_W['uniacid']} order by ptime asc ");
// wl_debug($orders);

    foreach($orders as$key =>$value){
    	if($value['address']=='虚拟'){
    		$orders[$key]['avatar'] = $value['openid'];
			$orders[$key]['nickname'] =  $value['addname'];
    	}else{
			$fans = member_get_by_params(" openid = '{$value['openid']}'");
			if (!empty($fans)) {
				$avatar = $fans['avatar'];
				$nickname=$fans['nickname'];
			}
    		$orders[$key]['avatar'] = $avatar;
			$orders[$key]['nickname'] = $nickname;
    	}
    }

    //取团长订单$order
    $order = order_get_by_params(" tuan_id = {$tuan_id} and tuan_first = 1 ");
	$params = pdo_fetchall("SELECT * FROM" . tablename('tg_goods_param') .  "WHERE goodsid = '{$order['g_id']}' ");
   //自己的订单，若没有参团则$myorder为空
    $myorder = order_get_by_params(" openid = '{$_W['openid']}' and tuan_id = {$tuan_id} and status in(1,2,3,4,6,7) ");
  	//团状态
  	$tuaninfo = group_get_by_params(" groupnumber = {$tuan_id} ");
  	$num_arr = array();
  	for($i=0;$i<$tuaninfo['lacknum'];$i++){
  		$num_arr[$i] = $i; 
  	}
  	if (empty($order['g_id'])) {
  		echo "<script>alert('组团信息不存在！');location.href='".app_url('home/index')."';</script>";
  		exit;
  	}else{
  		$goods = goods_get_by_params(" id = {$order['g_id']} ");
		//阶梯团
		if($goods['group_level_status']==2){
			$param_level = unserialize($goods['group_level']);
			for($i=0;$i<count($param_level)-1;$i++){
				for($j=0;$j<count($param_level)-$i-1;$j++){
					if($param_level[$j]['groupnum']<$param_level[$j+1]['groupnum']){
						$temp=$param_level[$j]; 
						$param_level[$j] = $param_level[$j+1];
						$param_level[$j+1]= $temp;
					}
				}
			}
			if($param_level){
				$num= round(((100-count($param_level)*2)/count($param_level)));
			}
			$goods['p'] = $param_level[0]['groupprice'];
		}
		//阶梯团
		//规格及规格项
		$allspecs = pdo_fetchall("select * from " . tablename('tg_spec') . " where goodsid=:id order by displayorder asc", array(':id' => $goods['id']));
		foreach ($allspecs as &$s) {
			$s['items'] = pdo_fetchall("select * from " . tablename('tg_spec_item') . " where  `show`=1 and specid=:specid order by displayorder asc", array(":specid" => $s['id']));
		}
		unset($s);
		$options = pdo_fetchall("select id,title,thumb,marketprice,productprice,costprice,specs,stock from " . tablename('tg_goods_option') . " where goodsid=:id order by id asc", array(':id' => $goods['id']));
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
		//规格及规格项
		if(empty($goods['unit'])){
			$goods['unit'] = '件';
		}
	    $endtime = $tuaninfo['endtime'];
	    $time = time(); /*当前时间*/
	    $lasttime2 = $endtime - $time;//剩余时间（秒数）
	    $lasttime = $goods['endtime'];
  	}
	
	$config['share']['share_title'] = "我参加了".$goods['gname']."拼团，快来加入吧！";
	$config['share']['share_desc'] = "【差".$tuaninfo['lacknum']."人】".$config['share']['share_desc'];
	$config['share']['share_url'] = app_url('order/group', array('tuan_id'=>$tuan_id,'group_type'=>'share'));
	$config['share']['share_image'] = $goods['gimg'];
	$pagetitle = $goods['gname'];
	
	session_start();
	if($tuaninfo['groupstatus']==3){
		$_SESSION['goodsid'] = $goods['id'];
		$_SESSION['tuan_id'] = $tuan_id;
		$_SESSION['groupnum'] = $tuaninfo['neednum'];
	}
  	include wl_template('order/group');
}else{
	echo "<script>alert('参数错误');location.href='".app_url('home/index')."';</script>";
}
