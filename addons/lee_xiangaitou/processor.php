<?php
//decode by QQ:3213288469 http://www.dede168.com/
defined('IN_IA')or exit('Access Denied');class Lee_xiangaitouModuleProcessor extends WeModuleProcessor {public function respond(){global $_W,$_GPC;$uniacid =$_W['uniacid'];$rid =$this->rule;$content =$this->message['content'];$from_user =$this->message['from'];$xgt=pdo_fetch("select * from ".tablename('lee_xiangaitou')." where rid=:rid",array(":rid"=>$rid));if(!empty($xgt)){if(TIMESTAMP<$xgt['starttime']){return $this->respText("活动未开始");}elseif(TIMESTAMP>$xgt['endtime']){return $this->respText("活动已结束");}else{$news =array ();$news [] =array ('title' => $xgt['new_title'], 'description' =>$xgt['new_content'], 'picurl' =>$xgt ['new_pic'], 'url' => $this->createMobileUrl ('index',array('from_user'=>$from_user,'xgtid'=>$xgt['id'])));return $this->respNews ($news );}}else{return $this->respText("掀盖头活动删除或不存在");}}}