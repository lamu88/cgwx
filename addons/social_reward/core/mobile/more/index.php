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
$pindex = max(1,$_GPC['page']);
$psize = 10;
$total = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE uniacid=:uniacid and status=1;',array('uniacid'=>$_W['uniacid']));
$sql = "SELECT d.*,(SELECT sum(payment) FROM ".tablename('social_reward_reward')." r WHERE r.did=d.id) as reward,(SELECT sum(liked)/count(*) FROM ".tablename('social_reward_reward')." rr WHERE rr.did=d.id) as liked,(SELECT sum(liked) FROM ".tablename('social_reward_reward')." rrr WHERE rrr.did=d.id) as likednum,(SELECT sum(unliked) FROM ".tablename('social_reward_reward')." rrrr WHERE rrrr.did=d.id) as unlikednum,(SELECT count(*) FROM ".tablename('social_reward_reward')." rrrrr WHERE rrrrr.did=d.id) as scantime,(SELECT count(*) FROM ".tablename('social_reward_reward')."rrrrrr WHERE rrrrrr.openid='".$openid."' and rrrrrr.did=d.id) as scaned FROM ".tablename('social_reward_data').' d WHERE uniacid='.$_W['uniacid'].' and status=1';
$order = "";
if($_GPC['order'] == 'time' || empty($_GPC['order'])){
	$order_condition = " ORDER BY createtime desc";
}elseif($_GPC['order']=='reward'){
	$order_condition = " ORDER BY reward desc";
}elseif($_GPC['order']=='liked'){
	$order_condition = " ORDER BY liked desc";
}
$list = pdo_fetchall($sql.$order_condition.' LIMIT '.($pindex-1)*$psize.','.$psize);
$showcount = min($total,($pindex-1)*$psize+$psize);
if($_W['ispost']){
	include $this->template('more/tpl_index');
	die();
}
include $this->template('more/index');