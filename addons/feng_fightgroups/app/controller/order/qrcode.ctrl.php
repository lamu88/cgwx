<?php
	wl_load()->model('order');
	wl_load()->model('goods');
	wl_load()->model('merchant');
	$id = intval($_GPC['id']);
	if($id){
		$order = order_get_by_id($id);
		$goods = goods_get_by_params(" id = {$order['g_id']} ");
		if($order['merchantid']){
			$merchant = merchant_get_by_id($order['merchantid']);
			$order['merchant_name'] = $merchant['name'];
		}else{
			$order['merchant_name'] = $_W['account']['name'];
		}
		if($order['is_hexiao']==1){
			//门店信息
			$storesids = explode(",", $goods['hexiao_id']);
			foreach($storesids as$key=>$value){
				if($value){
					$stores[$key] =  pdo_fetch("select * from".tablename('tg_store')."where id ='{$value}' and uniacid='{$_W['uniacid']}'");
				}
			}
			$qrcodeurl = urlencode($_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=feng_fightgroups&do=order&ac=check&mid=' . $order['orderno']);
		}
	}
	include wl_template('order/qrcode');
