<?php
defined('IN_IA') or exit('Access Denied');
define(EARTH_RADIUS, 6371);
define('RES', '../addons/weisrc_talk/template');
include 'model.php';
class weisrc_talkModuleSite extends WeModuleSite
{
    public $modulename = 'weisrc_talk';
    public $_appid = '';
    public $_appsecret = '';
    public $_accountlevel = '';
    public $_debug = '1';
    public $_weixin = '0';
    public $_pagesize = 18;
    public $info_page_size = 6;
    public $_weid = '';
    public $_fromuser = '';
    public $_nickname = '';
    public $_headimgurl = '';
    public $_auth2_openid = '';
    public $_auth2_nickname = '';
    public $_auth2_headimgurl = '';
    public $_auth2_sex = '';
    public $_auth2_key = '';
    public $table_chat = 'weisrc_talk_chat';
    public $table_fans = 'weisrc_talk_fans';
    function __construct()
    {
        global $_GPC, $_W;
        $this->_weid     = $_W['uniacid'];
        $this->_fromuser = $_W['fans']['from_user'];
        if ($_SERVER['HTTP_HOST'] == '127.0.0.1') {
            $this->_fromuser = 'debug';
        } else {
            code_compare($this->_auth2_key, authorization());
        }
        $this->_appid            = '';
        $this->_appsecret        = '';
        $this->_accountlevel     = $_W['account']['level'];
        $this->_auth2_openid     = 'auth2_openid_' . $_W['uniacid'];
        $this->_auth2_nickname   = 'auth2_nickname_' . $_W['uniacid'];
        $this->_auth2_headimgurl = 'auth2_headimgurl_' . $_W['uniacid'];
        $this->_auth2_sex        = 'auth2_sex_' . $_W['uniacid'];
        if (isset($_COOKIE[$this->_auth2_openid])) {
            $this->_fromuser = $_COOKIE[$this->_auth2_openid];
        }
        if ($this->_accountlevel < 4) {
            $setting = uni_setting($this->_weid);
            $oauth   = $setting['oauth'];
            if (!empty($oauth) && !empty($oauth['account'])) {
                $this->_account   = account_fetch($oauth['account']);
                $this->_appid     = $this->_account['key'];
                $this->_appsecret = $this->_account['secret'];
            }
        } else {
            $this->_appid     = $_W['account']['key'];
            $this->_appsecret = $_W['account']['secret'];
        }
    }
    public function doMobileGetLocation()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $title     = "约爱广场";
        $config    = $this->module['config']['weisrc_talk'];
        $title     = $config['title'];
        load()->model('mc');
        $userinfo   = mc_fansinfo($from_user);
        $nickname   = $userinfo['nickname'];
        $headimgurl = $userinfo['tag']['avatar'];
        $gender     = $userinfo['tag']['sex'];
        if (empty($from_user)) {
            message('授权失败!,请点击用户授权！');
        }
        if (empty($headimgurl)) {
            $userinfo = mc_oauth_userinfo();
            if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
                $headimgurl = $userinfo['avatar'];
                $gender     = $userinfo['sex'];
                $nickname   = $userinfo['nickname'];
            }
        }
        $user = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . "  WHERE weid = :weid  AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user,
            ':weid' => $weid
        ));
        if (empty($user)) {
            $data = array(
                'weid' => $weid,
                'from_user' => $from_user,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,
                'sex' => $gender,
                'age' => 0,
                'lasttime' => TIMESTAMP,
                'dateline' => TIMESTAMP
            );
            pdo_insert($this->table_fans, $data);
        } else {
            $data = array(
                'weid' => $weid,
                'from_user' => $from_user,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,
                'sex' => $gender,
                'visit' => $user['visit'] + 1,
                'lasttime' => TIMESTAMP
            );
            if (!empty($user['nickname'])) {
                unset($data['nickname']);
                unset($data['headimgurl']);
            }
            pdo_update($this->table_fans, $data, array(
                'from_user' => $from_user
            ));
        }
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('getlocation');
    }
    public function doMobileSetLocation()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $lat       = trim($_GPC['lat']);
        $lng       = trim($_GPC['lng']);
        $data      = array(
            'lat' => $lat,
            'lng' => $lng
        );
        pdo_update($this->table_fans, $data, array(
            'from_user' => $from_user
        ));
        $result = array(
            'isResultTrue' => 'true',
            'resultMsg' => '操作成功'
        );
        echo json_encode($result);
    }
    public function IsRange($curlat, $curlng)
    {
        $curlat = floatval($curlat);
        $curlng = floatval($curlng);
        if ($curlat <= 0) {
            return 0;
        }
        $config   = $this->module['config']['weisrc_talk'];
        $range    = intval($config['range']);
        $distance = $this->getDistance($curlat, $curlng, $config['lat'], $config['lng']);
        if ($distance > $range) {
            return 0;
        }
        return 1;
    }
    public function doMobileIndex()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $title     = "约爱广场";
        $lat       = trim($_GPC['lat']);
        $lng       = trim($_GPC['lng']);
        $pagesize  = $this->_pagesize;
        $config    = $this->module['config']['weisrc_talk'];
        $title     = $config['title'];
        load()->model('mc');
        $userinfo   = mc_fansinfo($from_user);
        $nickname   = $userinfo['nickname'];
        $headimgurl = $userinfo['tag']['avatar'];
        $gender     = $userinfo['tag']['sex'];
        if (empty($from_user) || empty($nickname)) {
        }
        $user        = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . "  WHERE weid = :weid  AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user,
            ':weid' => $weid
        ));
        $fans        = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . "  WHERE weid = :weid ORDER BY displayorder DESC, lasttime DESC,dateline DESC LIMIT " . $pagesize, array(
            ':weid' => $weid
        ));
        $total       = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . "  WHERE weid = :weid ", array(
            ':weid' => $weid
        ));
        $pagesize    = ceil($total / $pagesize);
        $noreadcount = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_chat) . " WHERE weid=:weid AND to_user=:from_user AND status=0", array(
            ':weid' => $weid,
            ':from_user' => $from_user
        ));
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('index');
    }
    public function doMobileGetgoToMyMsg()
    {
        global $_GPC, $_W;
        $weid        = $this->_weid;
        $from_user   = $this->_fromuser;
        $config      = $this->module['config']['weisrc_talk'];
        $title       = $config['title'];
        $noreadcount = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_chat) . " WHERE weid=:weid AND to_user=:from_user AND status=0", array(
            ':weid' => $weid,
            ':from_user' => $from_user
        ));
        $condition   = " WHERE weid = {$weid} AND status<>0 ";
        $curuser     = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user
        ));
        if (empty($curuser)) {
            message('用户不存在!');
        }
        $chats       = pdo_fetchall("select a.id AS id,a.from_user as from_user,count(1) AS count,a.content AS content,a.dateline AS dateline,b.headimgurl as headimgurl,b.nickname AS nickname,b.sex AS sex,b.lat AS lat,b.lng AS lng from (select * from " . tablename($this->table_chat) . " WHERE weid=:weid AND to_user=:from_user  ORDER BY dateline DESC) a INNER JOIN " . tablename($this->table_fans) . " b ON a.from_user=b.from_user GROUP BY a.from_user order by a.dateline DESC", array(
            ':weid' => $weid,
            ':from_user' => $from_user
        ));
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('getgoToMyMsg');
    }
    public function doMobileGetnearMyInfo()
    {
        global $_GPC, $_W;
        $weid        = $this->_weid;
        $from_user   = $this->_fromuser;
        $config      = $this->module['config']['weisrc_talk'];
        $title       = $config['title'];
        $condition   = " WHERE weid = {$weid} AND status<>0 ";
        $user        = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user
        ));
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('getnearMyInfo');
    }
    public function doMobileGetnearTaInfo()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $userid    = trim($_GPC['userid']);
        $config    = $this->module['config']['weisrc_talk'];
        $title     = $config['title'];
        $condition = " WHERE weid = {$weid} AND status<>0 ";
        $user      = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $userid
        ));
        if (empty($user)) {
            message('用户不存在!');
        }
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('getnearTaInfo');
    }
    public function doMobileGetnearTalk()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $userid    = trim($_GPC['userid']);
        if ($from_user == $userid) {
            message('不能和自己聊天');
        }
        $config    = $this->module['config']['weisrc_talk'];
        $title     = $config['title'];
        $pagesize  = $this->info_page_size;
        $condition = " WHERE weid = {$weid} AND status<>0 ";
        $curuser   = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user
        ));
        $talkuser  = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $userid
        ));
        if (empty($curuser) || empty($talkuser)) {
            message('用户不存在!');
        }
        $chats     = pdo_fetchall("SELECT * FROM " . tablename($this->table_chat) . " WHERE weid=:weid AND ((from_user=:from_user AND to_user=:to_user) OR (from_user=:to_user AND to_user=:from_user)) ORDER BY dateline DESC LIMIT " . $pagesize, array(
            ':weid' => $weid,
            ':from_user' => $from_user,
            ':to_user' => $userid
        ));
        $total     = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_chat) . " WHERE weid=:weid AND ((from_user=:from_user AND to_user=:to_user) OR (from_user=:to_user AND to_user=:from_user )) ", array(
            ':weid' => $weid,
            ':from_user' => $from_user,
            ':to_user' => $userid
        ));
        $pagecount = ceil($total / $pagesize);
        $chats     = array_reverse($chats, false);
        pdo_update($this->table_chat, array(
            'status' => 1
        ), array(
            'weid' => $weid,
            'from_user' => $userid,
            'to_user' => $from_user
        ));
        $share_image = tomedia($config['share_image']);
        $share_title = empty($config['share_title']) ? $config['title'] : $config['share_title'];
        $share_desc  = empty($config['share_desc']) ? $config['title'] : $config['share_desc'];
        $share_url   = empty($setting['share_url']) ? $_W['siteroot'] . 'app/' . $this->createMobileUrl('getlocation', array(), true) : $setting['share_url'];
        include $this->template('getnearTalk');
    }
    public function doMobileGetnearTalkToMore()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $userid    = trim($_GPC['userid']);
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize  = $this->info_page_size;
        $condition = " WHERE weid = {$weid} AND status<>0 ";
        $curuser   = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user
        ));
        $talkuser  = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $userid
        ));
        $chats     = pdo_fetchall("SELECT * FROM " . tablename($this->table_chat) . " WHERE weid=:weid AND ((from_user=:from_user AND to_user=:to_user) OR (from_user=:to_user AND to_user=:from_user )) ORDER BY dateline DESC LIMIT " . ($pageindex - 1) * $pagesize . ',' . $pagesize, array(
            ':weid' => $weid,
            ':from_user' => $from_user,
            ':to_user' => $userid
        ));
        $total     = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_chat) . " WHERE weid=:weid AND ((from_user=:from_user AND to_user=:to_user) OR (from_user=:to_user AND to_user=:from_user )) ", array(
            ':weid' => $weid,
            ':from_user' => $from_user,
            ':to_user' => $userid
        ));
        $pagecount = ceil($total / $pagesize);
        $chats     = array_reverse($chats, false);
        $str       = '';
        foreach ($chats as $key => $value) {
            if ($value['from_user'] != $from_user) {
                $str .= '<div class="ta">' . '<img src="' . tomedia($talkuser['headimgurl']) . '" alt="" class="taHead" onerror="this.src=\'resource/images/noavatar_middle.gif\'">' . '<div class="taCon">' . '<div class="sanL"></div>' . '<div class="taConR">' . $this->rep($value['content']) . '</div>' . '<div class="clearB"></div>' . '<span class="textMini" style="margin-left: 12px">' . date('Y-m-d H:i', $value['dateline']) . '</span>' . '</div>' . '<div class="clearB"></div>' . ' </div>';
            } else {
                $str .= '<div class="me">' . '<a href="' . $this->createMobileurl('getnearMyInfo', array(), true) . '" class="meHead">' . '<img src="' . tomedia($curuser['headimgurl']) . '" alt="" class="taHead" onerror="this.src=\'resource/images/noavatar_middle.gif\'"></a>' . '<div class="meCon">' . '<div class="sanR"></div>' . '<div class="meConL">' . $this->rep($value['content']) . '</div>' . '<div class="clearB"></div>' . '<span class="textMini" style="margin-right: 12px;float: right;">' . date('Y-m-d H:i', $value['dateline']) . '</span>' . '</div>' . '<div class="clearB"></div>' . ' </div>';
            }
        }
        $result = array(
            'isResultTrue' => 1,
            'resultMsg' => $str,
            'pagesize' => $pagecount
        );
        echo json_encode($result);
    }
    public function doMobileGetNearTalkInfo()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $userid    = trim($_GPC['userid']);
        $condition = " WHERE weid = {$weid} AND status<>0 ";
        $curuser   = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $from_user
        ));
        $talkuser  = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " {$condition} AND from_user=:from_user LIMIT 1", array(
            ':from_user' => $userid
        ));
        $chats     = pdo_fetchall("SELECT * FROM " . tablename($this->table_chat) . " WHERE weid=:weid AND status=0 AND from_user=:to_user AND to_user=:from_user ORDER BY dateline DESC LIMIT 3", array(
            ':weid' => $weid,
            ':from_user' => $from_user,
            ':to_user' => $userid
        ), 'id');
        $user      = array_keys($chats);
        pdo_query('UPDATE ' . tablename($this->table_chat) . " SET status=1 WHERE id IN ('" . implode("','", is_array($user) ? $user : array(
            $user
        )) . "')");
        $str = '';
        foreach ($chats as $key => $value) {
            $str .= '<div class="ta">' . '<img src="' . tomedia($talkuser['headimgurl']) . '" alt="" class="taHead" onerror="this.src=\'resource/images/noavatar_middle.gif\'">' . '<div class="taCon">' . '<div class="sanL"></div>' . '<div class="taConR">' . $this->rep($value['content']) . '</div>' . '<div class="clearB"></div>' . '<span class="textMini" style="margin-left: 12px">' . date('Y-m-d H:i', $value['dateline']) . '</span>' . '</div>' . '<div class="clearB"></div>' . ' </div>';
        }
        $result = array(
            'isResultTrue' => 1,
            'resultMsg' => $str
        );
        echo json_encode($result);
    }
    function rep($content)
    {
        $arr     = array(
            "[笑哈哈]",
            "[得瑟]",
            "[得意地笑]",
            "[转圈]",
            "[挤地铁]",
            "[我忍了]",
            "[粉爱你]",
            "[粉红兔火车]",
            "[转圈圈]",
            "[鼓掌]",
            "[压力]",
            "[抢镜]",
            "[草泥马]",
            "[神马]",
            "[多云]",
            "[给力]",
            "[围观]",
            "[v5]",
            "[小熊猫]",
            "[粉红兔微笑]",
            "[动感光波]",
            "[囧]",
            "[互粉]",
            "[礼物]",
            "[微笑]",
            "[呲牙笑]",
            "[大笑]",
            "[羞羞]",
            "[小可怜]",
            "[抠鼻孔]",
            "[惊讶]",
            "[大眼睛羞涩]",
            "[吐舌头]",
            "[闭嘴]",
            "[鄙视]",
            "[爱你哦]",
            "[泪牛满面]",
            "[偷笑]",
            "[嘴一个]",
            "[生病]",
            "[装可爱]",
            "[切~]",
            "[右不屑]",
            "[左不屑]",
            "[嘘]",
            "[雷人]",
            "[呕吐]",
            "[委屈]",
            "[装可爱]",
            "[再见]",
            "[疑问]",
            "[困]",
            "[money]",
            "[装酷]",
            "[色眯眯]",
            "[ok]",
            "[good]",
            "[nonono]",
            "[赞一个]",
            "[弱]"
        );
        $faceUrl = RES . '/themes/images/faces/';
        foreach ($arr as $key => $value) {
            if (strpos($content, $value) || strstr($content, $value)) {
                $img     = '<img style="width:30px;height:30px;" src="' . $faceUrl . ($key + 1) . '.png" />';
                $content = str_replace($value, $img, $content);
            }
        }
        return $content;
    }
    public function doMobileAddNearTalk()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $acceptid  = trim($_GPC['acceptid']);
        $senderid  = trim($_GPC['senderid']);
        $content   = trim($_GPC['content']);
        $data      = array(
            'weid' => $weid,
            'from_user' => $senderid,
            'to_user' => $acceptid,
            'content' => $content,
            'status' => 0,
            'dateline' => TIMESTAMP
        );
        pdo_insert('weisrc_talk_chat', $data);
        $tofans  = pdo_fetch("SELECT * FROM " . tablename('weisrc_talk_fans') . " WHERE weid=:weid AND from_user=:from_user LIMIT 1", array(
            ':weid' => $weid,
            ':from_user' => $acceptid
        ));
        $time    = TIMESTAMP - $tofans['chattime'];
        $content = "您有现场交友的新消息";
        $content .= '\n<a href=\"' . $_W['siteroot'] . 'app' . str_replace('./', '/', $this->createMobileurl('getgoToMyMsg', array(), true)) . '\">点击查看</a>';
        $content .= "\n(微信规定，每月最多可推送4条消息，您也可点击底部任意菜单，获取自点击时间起48小时内的不限制推送数)";
        if ($tofans['chattime'] == 0 || $time > 1800) {
            pdo_update('weisrc_talk_fans', array(
                'chattime' => TIMESTAMP
            ), array(
                'id' => $tofans['id']
            ));
            if ($this->_accountlevel == 4) {
                $this->sendText($acceptid, htmlspecialchars_decode($content));
            }
        }
    }
    private function sendText($openid, $content)
    {
        $send['touser']  = trim($openid);
        $send['msgtype'] = 'text';
        $send['text']    = array(
            'content' => urlencode($content)
        );
        $acc             = WeAccount::create();
        $data            = $acc->sendCustomNotice($send);
        return $data;
    }
    public function doMobileUpdateUserseat()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $from_user = $this->_fromuser;
        $age       = intval($_GPC['age']);
        $height    = intval($_GPC['height']);
        $weight    = intval($_GPC['weight']);
        $zy        = trim($_GPC['zy']);
        $xs        = trim($_GPC['xs']);
        $data      = array(
            'age' => $age,
            'height' => $height,
            'weight' => $weight,
            'job' => $zy,
            'content' => $xs
        );
        pdo_update($this->table_fans, $data, array(
            'from_user' => $from_user,
            'weid' => $weid
        ));
        $result = array(
            'isResultTrue' => 1,
            'resultMsg' => 'debug'
        );
        echo json_encode($result);
    }
    public function doMobileGetFindSex()
    {
        global $_GPC, $_W;
        $weid      = $this->_weid;
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize  = $this->_pagesize;
        $condition = " WHERE weid = {$weid} AND status<>0";
        $type      = intval($_GPC['type']);
        if ($type != 0) {
            if ($type == 1) {
                $condition .= " AND sex=1 ";
            } else {
                $condition .= " AND sex<>1 ";
            }
        }
        $fans      = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " {$condition} ORDER BY displayorder DESC, lasttime DESC,dateline DESC LIMIT " . ($pageindex - 1) * $pagesize . ',' . $pagesize);
        $total     = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . " {$condition} ");
        $pagecount = ceil($total / $pagesize);
        $str       = '';
        $flag      = true;
        $closediv  = false;
        $itemcount = 1;
        foreach ($fans as $key => $item) {
            $itemindex = $itemcount % 3;
            if ($itemindex == 1) {
                if ($flag == true) {
                    $class = "list_white";
                } else {
                    $class = "list_lan";
                }
                $str .= '<div class="' . $class . '">';
                $flag     = !$flag;
                $closediv = true;
            }
            if ($item['sex'] == 1) {
                $border = 'borderGG';
                $text   = 'textGG';
            } else {
                $border = 'borderMM';
                $text   = 'textMM';
            }
            $headimgurl = tomedia($item['headimgurl']);
            $from_user  = $item['from_user'];
            $nickname   = $item['nickname'];
            $str .= '<div class="user">
                <div class="head ' . $border . '">
                    <img src="' . $headimgurl . '" alt=""
                         onclick="readNum(\'' . $from_user . '\')" onerror="this.src=\'resource/images/noavatar_middle.gif\'"/>
                </div>
                <br/>
                <span class="' . $text . '">' . $nickname . '</span><br/>
                <span class="time">' . $this->format_date($item['lasttime']) . '</span>
            </div>';
            if ($itemindex == 0) {
                $str .= '</div>';
                $closediv = false;
            }
            $itemcount++;
        }
        if ($closediv == true) {
            $str .= '</div>';
        }
        $result = array(
            'isResultTrue' => 1,
            'resultMsg' => $str,
            'pagesize' => $pagecount
        );
        echo json_encode($result);
    }
    function format_date($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int) $k)) {
                return $c . $v . '前';
            }
        }
        return '刚刚';
    }
    public function domobileversion()
    {
        message($this->curversion);
    }
    public function oauth2($url)
    {
        global $_GPC, $_W;
        load()->func('communication');
        $code = $_GPC['code'];
        if (empty($code)) {
            message('code获取失败.');
        }
        $token     = $this->getAuthorizationCode($code);
        $from_user = $token['openid'];
        $userinfo  = $this->getUserInfo($from_user);
        $sub       = 1;
        if ($userinfo['subscribe'] == 0) {
            $sub     = 0;
            $authkey = intval($_GPC['authkey']);
            if ($authkey == 0) {
                $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->_appid . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
                header("location:$oauth2_code");
            }
            $userinfo = $this->getUserInfo($from_user, $token['access_token']);
        }
        if (empty($userinfo) || !is_array($userinfo) || empty($userinfo['openid']) || empty($userinfo['nickname'])) {
            echo '<h1>2获取微信公众号授权失败[无法取得userinfo], 请稍后重试！ 公众平台返回原始数据为: <br />' . $sub . $userinfo['meta'] . '<h1>';
            exit;
        }
        setcookie($this->_auth2_headimgurl, $userinfo['headimgurl'], TIMESTAMP + 3600 * 24);
        setcookie($this->_auth2_nickname, $userinfo['nickname'], TIMESTAMP + 3600 * 24);
        setcookie($this->_auth2_openid, $from_user, TIMESTAMP + 3600 * 24);
        setcookie($this->_auth2_sex, $userinfo['sex'], TIMESTAMP + 3600 * 24);
        return $userinfo;
    }
    public function getUserInfo($from_user, $ACCESS_TOKEN = '')
    {
        if ($ACCESS_TOKEN == '') {
            $ACCESS_TOKEN = $this->getAccessToken();
            $url          = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$ACCESS_TOKEN}&openid={$from_user}&lang=zh_CN";
        } else {
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$ACCESS_TOKEN}&openid={$from_user}&lang=zh_CN";
        }
        $json     = ihttp_get($url);
        $userInfo = @json_decode($json['content'], true);
        return $userInfo;
    }
    public function getAuthorizationCode($code)
    {
        $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->_appid}&secret={$this->_appsecret}&code={$code}&grant_type=authorization_code";
        $content     = ihttp_get($oauth2_code);
        $token       = @json_decode($content['content'], true);
        if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
            echo '<h1>1获取微信公众号授权' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'] . '<h1>';
            exit;
        }
        return $token;
    }
    public function getAccessToken()
    {
        global $_W;
        $account = $_W['account'];
        if (is_array($account['access_token']) && !empty($account['access_token']['token']) && !empty($account['access_token']['expire']) && $account['access_token']['expire'] > TIMESTAMP) {
            return $account['access_token']['token'];
        } else {
            if (empty($account['acid'])) {
                message('参数错误.');
            }
            if (empty($account['key']) || empty($account['secret'])) {
                message('请填写公众号的appid及appsecret, (需要你的号码为微信服务号)！', url('account/bind/post', array(
                    'acid' => $account['acid'],
                    'uniacid' => $account['uniacid']
                )), 'error');
            }
            $url     = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$account['key']}&secret={$account['secret']}";
            $content = ihttp_get($url);
            if (empty($content)) {
                message('获取微信公众号授权失败, 请稍后重试！');
            }
            $token = @json_decode($content['content'], true);
            if (empty($token) || !is_array($token)) {
                message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: <br />' . $token);
            }
            if (empty($token['access_token']) || empty($token['expires_in'])) {
                message('解析微信公众号授权失败, 请稍后重试！');
            }
            $record              = array();
            $record['token']     = $token['access_token'];
            $record['expire']    = TIMESTAMP + $token['expires_in'];
            $row                 = array();
            $row['access_token'] = iserializer($record);
            pdo_update('account_wechats', $row, array(
                'acid' => $account['acid']
            ));
            return $record['token'];
        }
    }
    public function getCode($url)
    {
        global $_W;
        $url         = urlencode($url);
        $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->_appid}&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=0#wechat_redirect";
        header("location:$oauth2_code");
    }
    public $curversion = '';
    public function squarePoint($lng, $lat, $distance = 0.5)
    {
        $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance / EARTH_RADIUS;
        $dlat = rad2deg($dlat);
        return array(
            'left-top' => array(
                'lat' => $lat + $dlat,
                'lng' => $lng - $dlng
            ),
            'right-top' => array(
                'lat' => $lat + $dlat,
                'lng' => $lng + $dlng
            ),
            'left-bottom' => array(
                'lat' => $lat - $dlat,
                'lng' => $lng - $dlng
            ),
            'right-bottom' => array(
                'lat' => $lat - $dlat,
                'lng' => $lng + $dlng
            )
        );
    }
    function getDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $radLat1 = $lat1 * M_PI / 180;
        $radLat2 = $lat2 * M_PI / 180;
        $a       = $lat1 * M_PI / 180 - $lat2 * M_PI / 180;
        $b       = $lng1 * M_PI / 180 - $lng2 * M_PI / 180;
        $s       = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s       = $s * EARTH_RADIUS;
        $s       = round($s * 1000);
        if ($len_type > 1) {
            $s /= 1000;
        }
        $s /= 1000;
        return round($s, $decimal);
    }
    function isWeixin()
    {
        if ($this->_weixin == 1) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (!strpos($userAgent, 'MicroMessenger')) {
                include $this->template('s404');
                exit();
            }
        }
    }
    function https_request($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'ERROR ' . curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }
    public function doWebfans()
    {
        global $_W, $_GPC;
        $weid      = $this->_weid;
        $url       = $this->createWebUrl('fans', array(
            'op' => 'display'
        ));
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->table_fans, array(
                        'displayorder' => $displayorder
                    ), array(
                        'id' => $id
                    ));
                }
                message('排序更新成功！', $url, 'success');
            }
            $pindex    = max(1, intval($_GPC['page']));
            $psize     = 10;
            $condition = " WHERE weid = {$weid} ";
            $list      = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " $condition ORDER BY displayorder DESC, lasttime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            if (!empty($list)) {
                $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . " $condition");
                $pager = pagination($total, $pindex, $psize);
            }
            $totalcount = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . " $condition");
            $mancount   = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . " $condition AND sex=1");
            $womancount = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_fans) . " $condition AND sex<>1");
            $todaycount = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename($this->table_fans) . " $condition AND date_format(from_UNIXTIME(`lasttime`),'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')");
        } elseif ($operation == 'post') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(
                    ':id' => $id
                ));
                if (empty($item)) {
                    message('抱歉，数据不存在或是已经删除！！', '', 'error');
                }
            } else {
                $item = array(
                    'dateline' => TIMESTAMP,
                    'status' => 1
                );
            }
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => intval($_W['uniacid']),
                    'nickname' => trim($_GPC['nickname']),
                    'content' => trim($_GPC['content']),
                    'status' => intval($_GPC['status']),
                    'displayorder' => intval($_GPC['displayorder']),
                    'lat' => trim($_GPC['baidumap']['lat']),
                    'lng' => trim($_GPC['baidumap']['lng']),
                    'dateline' => TIMESTAMP
                );
                if (!empty($_GPC['headimgurl'])) {
                    $data['headimgurl'] = $_GPC['headimgurl'];
                    load()->func('file');
                    file_delete($_GPC['headimgurl-old']);
                }
                if (empty($data['nickname'])) {
                    message('请输入昵称！');
                }
                if (empty($id)) {
                    pdo_insert($this->table_fans, $data);
                } else {
                    unset($data['dateline']);
                    pdo_update($this->table_fans, $data, array(
                        'id' => $id
                    ));
                }
                message('数据更新成功！', $url, 'success');
            }
        } elseif ($operation == 'delete') {
            $id  = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(
                ':id' => $id
            ));
            if (empty($row)) {
                message('抱歉，数据不存在或是已经被删除！');
            }
            pdo_delete($this->table_fans, array(
                'id' => $id
            ));
            message('删除成功！', $url, 'success');
        } elseif ($operation == 'check') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->table_fans, array(
                        'displayorder' => $displayorder
                    ), array(
                        'id' => $id
                    ));
                }
                message('排序更新成功！', $url, 'success');
            }
            $pindex    = max(1, intval($_GPC['page']));
            $psize     = 10;
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }
            if (!empty($_GPC['category_id'])) {
                $cid = intval($_GPC['category_id']);
                $condition .= " AND pcate = '{$cid}'";
            }
            if (isset($_GPC['status'])) {
                $condition .= " AND status = '" . intval($_GPC['status']) . "'";
            }
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_fans) . " WHERE weid = '{$_W['uniacid']}' AND mode=1 $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            if (!empty($list)) {
                $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_fans) . " WHERE weid = '{$_W['uniacid']}' $condition");
                $pager = pagination($total, $pindex, $psize);
            }
        } elseif ($operation == 'deleteall') {
            $rowcount    = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $feedback = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(
                        ':id' => $id
                    ));
                    if (empty($feedback)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_fans, array(
                        'id' => $id,
                        'weid' => $_W['uniacid']
                    ));
                    $rowcount++;
                }
            }
            $this->message("操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!", '', 0);
        } elseif ($operation == 'checkall') {
            $rowcount    = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $feedback = pdo_fetch("SELECT * FROM " . tablename($this->table_fans) . " WHERE id = :id", array(
                        ':id' => $id
                    ));
                    if (empty($feedback)) {
                        $notrowcount++;
                        continue;
                    }
                    $data = empty($feedback['status']) ? 1 : 0;
                    pdo_update($this->table_fans, array(
                        'status' => $data
                    ), array(
                        "id" => $id,
                        "weid" => $_W['uniacid']
                    ));
                    $rowcount++;
                }
            }
            $this->message("操作成功！共审核{$rowcount}条数据,{$notrowcount}条数据不能删除!!", '', 0);
        }
        load()->func('tpl');
        include $this->template('fans');
    }
    public $_code = "111000 110010 110111 110100 110010 111001 110111";
    public function doMobilesv()
    {
        echo $this->_code;
    }
    public function checkDatetime($str, $format = "H:i")
    {
        $str_tmp   = date('Y-m-d') . ' ' . $str;
        $unixTime  = strtotime($str_tmp);
        $checkDate = date($format, $unixTime);
        if ($checkDate == $str) {
            return 1;
        } else {
            return 0;
        }
    }
    public function message($error, $url = '', $errno = -1)
    {
        $data          = array();
        $data['errno'] = $errno;
        if (!empty($url)) {
            $data['url'] = $url;
        }
        $data['error'] = $error;
        echo json_encode($data);
        exit;
    }
}