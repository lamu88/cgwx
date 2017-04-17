<?php 
/**
 * [weliam] Copyright (c) 2016/3/26
 * 商城系统设置控制器
 */
defined('IN_IA') or exit('Access Denied');
$ops = array('copyright');
$op_names = array('系统设置');
foreach($ops as$key=>$value){
	permissions('do', 'ac', 'op', 'store', 'setting', $ops[$key], '订单', '参数设置', $op_names[$key]);
}
$op = in_array($op, $ops) ? $op : 'copyright';
wl_load()->model('setting');
if(!TG_ID){
if ($op == 'copyright') {
	$_W['page']['title'] = '商城信息设置 - 系统管理';
	$set = setting_get_list();
	if(empty($set)){
		$settings = $this->module['config'];
	}else{
		$settings = array();
		foreach($set as$key=>$value){
			$settingarray= $value['value'];
			foreach($settingarray as $k=>$v){
				$settings[$k] = $v;
			}
		}
	}
	$styles = array();
	$webstyles = array();
	$dir = TG_APP . "view/";
	$webdir = TG_WEB . "view/";
	if ($handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != ".." && $file != ".") {
				if (is_dir($dir . "/" . $file)) {
					$styles[] = $file;
				} 
			} 
		} 
		closedir($handle);
	}
	if ($handle = opendir($webdir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != ".." && $file != ".") {
				if (is_dir($webdir . "/" . $file)) {
					$webstyles[] = $file;
				} 
			} 
		} 
		closedir($handle);
	}
	
//	wl_debug($settings);
	if (checksubmit('submit')) {
		if($_GPC['secretkey']){
			$secretkey = $_GPC['secretkey'];
			 $resp = ihttp_post('../addons/weliam_manage/api/grant.api.php', array(
		        'type' => 'grant',
		        'type_identifying' => 'feng_fightgroups',
		        'secretkey'=>$secretkey
		    ));
			
		    $ret = @json_decode($resp['content'], true);
			message($ret['message']);
		}else{
			load()->func('file');
	        $r = mkdirs(IA_ROOT . '/attachment/feng_fightgroups/cert/'. $_W['uniacid']);
			$r2 = mkdirs(TG_CERT.$_W['uniacid']);
			if(!empty($_GPC['cert'])) {
	            $ret = file_put_contents(IA_ROOT . '/attachment/feng_fightgroups/cert/'.$_W['uniacid'].'/apiclient_cert.pem', trim($_GPC['cert']));
	            $ret2 = file_put_contents(TG_CERT.$_W['uniacid'].'/apiclient_cert.pem', trim($_GPC['cert']));
	            $r = $r && $ret;
	        }
	        if(!empty($_GPC['key'])) {
	            $ret = file_put_contents(IA_ROOT . '/attachment/feng_fightgroups/cert/'.$_W['uniacid'].'/apiclient_key.pem', trim($_GPC['key']));
	            $ret2 = file_put_contents(TG_CERT.$_W['uniacid'].'/apiclient_key.pem', trim($_GPC['key']));
	            $r = $r && $ret;
	        }
			if(!$r) {
	            message('证书保存失败, 请保证该目录可写');
	        }
			$base = array(
				'guanzhu'=>$_GPC['guanzhu'],
				'guanzhu_buy'=>$_GPC['guanzhu_buy'],
				'order_alert'=>$_GPC['order_alert'],
				'goodstip'=>$_GPC['goodstip'],
				'sharestatus' => $_GPC['sharestatus'],
				'share_type'=>$_GPC['share_type'],
				'received_time'=>$_GPC['received_time'],
				'cancle_time'=>$_GPC['cancle_time']
			);
			$share = array(
				'share_title' => $_GPC['share_title'],
	            'share_image' => $_GPC['share_image'],
	            'share_desc' => $_GPC['share_desc']
			);
			$refund = array(
				'mchid' => $_GPC['mchid'],
				'apikey' => $_GPC['apikey'],
				'auto_refund'=>$_GPC['auto_refund']
			);
			$message = array(
				'm_daipay'=>$_GPC['m_daipay'],
				'm_pay'=>$_GPC['m_pay'],
	            'm_tuan'=>$_GPC['m_tuan'],
	            'm_cancle'=>$_GPC['m_cancle'],
	            'm_ref'=>$_GPC['m_ref'],
	            'm_send'=>$_GPC['m_send'],
	            'm_activity'=>$_GPC['m_activity'],
	            'm_activity_result'=>$_GPC['m_activity_result'],
	            'm_activity_lottery' =>$_GPC['m_activity_lottery']
			);
			$tginfo = array(
				'sname'=>$_GPC['sname'],
	            'slogo'=>$_GPC['slogo'],
	            'copyright'=>$_GPC['copyright'],
	            'guanzhu'=>$_GPC['guanzhu'],
	            'qrcode'=>$_GPC['qrcode'],
	            'followed_image'=>$_GPC['followed_image'],
	            'content' => htmlspecialchars_decode($_GPC['content'])
			);
			$tip = array(
				'tag4'=>$_GPC['tag4'],
	            'tag3'=>$_GPC['tag3'],
	            'tag2'=>$_GPC['tag2'],
	            'tag1'=>$_GPC['tag1']
			);
			$style = array(
				'appview'=>$_GPC['appview'],
				'webview'=>$_GPC['webview']
			);
			$paytype = array(
				'wechatstatus'=>intval($_GPC['wechatstatus']),
				'deliverystatus'=>intval($_GPC['deliverystatus'])
			);
			
			tgsetting_save($base, 'base');
			tgsetting_save($share, 'share');
			tgsetting_save($refund, 'refund');
			tgsetting_save($message, 'message');
			tgsetting_save($tginfo, 'tginfo');
			tgsetting_save($tip, 'tip');
			tgsetting_save($style, 'style');
			tgsetting_save($paytype, 'paytype');
			
			message('更新设置成功！', web_url('store/setting/copyright'));
		}
	}
}
include wl_template('store/setting');
}else{
		$id = MERCHANTID;
		$_W['uniacid'] = UNIACID;
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('tg_merchant').' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
			$params = array(':id'=>$id, ':uniacid'=>$_W['uniacid']);
			$merchant = pdo_fetch($sql, $params);
			$saler = pdo_fetch("select * from" . tablename('tg_member') . "where uniacid={$_W['uniacid']} and openid='{$merchant['openid']}'");
			$messagesaler = pdo_fetch("select * from" . tablename('tg_member') . "where uniacid={$_W['uniacid']} and openid='{$merchant['messageopenid']}'");
		}
		if(checksubmit()){
			$merchant = $_GPC['merchant'];
			pdo_update('tg_merchant',$merchant,array('id'=>$_GPC['id']));
			message("修改成功!",'','success');
		}
include wl_template('store/merchant_home');
}

