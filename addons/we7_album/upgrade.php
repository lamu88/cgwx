<?php
$sql="CREATE TABLE IF NOT EXISTS `ims_album` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `content` varchar(1000) NOT NULL DEFAULT '',
  `displayorder` int(10) unsigned NOT NULL DEFAULT '0',
  `pcate` int(11) unsigned NOT NULL DEFAULT '0',
  `ccate` int(11) unsigned NOT NULL DEFAULT '0',
  `isview` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_album_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `thumb` varchar(255) NOT NULL COMMENT '分类图片',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级',
  `description` varchar(500) NOT NULL COMMENT '分类介绍',
  `displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_album_photo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `albumid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `ispreview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `displayorder` int(10) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_albumid` (`albumid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `ims_album_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL,
  `albumid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
";
pdo_run($sql);
if(!pdo_fieldexists('album',  'id')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('album',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `weid` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('album',  'title')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `title` varchar(50) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'thumb')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `thumb` varchar(255) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'content')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `content` varchar(1000) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `displayorder` int(10) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'pcate')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `pcate` int(11) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'ccate')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `ccate` int(11) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'isview')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `isview` tinyint(1) unsigned NOT NULL DEFAULT '1';");
}
if(!pdo_fieldexists('album',  'type')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `type` tinyint(1) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `createtime` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('album',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号';");
}
if(!pdo_fieldexists('album',  'name')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `name` varchar(50) NOT NULL COMMENT '分类名称';");
}
if(!pdo_fieldexists('album',  'thumb')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `thumb` varchar(255) NOT NULL COMMENT '分类图片';");
}
if(!pdo_fieldexists('album',  'parentid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级';");
}
if(!pdo_fieldexists('album',  'description')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `description` varchar(500) NOT NULL COMMENT '分类介绍';");
}
if(!pdo_fieldexists('album',  'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序';");
}
if(!pdo_fieldexists('album',  'enabled')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启';");
}
if(!pdo_fieldexists('album',  'weid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `weid` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('album',  'albumid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `albumid` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('album',  'title')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `title` varchar(100) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'description')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `description` varchar(1000) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'attachment')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `attachment` varchar(255) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('album',  'ispreview')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `ispreview` tinyint(1) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `displayorder` int(10) unsigned NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('album',  'createtime')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `createtime` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('album',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `rid` int(11) NOT NULL;");
}
if(!pdo_fieldexists('album',  'albumid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD `albumid` int(11) NOT NULL;");
}
if(!pdo_indexexists('album',  'idx_albumid')) {
	pdo_query("ALTER TABLE ".tablename('album')." ADD KEY `idx_albumid` (`albumid`);");
}
?>