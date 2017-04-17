<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
session_start();
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
//查询session 是否存在
if(!empty($_SESSION['city']['username'])){
    $username=trim($_SESSION['city']['username']);
    $password=trim($_SESSION['city']['password']);
    $uid=$this->islogin($username,$password,$uniacid);
    
    if($uid>0){
        header("location:".$this->createMobileUrl('personal',array('uid'=>$uid))."");
    }else{
        //清除session
        unset($_SESSION['city']);
        $this->newmessage('用户名或密码出错',$this->createMobileUrl('login'));
//         message('用户名或密码出错',$this->createMobileUrl('login'),error);
    }
}
if(!empty($_GPC['login'])){
    //用户名密码登录
    $username=trim($_GPC['username']);
    $password=trim($_GPC['password']);
    $uid=$this->islogin($username,$password,$uniacid);

    if($uid>0){

        header("location:".$this->createMobileUrl('personal',array('uid'=>$uid))."");
    }else{
        //清除session
        unset($_SESSION['city']);
        $this->newmessage('用户名或密码出错',$this->createMobileUrl('login'));
//         message('用户名或密码出错',$this->createMobileUrl('login'),error);
    }
}

//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];






















include $this->template('login');