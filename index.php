<?php
$file_path = "/etc/v2ray/config.json";
if(!isset($_GET["get"]) || !file_exists($file_path))
	exit("参数错误或配置文件不存在！");
$get=$_GET["get"];
$newdata=array();
$serverlist=array();
$data = file_get_contents($file_path);
$arr = json_decode($data,true);
$list=$arr["inbounds"];

//print_r($list);
foreach($list as $l)
{
	if($l["protocol"]=="vmess")
	{
		$v="2";
		$domain=$l["domain"];
		if($domain!=""){
			$add=$domain;
		}else{
			$add=getenv('SERVER_ADDR');
		}
		$port=$l["port"];
		$id=$l["settings"]["clients"][0]["id"];
		$aid=$l["settings"]["clients"][0]["alterId"];
		$net=$l["streamSettings"]["network"];
		if($net=="tcp"){
			$netset="tcpSettings";
		}
		if($net=="ws"){
			$netset="wsSettings";
		}
		if($net=="kcp"){
			$netset="kcpSettings";
		}
		if($net=="h2"){
			$netset="httpSettings";
		}
		if($net=="quic"){
			$netset="quicSettings";
		}
		$type=$l["streamSettings"][$netset]["header"]["type"];
		$host=$l["streamSettings"][$netset]["header"]["Host"];
		$path=$l["streamSettings"][$netset]["path"];
		$tls=$l["streamSettings"]["security"];
		
		if($type!="")
		{
			$typestr=$type;
		}else{
			$typestr="none";
		}
		$ps=$add."-".$net."-".$typestr."-".$port;
		if($v!="" && $ps!="" && $port!="" && $id!="" && $aid!="")
		{
		    $newdata['v']=$v;
		    $newdata['ps']=$ps;
		    $newdata['add']=$add;
		    $newdata['port']=$port;
		    $newdata['id']=$id;
		    $newdata['aid']=$aid;
		}
		else
		{
		    exit;
		}
		if($net!=""){
		    $newdata['net']=$net;
		}
		if($type!="")
		{
		    $newdata['type']=$type;
		}
		if($host!="")
		{
		    $newdata['host']=$host;
		}
		if($path!="")
		{   
		    $newdata['path']=$path;
		}
		if($tls!="")
		{
		    $newdata['tls']=$tls;
		}
		$json_string = json_encode($newdata);
		//print_r($json_string)."\r\n";
		$serverlist[]='vmess://'.base64_encode($json_string);
	}
}
if($get==0){
	$serverall;
	foreach($serverlist as $ser){
		$serverall=$serverall.$ser."\r\n";
	}
	echo(base64_encode($serverall));
}else{
	echo(base64_encode($serverlist[$get-1]));
}
?>