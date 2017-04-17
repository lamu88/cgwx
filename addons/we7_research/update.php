<?php
if(pdo_fieldexists('research', 'is_sms')) {
	pdo_query("ALTER TABLE  ".tablename('research')." CHANGE `is_sms` `is_sms` int(11) NOT NULL DEFAULT '0';");
}

if(pdo_fieldexists('research', 'sms_id')) {
	pdo_query("ALTER TABLE  ".tablename('research')." CHANGE `sms_id` `sms_id` int(11) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('research', 'url')) {
	pdo_query("ALTER TABLE ".tablename('research')." ADD `url` varchar(100) NOT NULL;");
}


if(!pdo_fieldexists('research_rows', 'from_tuijian')) {
	pdo_query("ALTER TABLE ".tablename('research_rows')." ADD `from_tuijian` varchar(50) NOT NULL DEFAULT '0' COMMENT '推荐人ID';");
}