<?php
global $_W,$_GPC;
$uniacid=$_W['uniacid'];
$fid=intval($_GPC['fid']);
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
//查询图片
$fimg=pdo_fetchall("select * from ".tablename('enjoy_city_fimg')." where fid=".$fid." and uniacid=".$uniacid."");
for($i=1;$i<=9;$i++){
   
    $loadimg[$i]=$fimg[$i-1];
    $loadimg[$i]['num']=$i;
}
// var_dump($loadimg);
// var_dump($fimg);
// exit();



//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];















include $this->template('loadimg');