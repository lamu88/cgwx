<?php
/*
 * 源码来自dede168资源网 www.dede168.com 交流群：97206582
 */
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'original';
$condition = " uniacid=:uniacid ";
$openid = $_W['openid'];
if(empty($openid)){//未关注者 授权登录
	$script = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$_W['account']['key']."&redirect_uri=".urlencode($_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$_GPC['mediaid']))))."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
	$html = "<script> window.location='".$script."'</script>";
	die($html);
}
$pindex = max(1,$_GPC['page']);
$psize = 6;
$total = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE uniacid=:uniacid and openid=:openid and status<>-2;',array('openid'=>$openid,'uniacid'=>$_W['uniacid']));

$list = pdo_fetchall('SELECT * FROM '.tablename('social_reward_data').' WHERE uniacid=:uniacid and openid=:openid and status<>-2 LIMIT '.($pindex-1)*$psize.','.$psize.';',array('uniacid'=>$_W['uniacid'],'openid'=>$openid));
$showcount = min($total,($pindex-1)*$psize+$psize);
if($op=='original' && $_W['ispost']){
	include $this->template('center/tpl');
	die();
}elseif($op=='delete'){
	$did = $_GPC['did'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE id=:did and uniacid=:uniacid and openid=:openid;',array('did'=>$did,'uniacid'=>$_W['uniacid'],'openid'=>$openid));
	if(empty($data)){
		die(json_encode(array('status'=>-1)));
	}else{
		// pdo_delete('social_reward_data',array('id'=>$did));
		pdo_update('social_reward_data',array('status'=>-2),array('id'=>$did));
		die(json_encode(array('status'=>1)));
	}
}elseif($op=='temp'){

	$media_id = $_GPC['mediaid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:media_id and openid=:openid and uniacid=:uniacid;',array('media_id'=>$media_id,'openid'=>$openid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$result = array('status'=>0,'result'=>'您无法操作该图片');
	}else{
		$fuzzy = intval($_GPC['fuzzy']);
		$random = $_GPC['random'];
		load()->func('file');
		mkdirs(OB_ROOT."/temp");
		exec("convert ".IA_ROOT.$data['originalurl']." -resize 500x800 -blur 0x".$fuzzy." ".OB_ROOT."/temp/{$random}.jpg 2>&1",$output,$return_val);
		$result = array('status'=>1,'result'=>$random);
	}
	die(json_encode($result));
}elseif($op=='setting'){
	$media_id = $_GPC['mediaid'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:media_id and openid=:openid and uniacid=:uniacid;',array('media_id'=>$media_id,'openid'=>$openid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		$enable = false;
	}else{
		$enable = true;
		if($_W['ispost']&&$_W['isajax']){
			load()->func('communication');
			load()->func('file');
			//字体设置
	        $text='长按并识别二维码看原图';
	        $fontSize = 16;
	        $font = IA_ROOT."/addons/social_reward/static/ht.ttf";
	        $cfg = $this->module['config'];
			$fuzzy = intval($_GPC['fuzzy']);
			$reward_min = $_GPC['reward_min'];
			$reward_max = $_GPC['reward_max'];
			$random = $_GPC['random'];
			if($data['type']=='image'){
				exec("convert ".IA_ROOT.$data['originalurl']." -resize 500x800 -blur 0x".$fuzzy." ".IA_ROOT.$data['blururl']);
	            // echo $json['content'];
	            // 原始图片信息
	            $dst = imagecreatefromjpeg(IA_ROOT.$data['blururl']);
	            $dst_info[0] = imagesx($dst);
	            $dst_info[1] = imagesy($dst);
	            ///创建二维码图片
	            $value=$_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$media_id)));
	            $errorCorrectionLevel = 'L'; 
	            $matrixPointSize = 3;
	            QRcode::png($value, OB_ROOT."/qr_{$media_id}.png", $errorCorrectionLevel, $matrixPointSize, 2); 

	            $src = imagecreatefrompng(OB_ROOT."/qr_{$media_id}.png");
	            $src_info[0] = imagesx($src);
	            $src_info[1] = imagesy($src);
	            //二维码透明度
	            $alpha = 100;
	            //合并二维码图片
	            imagecopymerge($dst,$src,($dst_info[0]-$src_info[0])/2,$dst_info[1]-$src_info[1]-30,0,0,$src_info[0],$src_info[1],$alpha);

	            //加入字样
	            $im = imagecreatetruecolor($dst_info[0],$dst_info[1]+50);
	            $black = imagecolorallocate($im, 0,0,0);
	            $white = imagecolorallocate($im, 255,255,255);
	            $fontBox = imagettfbbox($fontSize, 0, $font, $text);//文字水平居中实质
	            imagettftext($im, $fontSize, 0, ceil(($dst_info[0] - $fontBox[2]) / 2), 25, $white, $font, $text);
	            //合并字样图片
	            imagecopymerge($im,$dst,0,50,0,0,$dst_info[0],$dst_info[1],$alpha);
	            mkdirs(dirname(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg"));
	            imagejpeg($im,OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg");
	            imagedestroy($dst);
	            imagedestroy($src);
	            imagedestroy($im);
	            pdo_update('social_reward_data',array('fuzzy'=>$fuzzy,'random'=>$random,'reward_min'=>$reward_min,'reward_max'=>$reward_max),array('id'=>$data['id']));
			}elseif($data['type']=='shortvideo'){
				exec("convert ".IA_ROOT.$data['originalurl']." -blur 0x".$fuzzy." ".IA_ROOT.$data['blururl']);
	            // 原始图片信息
	            $dst = imagecreatefromjpeg(IA_ROOT.$data['blururl']);
	            $dst_info[0] = imagesx($dst);
	            $dst_info[1] = imagesy($dst);
	            //创建二维码图片
	            $value=$_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$media_id)));
	            $errorCorrectionLevel = 'L'; 
	            $matrixPointSize = 2;
	            QRcode::png($value, OB_ROOT."/qr_{$media_id}.png", $errorCorrectionLevel, $matrixPointSize, 2); 

	            $src = imagecreatefrompng(OB_ROOT."/qr_{$media_id}.png");
	            $src_info[0] = imagesx($src);
	            $src_info[1] = imagesy($src);
	            //透明度
	            $alpha = 100;
	            //播放icon
	           
	            // $bg = imagecolorallocatealpha($icon_img,255,255,255,0);
	            // imagealphablending($icon_img, false);
	            // imagesavealpha($icon_img, true);
	            $icon = imagecreatefrompng(IA_ROOT."/addons/social_reward/static/player.png");
	            $black = imagecolorallocatealpha($icon, 255,255,255,127);
	            imagecolortransparent($icon,$black);
	            imagealphablending($icon, false);
	            imagesavealpha($icon, true);
	            $icon_info[0] = imagesx($icon);
	            $icon_info[1] = imagesy($icon);
	            // $icon_img = imagecreate($icon_info[0],$icon_info[1]);
	            // imagealphablending($icon, false);
	            // imagesavealpha($icon, true);
	            // imagecopymerge($icon_img,$icon,0,0,0,0,$icon_info[0],$icon_info[1],$alpha);
	            
	            
	            //合并播放icon
	            imagecopymerge($dst,$icon,($dst_info[0]-$icon_info[0])/2,($dst_info[1]-$icon_info[1])/2,0,0,$icon_info[0],$icon_info[1],$alpha);
	            //加入字样
	            $im = imagecreatetruecolor($dst_info[0],$dst_info[1]+350);
	            $black = imagecolorallocate($im, 0,0,0);
	            $white = imagecolorallocate($im, 255,255,255);
	            $fontBox = imagettfbbox($fontSize, 0, $font, $text);//文字水平居中实质
	            imagettftext($im, $fontSize, 0, ceil(($dst_info[0] - $fontBox[2]) / 2), 30, $white, $font, $text);
	            //合并字样图片
	            imagecopymerge($im,$dst,0,150,0,0,$dst_info[0],$dst_info[1],$alpha);
	            //获取视频时长
	            $ffmpegInstance = new ffmpeg_movie(str_replace('jpg','mp4',IA_ROOT.$data['originalurl']));  
	            $duration = $ffmpegInstance->getDuration();
	            $text_duration = "视频时长：".round($duration)."s";
	            imagettftext($im, $fontSize, 0, 10, $dst_info[1]+150+30, $white, $font, $text_duration);
	            //合并二维码图片
	            imagecopymerge($im,$src,($dst_info[0]-$src_info[0])/2,$dst_info[1]+320-$src_info[1]+30,0,0,$src_info[0],$src_info[1],$alpha);
	            mkdirs(dirname(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg"));
	            imagejpeg($im,OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg");
	            imagedestroy($dst);
	            imagedestroy($icon);
	            imagedestroy($src);
	            imagedestroy($im);
	            pdo_update('social_reward_data',array('fuzzy'=>$fuzzy,'random'=>$random,'reward_min'=>$reward_min,'reward_max'=>$reward_max),array('id'=>$data['id']));			
	        }
	        $center = $_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('center',array('op'=>'setting','mediaid'=>$media_id)));
	        $msg = "您可以点击<a href='".$center."'>"."这里</a>设置'打赏金额'和'图片模糊度'";
	        $this->sendtext($msg,$this->message['from']);
	        $media_id = $this->uploadImage(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg");
	        $this->sendImage($openid,$media_id);
	        //删除图片
	        @unlink(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$media_id}.jpg");
	        if(!empty($cfg['ftip'])){
	            $this->postText($this->message['from'],$cfg['ftip']);
	        }
	        die("1");
		}
	}

	include $this->template('center/setting');
	die;
}
include $this->template('center/index');