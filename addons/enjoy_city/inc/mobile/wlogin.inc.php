<?php
global $_W,$_GPC;
$active='user';
$user_agent  = $_SERVER['HTTP_USER_AGENT'];
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
if (strpos($user_agent, 'MicroMessenger') === false) {
    $this->newmessage("请从微信客户端登录，谢谢",$this->createMobileUrl('login'));
//     die("请从微信客户端登录，谢谢");
}else{
    //微信登录
    $userlist=$this->auth();
    //如果存在则登录成功
    if(!empty($userlist)){
        session_start();
        $username=empty($userlist['username'])?$userlist['nickname']:$userlist['username'];
        $_SESSION['city']['username']=$username;
        $_SESSION['city']['openid']=$userlist['openid'];
        $_SESSION['city']['uid']=$userlist['uid'];
//         var_dump($_SESSION['city']);
//         exit();
        //跳转个人中心
        header("location:".$this->createMobileUrl('personal',array('uid'=>$userlist['uid']))."");
        
        
    }else {
        $this->newmessage('非法登录',$this->createMobileUrl('login'));
//         message('非法登录');
        exit();
    }
}






//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];






















include $this->template('wlogin');