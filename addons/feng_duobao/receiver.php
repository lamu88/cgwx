<?php
/**
 * 一元夺宝模块订阅器
 *
 * dede168资源网 www.dede168.com 交流群：97206582
 */
defined('IN_IA') or exit('Access Denied');

class Feng_duobaoModuleReceiver extends WeModuleReceiver {
	public function receive() {
		$type = $this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微赞文档来编写你的代码
	}
}