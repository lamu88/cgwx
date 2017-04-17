<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$condition = " uniacid=:uniacid and status<>-2";
$params['uniacid'] = $_W['uniacid'];

if($op=='display' || $op=='waiting' || $op=='passed' || $op=='rejected' || $op=='suspect'){
	load ()->func ( 'tpl' );
	$pindex = max(1,intval($_GPC['page']));
	$psize = 10;
	
	if(!empty($_GPC['nickname'])){
		$condition .= " and nickname like '%".$_GPC['nickname']."%'";
	}
	if(!empty($_GPC['id'])){
		$condition .= " and id=:id";
		$params['id'] = $_GPC['id'];
	}
	if(!empty($_GPC['searchtime'])){
		$starttime = strtotime($_GPC['time']['start']);
		$condition .= " and createtime>:starttime";
		$params['starttime'] = $starttime;
		$endtime = strtotime($_GPC['time']['end']);
		$condition .= " and createtime<:endtime";
		$params['endtime'] = $endtime;
	}
	$opus['count'] = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.';',$params);
	$opus['passed'] = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.' and status=1;',$params);
	$opus['rejected'] = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.' and status=-1;',$params);
	$opus['waiting'] = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.' and status=0;',$params);
	$opus['suspect'] = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.' and id in(SELECT did FROM '.tablename('social_reward_reward').' WHERE complain=1 and uniacid='.$_W['uniacid'].');',$params);
	if($_GPC['status'] != ""){
		$condition .= " and status=:status";
		$params['status'] = $_GPC['status'];
	}

	
	if($op!='suspect'){
		if($op=='waiting'){
			$condition .= " and status=0 ";
		}elseif($op=='passed'){
			$condition .= " and status=1 ";
		}elseif($op=='rejected'){
			$condition .= " and status=-1 ";
		}
		$count = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.';',$params);
		$list = pdo_fetchall('SELECT * FROM '.tablename('social_reward_data').' WHERE '.$condition.' ORDER BY createtime desc LIMIT '.($pindex-1)*$psize.','.$psize.';',$params);

	}else{
		$count = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_data').' WHERE '.$condition.' and id in(SELECT did FROM '.tablename('social_reward_reward').' WHERE complain=1 and uniacid='.$_W['uniacid'].');',$params);
		$list = pdo_fetchall('SELECT * FROM '.tablename('social_reward_data').' WHERE '.$condition.' and id in(SELECT did FROM '.tablename('social_reward_reward').' WHERE complain=1 and uniacid='.$_W['uniacid'].') ORDER BY createtime desc LIMIT '.($pindex-1)*$psize.','.$psize.';',$params);
		foreach ($list as &$item) {
			$complain_count = pdo_fetchcolumn('SELECT sum(complain) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid;',array('did'=>$item['id'],'uniacid'=>$_W['uniacid']));
			$item['complain_count'] = $complain_count;
		}
		unset($item);
	}
	
	$pager = pagination($count,$pindex,$psize);
}elseif($op=='pass'){
	$dataid = $_GPC['did'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE id=:did and uniacid=:uniacid;',array('did'=>$dataid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		message('您无法操作该记录',referer(),'info');
	}else{
		pdo_update('social_reward_data',array('status'=>1),array('id'=>$dataid));
		$cfg = $this->module['config'];
		if(!empty($cfg['noticetype'])||empty($cfg['verify_notice'])){
			$msg = "尊敬的【#nickname#】\n您有一个内容审核#result#，如有疑问可找客服申诉，内容编号为：#index#";
			if(!empty($cfg['verify_custom'])){
				$msg = $cfg['verify_custom'];
			}
			$msg = str_replace('#nickname#', $data['nickname'], $msg);
			$msg = str_replace('#result#', "通过", $msg);
            $msg = str_replace('#index#', $dataid, $msg);
            $this->sendtext($msg,$data['openid']);
		}else{
			$center = $_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('Opus',array('p'=>'detail','media_id'=>$data['media_id'])));
			$msg = array('first' => array('value' => "您提交的编号为".$dataid."的作品已通过审核", "color" => "#ff0000"), 'keyword1' => array('value' => "审核通过", "color" => "#4a5077"), 'keyword2' => array('value' => date('Y-m-d H:i:s',time())),'remark' => array('value' => "点击查看", "color" => "#4a5077"));
			$this->sendMsg($data['openid'], $cfg['verify_notice'], $msg, $center);
		}
		message('审核成功',referer(),'success');
	}
}elseif($op=='reject'){
	$dataid = $_GPC['did'];
	$data = pdo_fetch('SELECT * FROM '.tablename('social_reward_data').' WHERE id=:did and uniacid=:uniacid;',array('did'=>$dataid,'uniacid'=>$_W['uniacid']));
	if(empty($data)){
		message('您无法操作该记录',referer(),'info');
	}else{
		pdo_update('social_reward_data',array('status'=>-1),array('id'=>$dataid));
		$cfg = $this->module['config'];
		if(!empty($cfg['noticetype'])||empty($cfg['verify_notice'])){
			$msg = "尊敬的【#nickname#】\n您有一个内容审核#result#，如有疑问可找客服申诉，内容编号为：#index#";
			if(!empty($cfg['verify_custom'])){
				$msg = $cfg['verify_custom'];
			}
			$msg = str_replace('#nickname#', $data['nickname'], $msg);
			$msg = str_replace('#result#', "不通过", $msg);
            $msg = str_replace('#index#', $dataid, $msg);
            $this->sendtext($msg,$data['openid']);
		}else{
			$center = $_W['siteroot'] . str_replace('./', 'app/', $this -> createMobileurl('Opus',array('p'=>'detail','media_id'=>$data['media_id'])));
			$msg = array('first' => array('value' => "您提交的编号为".$dataid."的作品未通过审核", "color" => "#ff0000"), 'keyword1' => array('value' => "审核失败", "color" => "#4a5077"), 'keyword2' => array('value' => date('Y-m-d H:i:s',time())),'remark' => array('value' => "如有疑问可找客服申诉", "color" => "#4a5077"));
			$this->sendMsg($data['openid'], $cfg['verify_notice'], $msg);
		}
		message('审核成功',referer(),'success');
	}
}elseif($op=='viewcomplain'){
	$pindex = max(1,intval($_GPC['page']));
	$psize = 10;
	$re_count = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('social_reward_reward').' WHERE did=:did and uniacid=:uniacid and complain=1;',array('did'=>$_GPC['did'],'uniacid'=>$_W['uniacid']));
	$records = pdo_fetchall('SELECT * FROM '.tablename('social_reward_reward').'  WHERE did=:did and uniacid=:uniacid and complain=1 LIMIT '.($pindex-1)*$psize.', '.$psize.';',array('did'=>$_GPC['did'],'uniacid'=>$_W['uniacid']));
	$pager = pagination($re_count,$pindex,$psize);
}
include $this->template('web/opus/index');