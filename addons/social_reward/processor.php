<?php
/*
 * 源码来自dede168资源网 www.dede168.com 交流群：97206582
 */
defined('IN_IA') or exit('Access Denied');
define('OB_ROOT', IA_ROOT . '/addons/social_reward/attachment');
define('ATTA_ROOT', '/addons/social_reward/attachment');
require_once IA_ROOT . '/addons/social_reward/phpqrcode.php';

class Social_rewardModuleProcessor extends WeModuleProcessor {
    public function respond() {
        global $_W;
        $record = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE media_id=:media_id;',array('media_id'=>$this->message['mediaid']));
        if(!empty($record)){//防止重复发图
            die();
        }
        
        $mediaid = $this->message['mediaid'];
        $imageurl = $this->message['url'];
        $insert['uniacid'] = $_W['uniacid'];
        $openid = $this->message['from'];
        $insert['openid'] = $openid;
        $ACCESS_TOKEN = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$openid}&lang=zh_CN";
        load()->func('communication');
        $json = ihttp_get($url);
        $userInfo = @json_decode($json['content'], true);
        // file_put_contents(IA_ROOT."/addons/social_reward/debug.txt","\n info:".json_encode($userInfo),FILE_APPEND);
        $insert['nickname'] = $userInfo['nickname'];
        $insert['headimgurl'] = $userInfo['headimgurl'];
        $insert['sex'] = $userInfo['sex'];
        $insert['media_id'] = $this->message['mediaid'];
        $insert['createtime'] = time();
        $insert['status'] = 1;
        //字体设置
        $text='长按并识别二维码看原图';
        $fontSize = 16;
        $font = IA_ROOT."/addons/social_reward/static/ht.ttf";
        $cfg = $this->module['config'];
        $random = random(4);
        if(empty($cfg['tip'])){
            $cfg['tip'] = "正在拼命处理中，请稍后。。。";
        }
        $this->postText($this->message['from'],$cfg['tip']);
        load()->func('file');
        mkdirs(OB_ROOT."/original/".$_W['uniacid']."/{$openid}");
        mkdirs(OB_ROOT."/blur/".$_W['uniacid']."/{$openid}");
        if($this->message['type'] == 'image'){
            $insert['type'] = 'image';
            pdo_insert('social_reward_data',$insert);
            $dataid = pdo_insertid();
            $ret = $this->getMediaURL($this->getAccessToken(),$this->message['mediaid']);
            $this->file_write("original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg",$ret['content']);
            //ob_end_clean();

            // header('Content-type: image/jpg');
            exec("convert ".OB_ROOT."/original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg"." -resize 500x800 -blur 0x60 ".OB_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg 2>&1",$output,$return_val);
            file_put_contents(IA_ROOT."/addons/social_reward/debug.txt","\n msg:".json_encode($output),FILE_APPEND);
            // 原始图片信息
            $dst = imagecreatefromjpeg(OB_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
            $dst_info[0] = imagesx($dst);
            $dst_info[1] = imagesy($dst);
            //创建二维码图片
            $value=$_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$mediaid)));
            $errorCorrectionLevel = 'L'; 
            $matrixPointSize = 3;
            QRcode::png($value, OB_ROOT."/qr_{$mediaid}.png", $errorCorrectionLevel, $matrixPointSize, 2); 

