<?php
global $_W,$_GPC;
$active='user';
$uniacid=$_W['uniacid'];
//先取出活动详情
$act=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");

if(!empty($_GPC['register'])){
$username=trim($_GPC['username']);
$mobile=trim($_GPC['mobile']);
$password=trim($_GPC['password']);
$password2=trim($_GPC['password2']);
    
if($password!=$password2){
   $this->newmessage("两次输入密码不一样",$this->createMobileUrl('personal'));
//     message("两次输入密码不一样",$this->createMobileUrl('personal'),'error');
}else {
    //检查此手机号码是否已注册
   $resm= pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid."
        and mobile='".$mobile."'");
    if($resm>0){
      $this->newmessage("该手机号码已注册",$this->createMobileUrl('personal'));
//         message("该手机号码已注册",$this->createMobileUrl('personal'),'error');
    }else {
        $resn= pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid."
        and username='".$username."'");
        if($resn>0){
           $this->newmessage("用户登录名被占用，请重新设置",$this->createMobileUrl('personal'));
//             message("用户登录名被占用，请重新设置",$this->createMobileUrl('personal'),'error');
        }else{
            //可以注册
            $data=array(
                'uniacid'=>$uniacid,
                'username'=>$username,
                'password'=>$password,
                'mobile'=>$mobile,
                'ip'=>CLIENT_IP,
                'createtime'=>TIMESTAMP
            );
           
            $resi=pdo_insert('enjoy_city_fans',$data);
            $uid=pdo_insertid();
            if($uid>0){
                //注册成功，登录
               // $resl=$this->islogin($username,$password,$uniacid);
                //保存用户名 密码
                $_SESSION['city']['username']=$username;
                $_SESSION['city']['password']=$password;
                $_SESSION['city']['uid']=$uid;
                header("location:".$this->createMobileUrl('login')."");
                
            }
            
        }
    }
}






}




//分享
$sharelink =  $_W['siteroot'] . "app/".$this->createMobileUrl('entry');
$sharetitle=$act['mshare_title'];
$sharecontent=$act['mshare_content'];
$shareicon=$act['mshare_icon'];
















include $this->template('register');