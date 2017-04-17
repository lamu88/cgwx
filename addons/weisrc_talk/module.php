<?php
defined('IN_IA') or exit('Access Denied');
include '../addons/weisrc_talk/model.php';
class weisrc_talkModule extends WeModule
{
    public $modulename = 'weisrc_talk';
    public function settingsDisplay($settings)
    {
        global $_GPC, $_W;
        load()->func('tpl');
        if (empty($settings['weisrc_talk'])) {
            $settings['weisrc_talk']['title']         = "约爱广场";
            $settings['weisrc_talk']['range']         = "1000";
            $settings['weisrc_talk']['ad_title']      = "约爱广场";
            $settings['weisrc_talk']['ad_image']      = "../addons/weisrc_talk/template/themes/images/topImg2.jpg";
            $settings['weisrc_talk']['share_title']   = "约爱广场";
            $settings['weisrc_talk']['share_image']   = "../addons/weisrc_talk/icon.jpg";
            $settings['weisrc_talk']['share_desc']    = "约爱广场";
            $settings['weisrc_talk']['copyright_url'] = "#";
        }
        if (checksubmit()) {
            $cfg                                 = $settings;
            $cfg['weisrc_talk']['title']         = trim($_GPC['title']);
            $cfg['weisrc_talk']['range']         = trim($_GPC['range']);
            $cfg['weisrc_talk']['ad_image']      = trim($_GPC['ad_image']);
            $cfg['weisrc_talk']['ad_title']      = trim($_GPC['ad_title']);
            $cfg['weisrc_talk']['ad_url']        = trim($_GPC['ad_url']);
            $cfg['weisrc_talk']['follow_url']    = trim($_GPC['follow_url']);
            $cfg['weisrc_talk']['share_title']   = trim($_GPC['share_title']);
            $cfg['weisrc_talk']['share_image']   = trim($_GPC['share_image']);
            $cfg['weisrc_talk']['share_cancel']  = trim($_GPC['share_cancel']);
            $cfg['weisrc_talk']['share_desc']    = trim($_GPC['share_desc']);
            $cfg['weisrc_talk']['share_url']     = trim($_GPC['share_url']);
            $cfg['weisrc_talk']['copyright']     = trim($_GPC['copyright']);
            $cfg['weisrc_talk']['copyright_url'] = trim($_GPC['copyright_url']);
            $cfg['weisrc_talk']['lng']           = trim($_GPC['baidumap']['lng']);
            $cfg['weisrc_talk']['lat']           = trim($_GPC['baidumap']['lat']);
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        include $this->template('setting');
    }
}