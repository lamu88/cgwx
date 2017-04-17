<?php
global $_W,$_GPC;
$active='entry';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
//所有分类
$allkind=pdo_fetchall("select * from ".tablename('enjoy_city_kind')." where uniacid=".$uniacid." and parentid=0
    order by hot asc");




//分享
// $sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
// $sharetitle=$act['title']."无所不查，邀您入驻";
// $sharecontent="一个神奇的网站";
//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];


























include $this->template('allkind');