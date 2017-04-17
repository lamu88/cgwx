<?php

defined('IN_IA') or die('Access Denied');
class Enjoy_cityModuleReceiver extends WeModuleReceiver
{
	public function receive()
	{
		$type = $this->message['type'];
	}
}