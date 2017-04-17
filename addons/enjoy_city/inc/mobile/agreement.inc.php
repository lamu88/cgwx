<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");





//分享
// $sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
// $sharetitle=$act['title']."无所不查，邀您入驻";
// $sharecontent="一个神奇的网站";
//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];
















include $this->template('agreement');