            $src = imagecreatefrompng(OB_ROOT."/qr_{$mediaid}.png");
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
            mkdirs(dirname(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg"));
            imagejpeg($im,OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
            imagedestroy($dst);
            imagedestroy($src);
            imagedestroy($im);
            pdo_update('social_reward_data',array('blururl'=>ATTA_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg",'originalurl'=>ATTA_ROOT."/original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg"),array('id'=>$dataid));
        }elseif($this->message['type'] == 'shortvideo' || $this->message['type'] == 'video'){
            $insert['type'] = $this->message['type'];
            pdo_insert('social_reward_data',$insert);
            $dataid = pdo_insertid();
            $video = $this->getMediaURL($this->getAccessToken(),$this->message['mediaid']);
            $this->file_write("original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.mp4",$video['content']);
            $ret = $this->getMediaURL($this->getAccessToken(),$this->message['thumbmediaid']);
            $this->file_write("original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg",$ret['content']);
            //ob_end_clean();

            // header('Content-type: image/jpg');
            exec("convert ".OB_ROOT."/original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg"." -blur 0x60 ".OB_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
            // 原始图片信息
            $dst = imagecreatefromjpeg(OB_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
            
            $dst_info[0] = imagesx($dst);
            $dst_info[1] = imagesy($dst);
            //创建二维码图片
            $value = $_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('opus',array('op'=>'display','mediaid'=>$mediaid)));
            $errorCorrectionLevel = 'L'; 
            $matrixPointSize = 2;
            QRcode::png($value, OB_ROOT."/qr_{$mediaid}.png", $errorCorrectionLevel, $matrixPointSize, 2); 

            $src = imagecreatefrompng(OB_ROOT."/qr_{$mediaid}.png");
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
            $im = imagecreatetruecolor($dst_info[0],$dst_info[1]+330);
            $black = imagecolorallocate($im, 0,0,0);
            $white = imagecolorallocate($im, 255,255,255);
            $fontBox = imagettfbbox($fontSize, 0, $font, $text);//文字水平居中实质
            imagettftext($im, $fontSize, 0, ceil(($dst_info[0] - $fontBox[2]) / 2), 30, $white, $font, $text);
            //合并字样图片
            imagecopymerge($im,$dst,0,150,0,0,$dst_info[0],$dst_info[1],$alpha);
            //获取视频时长
            $ffmpegInstance = new ffmpeg_movie(OB_ROOT."/original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.mp4");  
            $duration = $ffmpegInstance->getDuration();
            $text_duration = "视频时长：".round($duration)."s";
            imagettftext($im, $fontSize, 0, 10, $dst_info[1]+150+30, $white, $font, $text_duration);
            //合并二维码图片
            imagecopymerge($im,$src,($dst_info[0]-$src_info[0])/2,$dst_info[1]+300-$src_info[1]+30,0,0,$src_info[0],$src_info[1],$alpha);
            mkdirs(dirname(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg"));
            imagejpeg($im,OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
            imagedestroy($dst);
            imagedestroy($icon);
            imagedestroy($src);
            imagedestroy($im);
            pdo_update('social_reward_data',array('blururl'=>ATTA_ROOT."/blur/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg",'originalurl'=>ATTA_ROOT."/original/".$_W['uniacid']."/{$openid}/{$mediaid}_{$random}.jpg"),array('id'=>$dataid));
        }
        $center = $_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('center',array('op'=>'setting','mediaid'=>$mediaid)));
        $msg = "您可以点击<a href='".$center."'>"."这里</a>设置'打赏金额'和'图片模糊度'";
        $this->sendtext($msg,$this->message['from']);
        $media_id = $this->uploadImage(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
        $this->sendImage($openid,$media_id);
        //删除图片
        @unlink(OB_ROOT."/".$_W['uniacid']."/{$openid}/{$mediaid}.jpg");
        if(!empty($cfg['ftip'])){
            $this->postText($this->message['from'],$cfg['ftip']);
        }
        die;
    }
    function file_write($filename, $data) {
        global $_W;
        $filename = OB_ROOT . '/' . $filename;
        mkdirs(dirname($filename));
        file_put_contents($filename, $data);
        @chmod($filename, $_W['config']['setting']['filemode']);
        return is_file($filename);
    }
    public function sendImage($openid, $media_id){
        $data = array("touser" => $openid, "msgtype" => "image", "image" => array("media_id" => $media_id));
        $ret = $this->postRes($this->getAccessToken(), json_encode($data));
        return $ret;
    }
    private function uploadImage($img){
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->getAccessToken() . "&type=image";
        $post = array('media' => new CurlFile($img));
        load()->func('communication');
        $ret = ihttp_request($url, $post);
        $content = @json_decode($ret['content'], true);
        return $content['media_id'];
    }
    private function getAccessToken(){
        global $_W;
        load()->model('account');
        $acid = $_W['acid'];
        if (empty($acid)) {
            $acid = $_W['uniacid'];
        }
        $account = WeAccount::create($acid);
        $token = $account->getAccessToken();
        return $token;
    }
    private function postRes($access_token, $data){
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        load()->func('communication');
        $ret = ihttp_request($url, $data);
        $content = @json_decode($ret['content'], true);
        return $content['errcode'];
    }
    private function getMediaURL($access_token,$media_id){
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";
        load()->func('communication');
        $ret = ihttp_request($url);
        file_put_contents(IA_ROOT."/addons/social_reward/debug.txt","\n type:".gettype($ret),FILE_APPEND);
        return $ret;
    }
    public function postText($openid, $text){
        $post = '{"touser":"' . $openid . '","msgtype":"text","text":{"content":"' . $text . '"}}';
        $ret = $this->postRes($this->getAccessToken(), $post);
        return $ret;
    }
    private function sendtext($txt, $openid){
        global $_W;
        $acid = $_W['account']['acid'];
        if (!$acid) {
            $acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account') . " WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
        }
        $acc = WeAccount::create($acid);
        $data = $acc->sendCustomNotice(array('touser' => $openid, 'msgtype' => 'text', 'text' => array('content' => urlencode($txt))));
        return $data;
    }

}