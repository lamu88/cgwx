<?php
/**
 * fwei_forms 通用表单
 * ============================================================================
 * * 版权所有 2005-2012 fwei.net，并保留所有权利。
 *   网站地址: http://www.fwei.net；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: fwei.net  / 1331305@qq.com
 *
 **/
include MODULE_ROOT . '/inc/common.php';
global $_GPC,  $_W;
$uniacid = $_W["uniacid"];
$rid = intval( $_GPC['id'] );
$page = intval( $_GPC['page'] );
$page = max(1, $page);
$limit = 300;
$slimit = ($page - 1) * $limit;
set_time_limit(0);

$cache_file = MODULE_ROOT . '/_data/cache_'.$rid.'.php';
if( !is_dir(MODULE_ROOT . '/_data') ){
	mkdir( MODULE_ROOT . '/_data', 0777 );
}
//表单基本信息
$forms = pdo_fetch('SELECT formid, title, stime, etime, num, total FROM '.tablename('fwei_forms')." WHERE rid = :rid AND uniacid = :uniacid", array(':uniacid' => $uniacid, ':rid'=>$rid) );
if( empty($forms) ){
	message('参数错误!', '',  'error');
}
$tmps = array();
$attrs = pdo_fetchall('SELECT attr_id, title FROM '.tablename('fwei_forms_attrs')." WHERE formid = :formid AND uniacid = :uniacid ORDER BY sort ASC, attr_id ASC", array(':uniacid' => $uniacid, ':formid'=>$forms['formid']) );
if( $page == 1 ){
	$out_data = array();
	//取出调研问题列表
	$tmps[] = 'FID';
	$tmps[] = 'OPENID';
	foreach ($attrs as $key => $val) {
		$tmps[] = $val['title'];
	}
	$tmps[] = '状态';
	$tmps[] = '时间';
	$out_data[] = $tmps;
} else {
	$out_data = include_once $cache_file;
}
$fanslist = pdo_fetchall("SELECT * FROM ".tablename('fwei_forms_fans')." WHERE formid = :formid ORDER BY fid DESC LIMIT $slimit, $limit" , array(':formid'=>$forms['formid']) );
if( empty($fanslist) ){
	unlink($cache_file);
	toExcel($out_data,"{$forms['title']}.xls");
	
	exit();
}

foreach ($fanslist as $val) {
	$tmps = array(
		$val['fid'],
		$val['openid']
	);
	$results = pdo_fetchall("SELECT GROUP_CONCAT(attr_value SEPARATOR ';') as attr_value, attr_id FROM ".tablename('fwei_forms_values').' WHERE formid = :formid AND fid = :fid GROUP BY attr_id', array(':formid'=>$forms['formid'], ':fid'=>$val['fid']), 'attr_id');
	foreach ($attrs as $question) {
		$tmps[] = $results[$question['attr_id']]['attr_value'];
	}
	$tmps[] = $val['status'] ? '已处理' : '未处理';
	$tmps[] = date('Y-m-d H:i:s', $val['created']);
	$out_data[] = $tmps;
}

file_put_contents($cache_file, "<?php \n\nreturn ".var_export($out_data, true).";");
$url = $this->createWebUrl('export', array('id'=>$rid,'page'=>($page+1) ));
echo "<meta http-equiv=\"refresh\" content=\"2;url=".($url)."\" />每页处理{$limit}条数据， 等2秒后进入下一页继续处理！";
