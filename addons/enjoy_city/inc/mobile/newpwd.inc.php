<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");

if(!empty($_GPC['register'])){
    session_start();
    $uid=$_SESSION['city']['uid'];
    $oldpass=trim($_GPC['oldpass']);
    $password=trim($_GPC['password']);
    $password2=trim($_GPC['password2']);
    //先判断旧密码是否正确
    $fans=pdo_fetch("select * from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." and uid=".$uid."");
    if($password==$password2){

        if($fans['password']==$oldpass){
            //更改密码
            $res=pdo_update('enjoy_city_fans',array('password'=>$password),array('uniacid'=>$uniacid,'uid'=>$uid));
            if($res>0){
                $_SESSION['city']['password']=$password;
                //修改成功
                header("location:".$this->createMobileUrl('personal')."");
            }
        }else{
            $this->newmessage("旧密码输入错误",$this->createMobileUrl('personal'));
//             message("旧密码输入错误",$this->createMobileUrl('personal'),'error');
        } 
    }else{
       $this->newmessage("两次输入密码不一样",$this->createMobileUrl('personal'));
//         message("两次输入密码不一样",$this->createMobileUrl('personal'),'error');
    }

    
    
    
}



//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];



















include $this->template('newpwd');