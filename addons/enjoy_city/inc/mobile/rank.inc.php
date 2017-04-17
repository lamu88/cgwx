<?php
global $_GPC,$_W;
$active='rank';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
//查询日榜 月榜 周邦 年榜
$uniacid=$_W['uniacid'];
$op=empty($_GPC['op'])?'d':trim($_GPC['op']);
if($op=='f'){
    $ranks=pdo_fetchall("select a.*,count(b.fid) as bc from ".tablename('enjoy_city_firm')." as a left join
        ".tablename('enjoy_city_forward')."as b on b.fid=a.id where a.uniacid=".$uniacid." and
        a.ischeck=1 group by b.fid order by a.hot desc limit 20");
    
}else {
    if($op=='d'){
        //日榜
        $stime=strtotime("-1 day");
    }elseif ($op=='w'){
    
        //周
        $stime=strtotime("-1 week");
    
    }elseif ($op=='m'){
        //月榜
        $stime=strtotime("-1 month");
    }elseif ($op=='y'){
        //年榜
        $stime=strtotime("-1 year");
    
    }
    $ranks=pdo_fetchall("select b.*,count(a.fid) as ac from ".tablename('enjoy_city_forward')." as a left join ".tablename('enjoy_city_firm')."
    as b on a.fid=b.id where a.uniacid=".$uniacid." and b.ischeck=1
    and a.createtime>='".$stime."' and a.createtime<='".TIMESTAMP."' group by a.fid order by ac desc limit 20");
    
}

// var_dump($ranks);
// exit();

// $ranks=pdo_fetchall("select * from ".tablename('enjoy_city_firm')." where uniacid=".$uniacid." and ischeck=1 order by
//     forward desc limit 50");

// $ranks=pdo_fetchall("select a.*,count(b.fid) as bc from ".tablename('enjoy_city_firm')." as a left join 
//     ".tablename('enjoy_city_forward')."as b on b.fid=a.id where a.uniacid=".$uniacid."
//     and b.createtime>='".$stime."' and b.createtime<='".TIMESTAMP."' and a.ischeck=1 group by b.fid order by bc desc limit 20");



















//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];















































include $this->template('rank');