<?php
global $_W,$_GPC;
$uniacid=$_W['uniacid'];
$fid=intval($_GPC['fid']);

//查询图片
$fimg=pdo_fetchall("select * from ".tablename('enjoy_city_fimg')." where fid=".$fid." and uniacid=".$uniacid."");
for($i=1;$i<=9;$i++){
   
    $loadimg[$i]=$fimg[$i-1];
    $loadimg[$i]['num']=$i;
}
// var_dump($loadimg);
// var_dump($fimg);
// exit();



















include $this->template('loadimg');




















