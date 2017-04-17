<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");

if(!empty($_GPC['setpwd'])){
    session_start();
    $uid=$_SESSION['city']['uid'];
    $mobile=trim($_GPC['mobile']);
    $password=trim($_GPC['password']);
    $password2=trim($_GPC['password2']);
    //先判断旧密码是否正确
    $fans=pdo_fetch("select * from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." and uid=".$uid."");
    if($password==$password2){
        //检查此手机号码是否已注册
        $resm= pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid."
        and mobile='".$mobile."'");
        if($resm>0){
            $this->newmessage("该手机号码已被占用",$this->createMobileUrl('personal'));
//             message("该手机号码已被占用",$this->createMobileUrl('personal'),'error');
        }else{
            //设置成功
            $res=pdo_update('enjoy_city_fans',array('password'=>$password,'mobile'=>$mobile),array('uniacid'=>$uniacid,'uid'=>$uid));
            if($res>0){
                $_SESSION['city']['password']=$password;
                //修改成功
                header("location:".$this->createMobileUrl('personal')."");
            }
        }
    }else{
       $this->newmessage("两次输入密码不一致",$this->createMobileUrl('personal'));
//         message("两次输入密码不一致",$this->createMobileUrl('personal'),'error');
    }

    
    
    
}








//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];













include $this->template('setpwd');