<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
class user {
	public function __construct() {
		global $_W;
	} 
	function getOpenid() {
		$userinfo = $this -> getInfo();
		return $userinfo['openid'];
	} 
	function getInfo($debug = false) {
		global $_W, $_GPC;
		$userinfo = array();
		load() -> model('mc');
		$userinfo = mc_oauth_userinfo();
		$uid = mc_openid2uid($userinfo['openid']);
		$member = mc_fetch($uid, array('nickname','avatar'));
		if(empty($member['nickname']) || empty($member['avatar'])){
			$result = mc_update($uid, array('nickname' => $userinfo['nickname'],'avatar' => $userinfo['avatar']));
		}
		if(!$debug){
			if (empty($userinfo['openid'])) {
				die("<!DOCTYPE html>
	            <html>
	                <head>
	                    <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
	                    <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
	                </head>
	                <body>
	                <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请在微信客户端打开链接</h4></div></div></div>
	                </body>
	            </html>");
			} 
		}
		return $userinfo;
	}
	function getSettingInfo(){
		//获取所有参数设置
		global $_W;
		$settings = pdo_fetch("select * from".tablename('uni_account_modules')."where uniacid='{$_W['uniacid']}' and module='weliam_indiana'");
		return unserialize($settings['settings']);
	}
	function followed(){
		//验证用户是否关注
		global $_W,$_GPC;
		$openid = self::getOpenid();
		$followeds = self::getSettingInfo();
		$iscredit1_followed = $followeds['iscredit1_followed'];
		if(!empty($openid)){
			$attention = pdo_fetch("select follow from".tablename('mc_mapping_fans')."where uniacid = '{$_W['uniacid']}' and openid = '{$openid}'");
			$followed = $attention['follow'] == 1;
		}
		
		return $followed;
	} 
} 
