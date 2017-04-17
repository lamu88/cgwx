<?php
global $_W,$_GPC;
$active='entry';
$pid=intval($_GPC['pid']);
$cid=empty($_GPC['cid'])?0:intval($_GPC['cid']);
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
//小分类
$kinds=pdo_fetchall("select * from ".tablename('enjoy_city_kind')." where parentid=".$pid." and uniacid=".$uniacid."
    and enabled=0 order by hot asc");

//查询小分类里面的商户
if($cid!=0){
    $firms=pdo_fetchall("select id,title,icon,breaks from ".tablename('enjoy_city_firm')." where parentid=".$pid." and childid=".$cid." and 
        uniacid=".$uniacid." and ischeck=1 order by hot asc");
}else{
    $firms=pdo_fetchall("select id,title,icon,breaks from ".tablename('enjoy_city_firm')." where parentid=".$pid." and
        uniacid=".$uniacid." and ischeck=1 order by hot asc limit 30");
}
// var_dump($kinds);
// var_dump($firms);
// exit();

//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];






















include $this->template('kind');