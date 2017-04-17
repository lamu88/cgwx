<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
load ()->func ( 'tpl' );
$pindex = max(1,intval($_GPC['page']));
$psize = 10;
//删除无效记录
pdo_query('DELETE FROM '.tablename('social_reward_reward').' WHERE isnull(createtime)');
$condition = " r.uniacid=:uniacid ";
$params['uniacid'] = $_W['uniacid'];
if(!empty($_GPC['nickname'])){
	$condition .= " and (r.nickname like '%".$_GPC['nickname']."%' or d.nickname like '%".$_GPC['nickname']."%')";
}

if(!empty($_GPC['searchtime'])){
	$starttime = strtotime($_GPC['time']['start']);
	$condition .= " and r.createtime>:starttime";
	$params['starttime'] = $starttime;
	$endtime = strtotime($_GPC['time']['end']);
	$condition .= " and r.createtime<:endtime";
	$params['endtime'] = $endtime;
}

$count = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_reward').' r LEFT JOIN '.tablename('social_reward_data').' d ON r.did=d.id WHERE '.$condition.';',$params);

$total_amount = pdo_fetchcolumn('SELECT sum(payment) FROM '.tablename('social_reward_reward').'r LEFT JOIN '.tablename('social_reward_data').' d ON r.did=d.id WHERE '.$condition.';',$params);
$list = pdo_fetchall('SELECT r.*,d.nickname as uploader, d.originalurl as src, d.type as type FROM '.tablename('social_reward_reward').' r LEFT JOIN '.tablename('social_reward_data').' d ON r.did=d.id WHERE '.$condition.' ORDER BY r.createtime desc LIMIT '.($pindex-1)*$psize.','.$psize.';',$params);

$pager = pagination($count,$pindex,$psize);
include $this->template('web/rewardRecord/index');