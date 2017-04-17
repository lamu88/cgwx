<?php
/*
 * 源码来自dede168资源网 www.dede168.com 交流群：97206582
 */
defined('IN_IA') or exit('Access Denied');
class Social_rewardModule extends WeModule {
	
	public function settingsDisplay($settings) {
		global $_GPC,$_W;
        load ()->func ( 'tpl' );
		if (checksubmit()) {

            $settings = $this->module['config'];
            $cfg['wxappid'] = $_GPC['wxappid'];
            $cfg['wxsecret'] = $_GPC['wxsecret'];
            
            
            $cfg['noticetype'] = $_GPC['noticetype'];
            $cfg['verify_notice'] = $_GPC['verify_notice'];
            $cfg['verify_custom'] = $_GPC['verify_custom'];
            $cfg['tip'] = $_GPC['tip'];
            $cfg['ftip'] = $_GPC['ftip'];
            $cfg['followqr'] = $_GPC['followqr'];
			if ($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		include $this->template('settings');
	}
}