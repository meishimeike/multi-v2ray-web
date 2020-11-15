<?php
header("Content-Type: text/html;charset=utf-8");
$config_path = "/etc/v2ray/config.json";

if(!isset($_GET["get"]) || !file_exists($config_path))
	exit("参数错误或配置文件不存在！");
else
	echo Get_Code(file_get_contents($config_path),$_GET["get"]);

function get_server_ip() {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_connect($sock,"8.8.8.8", 53);
    socket_getsockname($sock, $name); // $name passed by reference
    return $name;
}

function Get_Code($date,$get)
{
	//$newdata=array();
	$serverlist=array();	
	$arr = json_decode($date,true);
	$list=$arr["inbounds"];
	foreach($list as $l)
	{
	    $newdata=array();
	    if(isset($l["domain"])){
			$add=$l["domain"];
		}else{
			$add=get_server_ip();
		}
		$port=$l["port"];
		
		switch ($l["protocol"])
		{
			case "vmess":
				$v="2";
				$id=$l["settings"]["clients"][0]["id"];
				$aid=$l["settings"]["clients"][0]["alterId"];
				$net=$l["streamSettings"]["network"];
				switch ($net)
				{
					case "tcp":			
						$tls=$l["streamSettings"]["security"];
						$type=$l["streamSettings"]["tcpSettings"]["header"]["type"];
						//$host=$l["streamSettings"]["tcpSettings"]["header"]["request"]["headers"]["Host"];
						//$path=$l["streamSettings"]["tcpSettings"]["header"]["request"]["path"][0];
						break;  
					case "ws":
						$tls=$l["streamSettings"]["security"];
						$type=$l["streamSettings"]["wsSettings"]["headers"]["type"];
						$host=$l["streamSettings"]["wsSettings"]["headers"]["Host"];
						$path=$l["streamSettings"]["wsSettings"]["path"];
						break;
					case "h2":
						$tls=$l["streamSettings"]["security"];
						$type=$l["streamSettings"]["httpSettings"]["headers"]["type"];
						$host=$l["streamSettings"]["httpSettings"]["headers"]["Host"];
						$path=$l["streamSettings"]["httpSettings"]["path"];
						break;
					case "kcp":
						$tls=$l["streamSettings"]["security"];
						$type=$l["streamSettings"]["kcpSettings"]["header"]["type"];
						$host=$l["streamSettings"]["kcpSettings"]["security"];
						$path=$l["streamSettings"]["kcpSettings"]["key"];
						break;
					case "quic":
						$tls=$l["streamSettings"]["security"];
						$type=$l["streamSettings"]["quicSettings"]["header"]["type"];
						$host=$l["streamSettings"]["quicSettings"]["security"];
						$path=$l["streamSettings"]["quicSettings"]["key"];
						break;
					default:
				}
			
				$ps=$add."-".$net."-".$type."-".$port;
				$newdata['v']=$v;
				$newdata['ps']=$ps;
				$newdata['add']=$add;
				$newdata['port']=$port;
				$newdata['id']=$id;
				$newdata['aid']=$aid;
				$newdata['net']=$net;
				$newdata['tls']=$tls;
				if(isset($type)&&$type!="")
					$newdata['type']=$type;
				if(isset($host)&&$host!="")
					$newdata['host']=$host;
				if(isset($path)&&$path!="")
					$newdata['path']=$path;				
				$json_string = json_encode($newdata);
				$serverlist[]='vmess://'.base64_encode($json_string);
				//print_r($newdata)."\r\n";
				break;
			case "trojan":
			    $passwd=$l["settings"]["clients"][0]["password"];
			    $serverlist[]='trojan://'.$passwd.'@'.$add.":".$port.'#trojan-'.$add.'-'.$port;
				break;
			case "shadowsocks":
			    $passwd=$l["settings"]["password"];
			    $method=$l["settings"]["method"];
			    $serverlist[]='ss://'.base64_encode($method.':'.$passwd.'@'.$add.':'.$port.'#ss-'.$add.'-'.$port);
			    break;
			case "socks":
			    if(isset($l["settings"]["auth"]) && $l["settings"]["auth"]=="password"){
			        $user=$l["settings"]["accounts"][0]["user"];
			        $pass=$l["settings"]["accounts"][0]["pass"];
			        $serverlist[]='tg://socks?server='.$add.'&'.'port='.$port.'&user='.$user.'&pass='.$pass.'#socks5-'.$add.'-'.$port;
			    }else{
			        $serverlist[]='tg://socks?server='.$add.'&'.'port='.$port.'#'.$add.'-'.$port;
			    }
			    break;
			case "mtproto":
			    $secret=$l["settings"]["users"][0]["secret"];
			    $serverlist[]='tg://socks?server='.$add.'&'.'port='.$port.'&secret='.$secret.'#mtproto-'.$add.'-'.$port;
			    break;
			default:
		}
	}
	if($get==0){
		$serverall;
		foreach($serverlist as $ser){
			$serverall=$serverall.$ser."\r\n";
		}
		return base64_encode($serverall);
	}else{
		return base64_encode($serverlist[$get-1]);
	}
}
?>
