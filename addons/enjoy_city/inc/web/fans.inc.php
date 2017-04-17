<?php
//粉丝详情查询
global $_W,$_GPC;
$gid=$_GPC['gid'];
$uniacid=$_W['uniacid'];
$op=$_GPC['op'];
//删除用户信息
if($op=='delete'){
    //$id = intval($_GPC['id']);
    $uid=$_GPC['uid'];
	
    
 	//删除fans表
 	$res=pdo_delete('enjoy_city_fans', array('uid' => $uid,'uniacid'=>$uniacid));

	if($res>0){

		message('用户删除成功！', $this->createWebUrl('fans', array()), 'success');
	}
	
}else if($op=='ischeck'){

$ischeck=$_GPC['ischeck'];
$uid=$_GPC['uid'];
if($ischeck==0){
	$ischeck=1;
}else{
	$ischeck=0;
}
pdo_update('enjoy_city_fans',array('ischeck'=>$ischeck),array('uniacid'=>$uniacid,'uid'=>$uid));
message('操作成功', $this->createWebUrl('fans'), 'success');

	
	
}

$pindex = max(1, intval($_GPC['page']));
$psize = 10;
if($_GPC['nickname']){
 $where="and nickname LIKE '%".$_GPC['nickname']."%'";

}else{
	$where="";
}
// if($_GPC['unusual']){
// 	$where1="and ischeck=0";
// }else{
// 	$where1="";
// }

//查询粉丝
$fans=pdo_fetchall("select a.*,b.cnickname,b.id as cid from ".tablename('enjoy_city_fans')." as a left join ".tablename('enjoy_city_contact')."
    as b on a.uid=b.uid where 
    a.uniacid=".$uniacid." ".$where." order by uid desc LIMIT 
    " . ($pindex - 1) * $psize . ',' . $psize);
for($i=0;$i<count($fans);$i++){
	$fans[$i]['firm']=pdo_fetchall("select id,title from ".tablename('enjoy_city_firm')." where uniacid=".$uniacid." and uid=".$fans[$i][uid]."");
}
// var_dump($fans);
// exit();


//实际参加人数
$countadd=pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." ".$where."");
$pager = pagination($countadd, $pindex, $psize);
//已返现金额
//$countsum=pdo_fetchcolumn("select sum(money) from ".tablename('enjoy_back_moneylog')." where gid=".$gid." and uniacid=".$uniacid."");
//已支付金额
//$paysum=pdo_fetchcolumn("select sum(fee) from ".tablename('enjoy_back_paylog')." where uniacid=".$uniacid." and status=1");

//粉丝总人数
$countfans=pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid."");
		
$basic=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=0");

//审核过的人数
$countcheck=pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_fans')." where uniacid=".$uniacid." and ischeck=1");



















include $this->template('fans');