<?php
	error_reporting(E_ALL);
	set_time_limit(0);		
	//tokey、apikey：用户注册、登陆后在第三方开发页面可以查到
	$token=$_REQUEST['num'];
	$apikey=$_REQUEST['appkey'];
	//打印机背面有，用户要在打印机管理中登记
	$dtuid=$_REQUEST['code'];
	$imei=$_REQUEST['secret'];
	
	// \n换行，超过示例中的宽度会自动换行
		$senddata=$_REQUEST['printContent'];		
		SendPrintData($token,$apikey,$dtuid,$imei,$senddata);
//加错误提示，比如连接不成功提示
function SendPrintData($token,$apikey,$dtuid,$imei,$senddata)
{
	$port = 14999;
	$ip="120.24.219.107";
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

	if ($socket >= 0)
	{
		$result = socket_connect($socket, $ip, $port);
		if ($result >= 0)
		{
			$senddata = iconv("utf-8","gb2312//IGNORE",$senddata);  //转码，打印机用的是GB2312
			//把参加连成字符串,前后有{}
			//用数组再转成字符串时，汉字会变成ascii码
			//你看看有没有更好的方法
			$data='{"request":"17",';
			$data.='"token":"'.$token.'",';
			$data.='"api_key":"'.$apikey.'",';
			$data.='"id":"'.$dtuid.'",';
			$data.='"imei":"'.$imei.'",';
			$data.='"context":"'.$senddata.'"}';
			
			$buf=PackageWebHead();
			$buf=SetPacketLen($buf, 12+strlen($data));
			$buf=implode($buf);  //buf数组转成字符串
			$senddata=$buf.$data;  //合并

			if(socket_write($socket, $senddata, strlen($senddata)))
			{
				while($ReturnData = socket_read($socket, 8192,PHP_BINARY_READ))
				{
					//返回结果：&q8400{"request": "17", "request_code": "117"}
					//117表示成功
				}
			}
		}
	}
	socket_close($socket);
}

//加0-3位前缀
function PackageWebHead()
{
	$buf[0]=decode(0x19, '&#');
	$buf[1]=decode(0x26, '&#');
	$buf[2]=decode(0x72, '&#');
	$buf[3]=decode(0x01, '&#');
	return $buf;
}
//加4-11位前缀
function SetPacketLen($buf, $length)
{
	$buf[4] = $length%10+'0';
	$length /= 10;
	$buf[5] = $length%10+'0';
	$length /= 10;
	$buf[6] = $length%10+'0';
	$length /= 10;	
	$buf[7] = $length%10+'0';
	$length /= 10;	
	$buf[8] = $length%10+'0';
	$length /= 10;
	$buf[9] = $length%10+'0';
	$length /= 10;
	$buf[10] = $length%10+'0';
	$length /= 10;	
	$buf[11] = $length%10+'0';
	return $buf;
}
/**
* 将ascii码转为字符串
* @param type $str 要解码的字符串
* @param type $prefix 前缀，默认:&#
* @return type
*/
function decode($str, $prefix="&#")
{
	$str = str_replace($prefix, "", $str);
	$a = explode(";", $str);
	$utf='';
	foreach ($a as $dec)
	{
		if ($dec < 128)
		{
			$utf .= chr($dec);
		} 
		else if ($dec < 2048)
		{
			$utf .= chr(192 + (($dec - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		} 
		else 
		{
			$utf .= chr(224 + (($dec - ($dec % 4096)) / 4096));
			$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		}
	}
	return $utf;
}


?>