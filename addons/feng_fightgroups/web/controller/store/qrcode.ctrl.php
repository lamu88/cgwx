<?php
/**
 */

defined('IN_IA') or exit('Access Denied');

$url = $_GPC['url'];
require(IA_ROOT . '/framework/library/qrcode/phpqrcode.php');
$errorCorrectionLevel = "L";
$matrixPointSize = "5";
QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
exit();
