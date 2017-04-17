<?php
if(!pdo_fieldexists('bm_qrsign_reply', 'message1')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `message1` varchar(100) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('bm_qrsign_reply', 'message2')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `message2` varchar(100) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('bm_qrsign_reply', 'message3')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `message3` varchar(100) NOT NULL DEFAULT '';");
}

if(!pdo_fieldexists('bm_qrsign_reply', 'pic_good')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `pic_good` varchar(100) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('bm_qrsign_reply', 'ismemo1')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `ismemo1` int(1) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('bm_qrsign_reply', 'ismemo2')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `ismemo2` int(1) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('bm_qrsign_reply', 'ismemo3')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `ismemo3` int(1) NOT NULL DEFAULT '0';");
}
  
if(!pdo_fieldexists('bm_qrsign_reply', 'linkurl')) {
	pdo_query("ALTER TABLE ".tablename('bm_qrsign_reply')." ADD `linkurl` varchar(200) NOT NULL DEFAULT ''");
}
