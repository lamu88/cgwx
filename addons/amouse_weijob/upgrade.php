<?php
$sql="CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) NOT NULL,
  `from_user` varchar(50) DEFAULT '' COMMENT '在微信端添加公司',
  `title` varchar(50) NOT NULL COMMENT '公司名称',
  `short` varchar(10) NOT NULL COMMENT '公司简称',
  `thumb` varchar(255) NOT NULL COMMENT '公司缩略图',
  `thumb1` varchar(255) NOT NULL COMMENT '公司缩略图',
  `linkman` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人',
  `tel` varchar(255) NOT NULL COMMENT '固定电话',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `qq` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL COMMENT '简历投递邮箱',
  `content` text NOT NULL COMMENT '公司简介',
  `companyorder` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `province` varchar(50) NOT NULL COMMENT '省',
  `city` varchar(50) NOT NULL COMMENT '市',
  `dist` varchar(50) NOT NULL COMMENT '区',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `lng` varchar(255) NOT NULL COMMENT '经度',
  `lat` varchar(255) NOT NULL COMMENT '纬度',
  `createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_employ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) unsigned NOT NULL,
  `companyid` int(11) unsigned NOT NULL COMMENT '公司id',
  `jobname` varchar(50) NOT NULL COMMENT '岗位名称',
  `hits` int(11) NOT NULL DEFAULT '0',
  `type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '工作类型：1为全职 2为兼职 3为实习',
  `description` text NOT NULL COMMENT '岗位描述',
  `employorder` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `status` int(1) NOT NULL DEFAULT '0',
  `number` int(11) unsigned NOT NULL COMMENT '人数',
  `edu` varchar(11) DEFAULT '不限' COMMENT '学历要求：不限，初中，高中，中专，大专，本科，硕士，博士',
  `offer` int(11) NOT NULL DEFAULT '0' COMMENT '薪水 0为面议',
  `workplace` varchar(100) NOT NULL COMMENT '工作地点',
  `workyear` varchar(50) NOT NULL COMMENT '工作年限',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL COMMENT '发布时间',
  `location_p` varchar(100) NOT NULL DEFAULT '' COMMENT '省',
  `location_c` varchar(100) NOT NULL DEFAULT '' COMMENT '省',
  `location_a` varchar(100) NOT NULL DEFAULT '' COMMENT '省',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) unsigned NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `hits` int(11) NOT NULL DEFAULT '0',
  `from_user` varchar(100) NOT NULL,
  `type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '4求职',
  `description` text NOT NULL COMMENT '个人介绍',
  `status` int(1) NOT NULL DEFAULT '0',
  `sex` int(11) NOT NULL DEFAULT '0' COMMENT '性别 0男 1女',
  `mobile` varchar(100) NOT NULL COMMENT '联系电话',
  `age` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `work` varchar(50) NOT NULL COMMENT '期望工作',
  `edu` varchar(50) NOT NULL COMMENT '学历',
  `salary` varchar(50) NOT NULL COMMENT '期望工资',
  `addr` varchar(50) NOT NULL COMMENT '期望地点',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `companyid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_resume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) NOT NULL,
  `from_user` varchar(100) NOT NULL,
  `name` varchar(20) DEFAULT NULL COMMENT '简历姓名',
  `sex` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '性别，0为男1为女',
  `age` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `major` varchar(20) NOT NULL DEFAULT '' COMMENT '专业',
  `college` varchar(10) DEFAULT NULL COMMENT '毕业院校',
  `home` varchar(255) DEFAULT NULL COMMENT '生源地',
  `skill` varchar(255) DEFAULT NULL COMMENT '专业技能',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `status` varchar(10) DEFAULT NULL COMMENT '政治面貌',
  `self` varchar(255) NOT NULL DEFAULT '' COMMENT '自我评价',
  `experience` varchar(255) NOT NULL DEFAULT '' COMMENT '工作经历',
  `education` varchar(255) NOT NULL DEFAULT '' COMMENT '教育经历',
  `job_direction` varchar(255) NOT NULL DEFAULT '' COMMENT '求职方向',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_amouse_weijob_resume_recod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weid` int(11) unsigned NOT NULL DEFAULT '0',
  `jobid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '投简工作id',
  `cvid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '简历id',
  `from_user` varchar(100) NOT NULL DEFAULT '0',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '投简时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='投简管理';
";
pdo_run($sql);
if(!pdo_fieldexists('amouse_weijob_company',  'id')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `id` int(11) NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `weid` int(11) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'from_user')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `from_user` varchar(50) DEFAULT '' COMMENT '在微信端添加公司';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'title')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `title` varchar(50) NOT NULL COMMENT '公司名称';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'short')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `short` varchar(10) NOT NULL COMMENT '公司简称';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'thumb')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `thumb` varchar(255) NOT NULL COMMENT '公司缩略图';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'thumb1')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `thumb1` varchar(255) NOT NULL COMMENT '公司缩略图';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'linkman')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `linkman` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'tel')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `tel` varchar(255) NOT NULL COMMENT '固定电话';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'phone')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'qq')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `qq` varchar(255) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'status')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `status` int(1) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'email')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `email` varchar(100) NOT NULL COMMENT '简历投递邮箱';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'content')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `content` text NOT NULL COMMENT '公司简介';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'companyorder')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `companyorder` int(11) unsigned DEFAULT '0' COMMENT '排序';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'province')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `province` varchar(50) NOT NULL COMMENT '省';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'city')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `city` varchar(50) NOT NULL COMMENT '市';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'dist')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `dist` varchar(50) NOT NULL COMMENT '区';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'address')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `address` varchar(255) NOT NULL COMMENT '地址';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'lng')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `lng` varchar(255) NOT NULL COMMENT '经度';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'lat')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `lat` varchar(255) NOT NULL COMMENT '纬度';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `createtime` int(11) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `weid` int(11) unsigned NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'companyid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `companyid` int(11) unsigned NOT NULL COMMENT '公司id';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'jobname')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `jobname` varchar(50) NOT NULL COMMENT '岗位名称';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'hits')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `hits` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'type')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '工作类型：1为全职 2为兼职 3为实习';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'description')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `description` text NOT NULL COMMENT '岗位描述';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'employorder')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `employorder` int(11) unsigned DEFAULT '0' COMMENT '排序';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'status')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `status` int(1) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'number')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `number` int(11) unsigned NOT NULL COMMENT '人数';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'edu')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `edu` varchar(11) DEFAULT '不限' COMMENT '学历要求：不限，初中，高中，中专，大专，本科，硕士，博士';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'offer')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `offer` int(11) NOT NULL DEFAULT '0' COMMENT '薪水 0为面议';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'workplace')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `workplace` varchar(100) NOT NULL COMMENT '工作地点';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'workyear')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `workyear` varchar(50) NOT NULL COMMENT '工作年限';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `endtime` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `createtime` int(11) NOT NULL COMMENT '发布时间';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'location_p')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `location_p` varchar(100) NOT NULL DEFAULT '' COMMENT '省';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'location_c')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `location_c` varchar(100) NOT NULL DEFAULT '' COMMENT '省';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'location_a')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `location_a` varchar(100) NOT NULL DEFAULT '' COMMENT '省';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `weid` int(11) unsigned NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'name')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `name` varchar(50) NOT NULL COMMENT '姓名';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'hits')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `hits` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'from_user')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `from_user` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'type')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '4求职';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'description')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `description` text NOT NULL COMMENT '个人介绍';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'status')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `status` int(1) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'sex')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `sex` int(11) NOT NULL DEFAULT '0' COMMENT '性别 0男 1女';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'mobile')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `mobile` varchar(100) NOT NULL COMMENT '联系电话';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'age')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `age` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '年龄';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'work')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `work` varchar(50) NOT NULL COMMENT '期望工作';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'edu')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `edu` varchar(50) NOT NULL COMMENT '学历';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'salary')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `salary` varchar(50) NOT NULL COMMENT '期望工资';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'addr')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `addr` varchar(50) NOT NULL COMMENT '期望地点';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `endtime` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `createtime` int(11) NOT NULL COMMENT '发布时间';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `rid` int(11) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'companyid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `companyid` int(11) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `weid` int(11) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'from_user')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `from_user` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('amouse_weijob_company',  'name')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `name` varchar(20) DEFAULT NULL COMMENT '简历姓名';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'sex')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `sex` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '性别，0为男1为女';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'age')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `age` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '年龄';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'major')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `major` varchar(20) NOT NULL DEFAULT '' COMMENT '专业';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'college')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `college` varchar(10) DEFAULT NULL COMMENT '毕业院校';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'home')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `home` varchar(255) DEFAULT NULL COMMENT '生源地';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'skill')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `skill` varchar(255) DEFAULT NULL COMMENT '专业技能';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'phone')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'email')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `email` varchar(255) DEFAULT NULL COMMENT '邮箱';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'status')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `status` varchar(10) DEFAULT NULL COMMENT '政治面貌';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'self')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `self` varchar(255) NOT NULL DEFAULT '' COMMENT '自我评价';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'experience')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `experience` varchar(255) NOT NULL DEFAULT '' COMMENT '工作经历';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'education')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `education` varchar(255) NOT NULL DEFAULT '' COMMENT '教育经历';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'job_direction')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `job_direction` varchar(255) NOT NULL DEFAULT '' COMMENT '求职方向';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `createtime` int(11) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `weid` int(11) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'jobid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `jobid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '投简工作id';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'cvid')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `cvid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '简历id';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'from_user')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `from_user` varchar(100) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('amouse_weijob_company',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('amouse_weijob_company')." ADD `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '投简时间';");
}

?>