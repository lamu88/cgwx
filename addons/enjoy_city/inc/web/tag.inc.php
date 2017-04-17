<?php
global $_W,$_GPC;
load()->func('tpl');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$uniacid=$_W['uniacid'];
$pindex = max(1, intval($_GPC['page']));
$psize = 15;
if ($op == 'display') {
	$list = pdo_fetchall("SELECT a.*,b.fnickname,c.id as cid,c.title FROM " . tablename('enjoy_city_firmlabel') . " as a left join 
	    ".tablename('enjoy_city_firmfans')." as b on a.openid=b.openid and a.fid=b.fid
	    left join ".tablename('enjoy_city_firm')." as c on a.fid=c.id WHERE a.uniacid = '{$_W['uniacid']}' and a.checked=0 
	    and b.flag=1 ORDER BY a.createtime asc
	LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$countadd=pdo_fetchcolumn("select count(*) FROM " . tablename('enjoy_city_firmlabel') . " as a left join 
	    ".tablename('enjoy_city_firmfans')." as b on a.openid=b.openid and a.fid=b.fid WHERE a.uniacid = '{$_W['uniacid']}'
	and b.flag=1 and a.checked=0");

	$pager = pagination($countadd, $pindex, $psize);
} elseif ($op == 'checked') {
    $checked=$_GPC['checked'];
    $id=$_GPC['id'];
    if($checked==0){
        $checked=1;
    }else{
        $checked=0;
    }
    $resr=pdo_update('enjoy_city_firmlabel',array('checked'=>$checked),array('uniacid'=>$uniacid,'id'=>$id));
 message('审核成功', $this->createWebUrl('tag'), 'success');
// 	$id = intval($_GPC['id']);
// 	if (checksubmit('submit')) {
// 		$data = array(
// 				'uniacid' => $_W['uniacid'],
// 				'hot'=>$_GPC['hot'],
// 				'title'=>$_GPC['title'],
// 				'pic'=>$_GPC['pic'],
// 				'url'=>$_GPC['url'],
// 		);
// 		if (!empty($id)) {
// 			pdo_update('enjoy_city_firmlabel', $data, array('id' => $id));
// 			$message="更新标签成功！";
// 		} else {
// 			pdo_insert('enjoy_city_firmlabel', $data);
// 			$id = pdo_insertid();
// 			$message="新增标签成功！";
// 		}
// 		message($message, $this->createWebUrl('tag', array('op' => 'display')), 'success');
// 	}
// 	//修改
// 	$ad = pdo_fetch("SELECT * FROM " . tablename('enjoy_city_firmlabel') . " WHERE id = '$id' and uniacid = '{$_W['uniacid']}'");
} elseif ($op == 'delete') {
	$id = intval($_GPC['id']);
	$ad = pdo_fetch("SELECT id FROM " . tablename('enjoy_city_firmlabel') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
	if (empty($ad)) {
		message('抱歉，标签不存在或是已经被删除！', $this->createWebUrl('tag', array('op' => 'display')), 'error');
	}
	pdo_delete('enjoy_city_firmlabel', array('id' => $id));
	message('标签删除成功！', $this->createWebUrl('tag', array('op' => 'display')), 'success');
} else {
	message('请求方式不存在');
}
$basic=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=0");

include $this->template('tag');