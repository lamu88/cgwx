<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$openid = $_W['openid'];
if(empty($openid)){//未关注者 授权登录
	$script = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$_W['account']['key']."&redirect_uri=".urlencode($_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$_GPC['mediaid']))))."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
	$html = "<script> window.location='".$script."'</script>";
	die($html);
}
if($op=='display'){
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:mediaid and uniacid=:uniacid and status=1;',array('mediaid'=>$_GPC['mediaid'],'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$enable = false;
	}else{
		$record = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and openid=:openid and uniacid=:uniacid;',array('did'=>$data['id'],'openid'=>$openid,'uniacid'=>$_W['uniacid']));
		if(empty($record)){
			$enable = false;
		}else{
			if($record['complain'] == 1){
				$complain = true;
			}
			$enable = true;
		}
	}
}elseif($op=='post'){
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:mediaid and uniacid=:uniacid and status=1;',array('mediaid'=>$_GPC['mediaid'],'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$result = array('status'=>0,'result'=>'该作品已被处理');
	}else{
		$record = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and openid=:openid and uniacid=:uniacid;',array('did'=>$data['id'],'openid'=>$openid,'uniacid'=>$_W['uniacid']));
		if(empty($record)){
			$result = array('status'=>0,'result'=>'请通过正常途径来投诉');
		}else{
			$result = array('status'=>1);
			pdo_query('UPDATE '.tablename('social_reward_reward').' SET complain=1, complain_type="'.$_GPC['type'].'", complain_reason="'.$_GPC['reason'].'" WHERE id='.$record['id']);
		}
		
	}
	die(json_encode($result));
}
include $this->template('opus/complain');