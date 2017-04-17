<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
//检查session是否存在
session_start();

$username=$_SESSION['city']['username'];
$uid=$_SESSION['city']['uid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
if(empty($uid)){
    //清除session
    unset($_SESSION['city']);
    //返回登录页面
    header("location:".$this->createMobileUrl('login')."");
}
$user_agent  = $_SERVER['HTTP_USER_AGENT'];
if (strpos($user_agent, 'MicroMessenger') === false) {
   //查询用户名密码
   $password=$_SESSION['city']['password'];
   $fans=pdo_fetch("select * from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." and (username='".$username."'
       or mobile='".$username."')");
// var_dump($fans['password']==$password);
// exit();
if($fans['password']==$password){
    //登录成功

}else{
    //清除session
    unset($_SESSION['city']);
    //返回登录页面
    header("location:".$this->createMobileUrl('login')."");
}
}else{
    //是微信页面访问就
    $openid=$_SESSION['city']['openid'];
   $fans=pdo_fetch("select * from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." and uid='".$uid."'");
   if(!empty($fans['uid'])){
       //登录成功
   }else{
       //返回登录页面
       header("location:".$this->createMobileUrl('login')."");
   }
}
//登录成功返回个人中心页面
$fans['nickname']=empty($fans['nickname'])?$fans['username']:$fans['nickname'];
$fans['avatar']=empty($fans['avatar'])?tomedia('../addons/enjoy_city/public/images/img.png'):$fans['avatar'];

//粉丝人脉信息
$mycontact=pdo_fetch("select * from ".tablename('enjoy_city_contact')." where uniacid=".$uniacid." and 
    uid=".$uid."");


//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];





























include $this->template('personal');