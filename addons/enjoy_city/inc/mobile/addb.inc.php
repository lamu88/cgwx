<?php
global $_W,$_GPC;
$uniacid=$_W['uniacid'];
$active='quan';
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
$firm[location_x]=empty($firm[location_x])?0:$firm[location_x];
$firm[location_y]=empty($firm[location_y])?0:$firm[location_y];
session_start();
$uid=$_SESSION['city']['uid'];
$fid=intval($_GPC['fid']);
$user_agent  = $_SERVER['HTTP_USER_AGENT'];
if (strpos($user_agent, 'MicroMessenger') === false) {
   $w_login=0;
}else{
    $w_login=1;
    //微信登录
    $userinfo = mc_oauth_userinfo();
    $ouid=pdo_fetchcolumn("select uid from ".tablename('enjoy_city_fans')." where openid='".$userinfo[openid]."'");
    if($ouid==$uid){
        //说明是可以直接微信支付的
        $w_pay=1;
    }else{
        if($ouid){
            //不可以绑定
            $w_pay=0;
        }else {
            //可以绑定
            $w_pay=0;
            $w_bang=1;
        }
    }
}
if(empty($uid)){
    header("location:".$this->createMobileUrl('login')."");
}
//选择业务员
$sellers=pdo_fetchall("select * from ".tablename('enjoy_city_seller')." where uniacid=".$uniacid."");
if(checksubmit('submit')){

    $data = array(
        'uniacid' => $_W['uniacid'],
        'title'=>$_GPC['title'],
        'stime'=>date('Y-m-d H:i:s',TIMESTAMP),
        'etime'=>date('Y-m-d H:i:s',(TIMESTAMP+365*24*60*60)),
        'province'=>$_GPC['shop']['province'],
        'city'=>$_GPC['shop']['city'],
        'district'=>$_GPC['shop']['country'],
        'address'=>$_GPC['shop']['address'],
        'location_x'=>$_GPC['shop']['lng'],
        'location_y'=>$_GPC['shop']['lat'],
        'intro'=>$_GPC['intro'],
        'tel'=>$_GPC['tel'],
//         'icon'=>$_GPC['icon'],
//         'img'=>$_GPC['img'],
//         'browse'=>$_GPC['browse'],
//         'forward'=>$_GPC['forward'],
        'ischeck'=>$_GPC['ischeck'],
        'ismoney'=>$_GPC['ismoney'],
        'ispay'=>0,
        'paymoney'=>$act['fee'],
        'sid'=>$_GPC['sid'],
        'parentid'=>$_GPC['shop']['cat_id'],
        'childid'=>$_GPC['shop']['child_id'],
        'wei_num'=>$_GPC['wei_num'],
        'wei_sex'=>$_GPC['wei_sex'],
        'wei_name'=>$_GPC['wei_name'],
        'wei_intro'=>$_GPC['wei_intro'],
        'icon'=>$_GPC['icon'],
        'img'=>$_GPC['img'],
        'wei_avatar'=>$_GPC['wei_avatar'],
        'wei_ewm'=>$_GPC['wei_ewm'],
        's_name'=>$_GPC['s_name'],
        'breaks'=>trim($_GPC['breaks']),
        'firmurl'=>trim($_GPC['firmurl']),
        'custom'=>trim($_GPC['custom']),
        'uid'=>$uid
    );
    
    if($fid>0){
       //更新
       pdo_update('enjoy_city_firm', $data,array('id'=>$fid));
       header("location:".$this->createMobileUrl('bdetail')."");
    }else {
        $data['createtime']=TIMESTAMP;
        $res=pdo_insert('enjoy_city_firm', $data);
        $newfid=pdo_insertid();
        $message="有新商户入驻啦，点击审核";
        //插入成功后通知管理员审核
        require_once MB_ROOT . '/controller/weixin.class.php';
        $url=$this->str_murl($this->createMobileUrl("firm",array('fid'=>$newfid)));
        $config = $this->module['config']['api'];
        $now=date('Y-m-d H:i:s',TIMESTAMP);
        //echo $xxquan;
        $template = array(
            'touser'      => $config['admin'],
            'template_id' => $config['mid'],
            'url'         => $url,
            'topcolor'    => '#743a3a',
            'data' 		  => array('first'=>array('value'=>urlencode('有新商户入驻啦'),'color'=>'#007aff'),
                'keyword1'=>array('value'=>urlencode($data['title']),'color'=>'#007aff'),
                'keyword2'=>array('value'=>urlencode($data['s_name']),'color'=>'#007aff'),
                'keyword3'=>array('value'=>urlencode($act['fee'].'元'),'color'=>'#007aff'),
                'keyword4'=>array('value'=>urlencode('未支付'),'color'=>'#007aff'),
                'keyword5'=>array('value'=>urlencode($now),'color'=>'#007aff'),
                'remark'=>array('value'=>urlencode($message),'color'=>'#007aff'),
            )
        );
        $api = $this->module['config']['api'];
        $weixin = new class_weixin($_W['account']['key'],$_W['account']['secret']);
        $weixin->send_template_message(urldecode(json_encode($template)));
        
        
    }
  
    if($res>0){
        if(empty($act['kfewm'])){
            header("location:".$this->createMobileUrl('bdetail')."");
        }else{
            header("location:".$this->createMobileUrl('kfewm')."");
        }
        
//         header("location:".$this->createMobileUrl('bdetail')."");
    }
    
    
}
//分类
$pids=pdo_fetchall("select id,name as text from ".tablename('enjoy_city_kind')." where uniacid=".$uniacid." and parentid=0 and (wurl='' or wurl is null) order by hot asc");

for($i=0;$i<count($pids);$i++){
    $pids[$i]['value']=$pids[$i]['id'];
    $pids[$i]['children']=pdo_fetchall("select id,name as text from ".tablename('enjoy_city_kind')." where uniacid=".$uniacid." and parentid=".$pids[$i]['id']."
        and (wurl='' or wurl is null)");
for($j=0;$j<count($pids[$i]['children']);$j++){
    $pids[$i]['children'][$j]['value']=$pids[$i]['children'][$j]['id'];
}
}



$catdata=json_encode($pids);
//exit();           

//编辑

if($fid>0){
    $firm=pdo_fetch("select * from ".tablename('enjoy_city_firm')." where uniacid=".$uniacid." and
        id=".$fid."");
}




// //分享
// $sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
// $sharetitle=$act['title']."无所不查，邀您入驻";
// $sharecontent="一个神奇的网站";

//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];








include $this->template('addb');