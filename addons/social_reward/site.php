<?php
/*
 * 源码来自dede168资源网 www.dede168.com 交流群：97206582
 */
defined('IN_IA') or exit('Access Denied');
define('OB_ROOT', IA_ROOT . '/addons/social_reward/attachment');
define('ATTA_ROOT', '/addons/social_reward/attachment');
require_once IA_ROOT . '/addons/social_reward/phpqrcode.php';
class Social_rewardModuleSite extends WeModuleSite {
	public function doWebOpus(){$this->_exec(__FUNCTION__ ,'index');}
    public function doMobileCenter(){$this->_exec(__FUNCTION__ ,'index',false);}
    public function doMobileOpus(){$this->_exec(__FUNCTION__ ,'index',false);}
    public function doMobileMore(){$this->_exec(__FUNCTION__ ,'index',false);}
	public function _exec($do, $default = '', $web = true){
        global $_GPC;
        $do = strtolower(substr($do, $web ? 5 : 8));
        $p  = trim($_GPC['p']);
        empty($p) && $p = $default;
        if ($web) {
            $file = IA_ROOT . "/addons/social_reward/core/web/" . $do . "/" . $p . ".php";
        } else {
            $file = IA_ROOT . "/addons/social_reward/core/mobile/" . $do . "/" . $p . ".php";
        }
        if (!is_file($file)) {
            message("未找到 控制器文件 {$do}::{$p} : {$file}");
        }
        include $file;
        exit;
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
    public function sendMsg($openid, $tplmsgid, $data = array(), $url = ""){
        global $_W;

        if (!empty($data)){
            $account = WeAccount::create($_W['account']['acid']);
            if ($_W['account']['level'] == 3){
                foreach ($tplmsg["data"] as $key => $value){
                    $tplmsg["content"] = str_replace("{{" . $key . ".DATA}}", $value["value"], $tplmsg["content"]);
                }
                return $account -> sendCustomNotice(array("touser" => $openid, "msgtype" => "text", "text" => array("content" => urlencode($tplmsg["content"]),),));
            }elseif ($_W['account']['level'] == 4){
                return $account -> sendTplNotice($openid, $tplmsgid, $data, $url);
            }
        }
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

}