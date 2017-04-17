<?php
global $_W,$_GPC;
load()->func('tpl');
$uniacid=$_W['uniacid'];
//基本设置
$serverip = $this->getServerIP();
$item=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
$item['title']=empty($item['title'])?'微城市':$item['title'];
//$item['bgpic']=empty($item['share_icon'])?'./addons/enjoy_luck/public/images/redpackets_home_bg_title.png':$item['apic'];
$item['share_title']=empty($item['share_title'])?'#firm#强势入驻微城市':$item['share_title'];
$item['share_content']=empty($item['share_content'])?'我向你推荐#firm#，快来看看吧':$item['share_content'];
$item['mshare_title']=empty($item['mshare_title'])?'微城市无所不查，邀您入驻':$item['mshare_title'];
$item['mshare_content']=empty($item['mshare_content'])?'一个神奇的网站':$item['mshare_content'];
$item['jointel']=empty($item['jointel'])?'每日查询500次，加盟联系：#tel#':$item['jointel'];
$item['share_icon']=empty($item['share_icon'])?'#firmlogo#':$item['share_icon'];
$item['province']=empty($item['province'])?'北京':$item['province'];
$item['city']=empty($item['city'])?'北京市':$item['city'];
$item['district']=empty($item['district'])?'海淀区':$item['district'];
$item['slogo']=empty($item['slogo'])?'./addons/enjoy_city/public/images/slogo.png':$item['slogo'];
$item['banner']=empty($item['banner'])?'./addons/enjoy_city/public/images/banner.jpg':$item['banner'];
$basic=pdo_fetch("select * from ".tablename('enjoy_city_reply')." where uniacid=0");

//提交
if(checksubmit('submit')){
//     var_dump($_GPC);
//     exit();
	//判断是否已经存在这个代理
	$exist=pdo_fetchcolumn("select count(*) from ".tablename('enjoy_city_reply')." where uniacid=".$uniacid."");
	$data=array(
			'uniacid'=>$uniacid,
			'title'=>$_GPC['title'],
			'ewm'=>trim($_GPC['ewm']),
			'icon'=>trim($_GPC['icon']),
			'province'=>trim($_GPC['province']),
			'city'=>trim($_GPC['city']),
			'district'=>trim($_GPC['district']),
			'tel'=>trim($_GPC['tel']),
			'copyright'=>trim($_GPC['copyright']),
			'slogo'=>trim($_GPC['slogo']),
			'banner'=>trim($_GPC['banner']),
// 			'color'=>$_GPC['color'],
// 			'bgpic'=>$_GPC['bgpic'],
// 			'redpic'=>$_GPC['redpic'],
			'sucai'=>$_GPC['sucai'],
	        'weixin'=>trim($_GPC['weixin']),
			'share_icon'=>$_GPC['share_icon'],
			'share_title'=>$_GPC['share_title'],
			'share_content'=>$_GPC['share_content'],
			'mshare_icon'=>$_GPC['mshare_icon'],
			'mshare_title'=>$_GPC['mshare_title'],
			'mshare_content'=>$_GPC['mshare_content'],
			'jointel'=>$_GPC['jointel'],
			'agreement'=>$_GPC['agreement'],
			'fee'=>$_GPC['fee'],
			'bonus'=>trim($_GPC['bonus']),
			'kfewm'=>$_GPC['kfewm'],
			'onlinepay'=>$_GPC['onlinepay'],
			'isright'=>$_GPC['isright'],
			'issmple'=>$_GPC['issmple'],
// 			'rule'=>$_GPC['rule'],
// 			'limited'=>$_GPC['limited'],
// 			'bsubscribe'=>$_GPC['bsubscribe'],
// 			'asubscribe'=>$_GPC['asubscribe'],
// 			'snum'=>$_GPC['snum'],
// 			'bnum'=>$_GPC['bnum'],
// 			'spay'=>$_GPC['spay'],
// 			'bpay'=>$_GPC['bpay'],
// 			'smoney'=>$_GPC['smoney'],
// 			'minget'=>$_GPC['minget'],
// 			'cashmin'=>$_GPC['cashmin'],
// 			'ischeck'=>$_GPC['ischeck'],
// 			'ewm'=>$_GPC['ewm'],
// 			'kinda'=>$_GPC['kinda'],
// 			'locationtype'=>$_GPC['locationtype'],
            'createtime'=>TIMESTAMP

	);
	if($exist>0){
		//update
		$res=pdo_update('enjoy_city_reply',$data,array('uniacid'=>$uniacid));
		$message="更新代理成功";

	}else{

		//插入数据库
		$res=pdo_insert('enjoy_city_reply',$data);
		$message="新增代理成功";
			
	}

		
	if($res==1){
		message($message,$this->createWebUrl('basic'), 'success');
	}
		
		
		
}






include $this->template('basic');