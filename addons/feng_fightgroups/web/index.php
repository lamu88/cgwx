<?php
if(empty($_GPC)){
	require_once '../../../framework/bootstrap.inc.php';
	if($_GPC['c'] == 'utility'){
		require IA_ROOT. '/addons/feng_fightgroups/web/merch.php';
		exit;
	}
}
define('IN_MOBILE', true);
wl_load()->func('template');
wl_load()->model('setting');
wl_load()->model('permissions');
load()->func('communication');
global $_W,$_GPC;

session_start();
$_SESSION['role']='';
define('UNIACID', '');
define('TG_ID', '');
define('TG_MERCHANTID','');
define('MERCHANTID','');
$domain = $_SERVER['SERVER_NAME']; 
$resp = ihttp_request('../addons/weliam_manage/api/user.api.php', array('type' => 'data','module' => 'feng_fightgroups','website' => $_W['setting']['site']['key'],'domain'=>$domain),null,1);

$controller = $_GPC['do'];
$action = $_GPC['ac'];
$op = $_GPC['op'];

if(empty($controller) || empty($action)) {
	$_GPC['do'] = $controller = 'store';
	$_GPC['ac'] = $action = 'setting';
}
$getlistFrames = 'get'.$controller.'Frames';
$frames = $getlistFrames();
$top_menus = get_top_menus();
tgsetting_load();

ihttp_request(MODULE_URL.'core/asynchronous/auto.php', array('uniacid' => $uniacid),array('Content-Type' => 'application/x-www-form-urlencoded'),1);
$file = TG_WEB . 'controller/'.$controller.'/'.$action.'.ctrl.php';
if (!file_exists($file)) {
	header("Location: index.php?c=site&a=entry&m=feng_fightgroups&do=store&ac=setting&op=display&");
	exit;
}
require $file;

