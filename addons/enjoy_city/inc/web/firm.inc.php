<?php

global $_W, $_GPC;
load()->func('tpl');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 9;
$uniacid = $_W['uniacid'];
if ($op == 'display') {
	if ($_GPC['title']) {
		$where = "and a.title LIKE '%" . $_GPC['title'] . "%'";
	} else {
		$where = "";
	}
	if ($_GPC['unusual']) {
		$where1 = "and a.ischeck=0";
	} else {
		$where1 = "";
	}
	if (checksubmit('submit')) {
		if (!empty($_GPC['hot'])) {
			foreach ($_GPC['hot'] as $id => $hot) {
				pdo_update('enjoy_city_firm', array('hot' => $hot), array('id' => $id));
			}
			message('排序更新成功！', $this->createWebUrl('firm', array('op' => 'display')), 'success');
		}
	}
	// 	if($_GPC['title']){
	// 		$where="and a.title LIKE '%".$_GPC['title']."%'";
	// 	}
	//$list = pdo_fetchall("SELECT * FROM " . tablename('enjoy_city_firm') . " WHERE uniacid = '{$_W['uniacid']}' ".$where." ORDER BY hot desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$list = pdo_fetchall("SELECT a.*,b.name FROM " . tablename('enjoy_city_firm') . " as a left join " . tablename('enjoy_city_kind') . " as b on
		    a.childid=b.id WHERE a.uniacid = '{$_W['uniacid']}' " . $where . $where1 . " and a.ispay>-1 ORDER BY a.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$countadd = pdo_fetchcolumn("select count(*) FROM " . tablename('enjoy_city_firm') . " as a left join " . tablename('enjoy_city_kind') . " as b on
		    a.childid=b.id WHERE a.uniacid = '{$_W['uniacid']}' " . $where . $where1 . " and a.ispay>-1");
	//商家总数
	$sumfirm = pdo_fetchcolumn("select count(*) from " . tablename('enjoy_city_firm') . " where ispay>-1 and
		    uniacid=" . $uniacid . "");
	//通过审核的商家
	$checkfirm = pdo_fetchcolumn("select count(*) from " . tablename('enjoy_city_firm') . " where ispay>-1 and ischeck>0 and
		    uniacid=" . $uniacid . "");
	$pager = pagination($countadd, $pindex, $psize);
	for ($i = 0; $i < count($list); $i++) {
		$list[$i]['img'] = unserialize($list[$i]['img']);
	}
} elseif ($op == 'post') {
	//查询业务员
	$sellers = pdo_fetchall("select * from " . tablename('enjoy_city_seller') . " where uniacid=" . $uniacid . "");
	$sql = 'SELECT * FROM ' . tablename('enjoy_city_kind') . ' WHERE `uniacid` = :uniacid ORDER BY `parentid`, `hot` asc';
	$category = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'id');
	$parent = $children = array();
	if (!empty($category)) {
		foreach ($category as $cid => $cate) {
			if (!empty($cate['parentid'])) {
				$children[$cate['parentid']][] = $cate;
			} else {
				$parent[$cate['id']] = $cate;
			}
		}
	}
	if (!empty($_GPC['category']['childid'])) {
		$params[':ccate'] = intval($_GPC['category']['childid']);
	}
	if (!empty($_GPC['category']['parentid'])) {
		$params[':pcate'] = intval($_GPC['category']['parentid']);
	}
	//     var_dump($parent);
	//     var_dump($children);
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'title' => $_GPC['title'], 'hot' => $_GPC['hot'], 'stime' => $_GPC['stime'], 'etime' => $_GPC['etime'], 'province' => $_GPC['reside']['province'], 'city' => $_GPC['reside']['city'], 'district' => $_GPC['reside']['district'], 'address' => $_GPC['address'], 'location_x' => $_GPC['baidumap']['lng'], 'location_y' => $_GPC['baidumap']['lat'], 'intro' => $_GPC['intro'], 'tel' => $_GPC['tel'], 'icon' => $_GPC['icon'], 'img' => $_GPC['img'], 'browse' => $_GPC['browse'], 'forward' => $_GPC['forward'], 'ischeck' => $_GPC['ischeck'], 'ismoney' => $_GPC['ismoney'], 'sid' => $_GPC['sid'], 'parentid' => $_GPC['category']['parentid'], 'childid' => $_GPC['category']['childid'], 'wei_num' => trim($_GPC['wei_num']), 'wei_name' => trim($_GPC['wei_name']), 'wei_sex' => intval($_GPC['wei_sex']), 'wei_intro' => trim($_GPC['wei_intro']), 'wei_avatar' => trim($_GPC['wei_avatar']), 'wei_ewm' => trim($_GPC['wei_ewm']), 's_name' => trim($_GPC['s_name']), 'breaks' => trim($_GPC['breaks']), 'uid' => intval($_GPC['uid']), 'firmurl' => trim($_GPC['firmurl']), 'custom' => trim($_GPC['custom']));
		// 		var_dump($_GPC);
		// 		exit();
		if (!empty($id)) {
			pdo_update('enjoy_city_firm', $data, array('id' => $id));
			$message = "更新商户成功！";
		} else {
			$data['createtime'] = TIMESTAMP;
			pdo_insert('enjoy_city_firm', $data);
			$id = pdo_insertid();
			//pdo_update('enjoy_city_firm',array('fgid'=>$id),array('id' => $id));
			$message = "新增商户成功！";
		}
		message($message, $this->createWebUrl('firm', array('op' => 'display')), 'success');
	}
	//修改
	$firm = pdo_fetch("SELECT * FROM " . tablename('enjoy_city_firm') . " WHERE id = '{$id}' and uniacid = '{$_W['uniacid']}'");
	//付费情况
	$payment = pdo_fetch("select uniontid,fee,status from " . tablename('core_paylog') . " where tid=" . $id . "
		    and module='enjoy_city' and uniacid=" . $uniacid . "");
	// 	var_dump($payment);
	// 	exit();
	//默认为一年
	$firm['etime'] = empty($firm['etime']) ? date("Y-m-d H:i:s", TIMESTAMP + 365 * 24 * 60 * 60) : $firm['etime'];
	$firm['baidumap'] = array('lng' => $firm['location_x'], 'lat' => $firm['location_y']);
	//先取出活动详情
	$act = pdo_fetch("select * from " . tablename('enjoy_city_reply') . " where uniacid=" . $uniacid . "");
	$firm['province'] = empty($firm['province']) ? $act['province'] : $firm['province'];
	$firm['city'] = empty($firm['city']) ? $act['city'] : $firm['city'];
	$firm['district'] = empty($firm['district']) ? $act['district'] : $firm['district'];
} elseif ($op == 'delete') {
	$id = intval($_GPC['id']);
	$firm = pdo_fetch("SELECT id FROM " . tablename('enjoy_city_firm') . " WHERE id = '{$id}' AND uniacid=" . $_W['uniacid'] . "");
	if (empty($firm)) {
		message('抱歉，商户不存在或是已经被删除！', $this->createWebUrl('firm', array('op' => 'display')), 'error');
	}
	pdo_delete('enjoy_city_firm', array('id' => $id));
	message('商户删除成功！', $this->createWebUrl('firm', array('op' => 'display')), 'success');
} else {
	if ($op == 'ischeck') {
		// var_dump($_GPC);
		// exit();
		$ischeck = $_GPC['ischeck'];
		$id = $_GPC['id'];
		if ($ischeck == 0) {
			$ischeck = 1;
			$firm = pdo_fetch("SELECT * FROM " . tablename('enjoy_city_firm') . " WHERE id = '{$id}' AND uniacid=" . $_W['uniacid'] . "");
			$fopenid = pdo_fetchcolumn("select openid from " . tablename('enjoy_city_fans') . " where uid=" . $firm[uid] . " and
			uniacid=" . $uniacid . "");
			if (!empty($fopenid)) {
				//通过审核，发模板消息给商户
				$message = "恭喜,您的店铺通过审核了，请尽快完善店铺信息，以获取更多的展示效果";
				//插入成功后通知管理员审核
				require_once MB_ROOT . '/controller/weixin.class.php';
				$url = $this->str_murl($this->createMobileUrl("firm", array('fid' => $firm['id'])));
				$config = $this->module['config']['api'];
				$now = date('Y-m-d', TIMESTAMP);
				//echo $xxquan;
				$template = array('touser' => $fopenid, 'template_id' => $config['mid2'], 'url' => $url, 'topcolor' => '#743a3a', 'data' => array('first' => array('value' => urlencode('通过审核，请尽快完善店铺信息'), 'color' => '#007aff'), 'keyword1' => array('value' => urlencode($firm['title']), 'color' => '#007aff'), 'keyword2' => array('value' => urlencode('通过'), 'color' => '#007aff'), 'keyword3' => array('value' => urlencode($now), 'color' => '#007aff'), 'remark' => array('value' => urlencode($message), 'color' => '#007aff')));
				$api = $this->module['config']['api'];
				$weixin = new class_weixin($_W['account']['key'], $_W['account']['secret']);
				$weixin->send_template_message(urldecode(json_encode($template)));
			}
		} else {
			$ischeck = 0;
		}
		$resr = pdo_update('enjoy_city_firm', array('ischeck' => $ischeck), array('uniacid' => $uniacid, 'id' => $id));
		message('操作成功', $this->createWebUrl('firm'), 'success');
	} else {
		if ($op == 'rule') {
			$id = intval($_GPC['id']);
			$rule = 'F' . $id;
			//增加规则
			//先查询店铺是否存在
			$rcount = pdo_fetchcolumn("select count(*) from " . tablename('rule_keyword') . " where uniacid=" . $uniacid . " and 
			        content='" . $rule . "'");
			if ($rcount > 0) {
				message('店铺规则已存在', $this->createWebUrl('firm'), 'error');
			} else {
				//插入2张规则表
				$data1 = array('uniacid' => $uniacid, 'name' => $rule, 'module' => 'enjoy_city', 'status' => 1);
				pdo_insert('rule', $data1);
				$rid = pdo_insertid();
				$data2 = array('rid' => $rid, 'uniacid' => $uniacid, 'module' => 'enjoy_city', 'content' => $rule, 'type' => 1, 'status' => 1);
				$ekey = pdo_insert('rule_keyword', $data2);
				if ($ekey > 0) {
					pdo_update('enjoy_city_firm', array('rid' => $rid), array('id' => $id, 'uniacid' => $uniacid));
					message('增加店铺规则成功', $this->createWebUrl('firm'), 'success');
				}
			}
		} else {
			if ($op == 'UploadExcel') {
				if ($_GPC['leadExcel'] == "true") {
					$filename = $_FILES['inputExcel']['name'];
					$tmp_name = $_FILES['inputExcel']['tmp_name'];
					$flag = $this->checkUploadFileMIME($_FILES['inputExcel']);
					if ($flag == 0) {
						message('文件格式不对.');
					}
					if (empty($tmp_name)) {
						message('请选择要导入的Excel文件！');
					}
					$msg = $this->uploadFile($filename, $tmp_name, $_GPC);
					if ($msg == 1) {
						message('导入成功！', referer(), 'success');
					} else {
						message($msg, '', 'error');
					}
				}
			} else {
				if ($_GPC['op'] == 'excel') {
					//$userlist1=pdo_fetchall("select * from ".tablename('enjoy_red_fans')." where uniacid=".$uniacid." and total>0 order by total desc");
					$list = pdo_fetchall("SELECT a.*,b.name FROM " . tablename('enjoy_city_firm') . " as a left join " . tablename('enjoy_city_kind') . " as b on
					    a.childid=b.id WHERE a.uniacid = '{$_W['uniacid']}' and a.ispay>-1 ORDER BY a.createtime desc");
					$title = array('排序', '商户名', '起始时间', '终止时间', '一级分类', '二级分类', '省', '市', '区', '详细地址', '经度', '纬度', '电话', '简介', '优惠信息', '自定义按钮名称', '商品外链', 'logo图片', '横幅图片', '浏览次数', '转发次数', '老板微信号', '老板微信昵称', '老板性别', '老板头像', '老板二维码', '申请人姓名', '申请人UID', '老板个人简介');
					$arraydata[] = iconv("UTF-8", "GB2312//IGNORE", implode("\t", $title));
					foreach ($list as &$value) {
						$cash = $value['total'] - $value['cashed'];
						$tmp_value = array($value['hot'], $value['title'], $value['stime'], $value['etime'], $value['parentid'], $value['childid'], $value['province'], $value['city'], $value['district'], $value['address'], $value['location_x'], $value['location_y'], $value['tel'], $value['intro'], $value['custom'], $value['breaks'], $value['firmurl'], $value['icon'], $value['img'], $value['forward'], $value['browse'], $value['wei_num'], $value['wei_name'], $value['wei_sex'], $value['wei_avatar'], $value['wei_ewm'], $value['s_name'], $value['uid'], $value['wei_intro']);
						$arraydata[] = iconv("UTF-8", "GB2312//IGNORE", implode("\t", $tmp_value));
					}
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Type: application/vnd.ms-execl");
					header("Content-Type: application/force-download");
					header("Content-Type: application/download");
					header("Content-Disposition: attachment; filename=" . date('Ymd') . '.xls');
					header("Content-Transfer-Encoding: binary");
					header("Pragma: no-cache");
					header("Expires: 0");
					echo implode("\t\n", $arraydata);
					die;
				} else {
					message('请求方式不存在');
				}
			}
		}
	}
}
$basic = pdo_fetch("select * from " . tablename('enjoy_city_reply') . " where uniacid=0");
include $this->template('firm');