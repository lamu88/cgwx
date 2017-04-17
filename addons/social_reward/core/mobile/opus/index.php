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
$ACCESS_TOKEN = $this->getAccessToken();
$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$openid}&lang=zh_CN";
load()->func('communication');
$json = ihttp_get($url);
$userInfo = @json_decode($json['content'], true);
if($op=='display'){
	
	$cfg = $this->module['config'];

	$media_id = $_GPC['mediaid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:media_id and uniacid=:uniacid;',array('media_id'=>$media_id,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$enable = false;
	}else{
		$enable = true;
		$reward = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and openid=:openid and uniacid=:uniacid;',array('did'=>$data['id'],'openid'=>$openid,'uniacid'=>$_W['uniacid']));
		if(empty($reward)){
			$award_num = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid']));
			$award_total = pdo_fetchcolumn('SELECT sum(payment) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid']));
			$award_num = intval($award_num);
			$award_total = number_format($award_total,2);
			if($openid == $data['openid']){
				$needpay = fasle;
			}else{
				$needpay = true;
			}
			
		}else{

			$needpay = false;
		}
		$likenum = pdo_fetchcolumn('SELECT sum(liked) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid']));
		$likenum = intval($likenum);
		$unlikenum = pdo_fetchcolumn('SELECT sum(unliked) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid']));
		$unlikenum = intval($unlikenum);
		$scansNum = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid']));
		$scansNum = intval($scansNum);
	}

	include $this->template('opus/index');
}elseif($op=='complete'){
	$cfg = $this->module['config'];
	if(empty($userInfo['subscribe'])){
		$result = array('status'=>0,'result'=>"非法操作！");
		die(json_encode($result));
	}
	$openid = $_W['openid'];
	$mediaid = $_GPC['mediaid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:mediaid and uniacid=:uniacid;',array('mediaid'=>$mediaid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$result = array('status'=>0,'result'=>"该作品已被作者删除");
	}else{
		$record = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid and openid=:openid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid'],'openid'=>$openid));
		if(empty($record)){
			$insert['uniacid'] = $_W['uniacid'];
			$insert['openid'] = $openid;
			$insert['did'] = $data['id'];
			$insert['createtime'] = time();
			pdo_insert('social_reward_reward',$insert);
		}
		$result = array('status'=>1,'result'=>$data['originalurl']);
	}
	die(json_encode($result));
}elseif($op=='like'){
	$mediaid = $_GPC['mediaid'];
	$openid = $_W['openid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:mediaid and uniacid=:uniacid and status=1;',array('mediaid'=>$mediaid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$result = array('status'=>0,'result'=>"该作品已被删除或已被屏蔽");
	}else{
		$record = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid and openid=:openid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid'],'openid'=>$openid));
		if(empty($record)){
			$result = array('status'=>0,'result'=>"您暂时无法评价该作品");
		}else{
			
			pdo_query('UPDATE '.tablename('social_reward_reward').' set liked=1-liked WHERE id='.$record['id'].';');
			pdo_query('UPDATE '.tablename('social_reward_reward').' set unliked=0 WHERE id='.$record['id'].' and liked=1;');
			$result = array('status'=>1);
		}
	}
	die(json_encode($result));
}elseif($op=='unlike'){
	$mediaid = $_GPC['mediaid'];
	$openid = $_W['openid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:mediaid and uniacid=:uniacid and status=1;',array('mediaid'=>$mediaid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$result = array('status'=>0,'result'=>"该作品已被删除或已被屏蔽");
	}else{
		$record = pdo_fetch('SELECT * FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid and openid=:openid;',array('did'=>$data['id'],'uniacid'=>$_W['uniacid'],'openid'=>$openid));
		if(empty($record)){
			$result = array('status'=>0,'result'=>"您暂时无法评价该作品");
		}else{
			
			pdo_query('UPDATE '.tablename('social_reward_reward').' set unliked=1-unliked WHERE id='.$record['id'].';');
			pdo_query('UPDATE '.tablename('social_reward_reward').' set liked=0 WHERE id='.$record['id'].' and unliked=1;');
			$result = array('status'=>1);
		}
	}
	die(json_encode($result));
}