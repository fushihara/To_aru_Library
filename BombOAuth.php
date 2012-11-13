<?php

//*************************************************
//************* BombOAuth Version 1.0 *************
//*************************************************
//
//　　　　　　　　　　　　　　作者: @To_aru_User
//
// OAuthのPOSTリクエストを、レスポンスを回収せずに
// 高速にループさせたいときに役立つライブラリです。
//
//●使用例
//
// $consumer_key = 'xxxxxxxxxxxxxxx';
// $consumer_secret = 'yyyyyyyyyyyyyy';
// $access_token = 'zzzzzzzzzzzzzzz';
// $access_token_secret = 'wwwwwwwwwwwwww';
//
// $to = new BombOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
// for ($i=1;$i<=50;$i++)
//   $to->sockRequest('http://api.twitter.com/1.1/statuses/update.json',array('status'=>'超速爆撃'.str_repeat('★',$i)));
//

class BombOAuth {
	
	private $consumer_key;
	private $consumer_secret;
	private $access_token;
	private $access_token_secret;
	
	public function __construct($consumer_key,$consumer_secret,$access_token,$access_token_secret) {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->access_token = $access_token;
		$this->access_token_secret = $access_token_secret;
	}
	
	public function sockRequest($url,$opt=array()) {
		$query = $this->getQuery($url,$opt);
		$parsed = parse_url($url);
		if ($parsed!==false && !empty($parsed['host'])) {
			if (empty($parsed['path'])) $parsed['path'] = '/';
			if (empty($parsed['scheme'])) $parsed['scheme'] = 'http';
			$port = ($parsed['scheme']==='https') ? 443 : 80;
			$host = ($port===443) ? 'ssl://'.$parsed['host'] : $parsed['host'];
			$fp = @fsockopen($host,$port);
			if ($fp!==false) {
				$request  = 'POST '.$parsed['path'].' HTTP/1.1'."\r\n";
				$request .= 'Host: '.$parsed['host']."\r\n";
				$request .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";
				$request .= 'Content-Length: '.strlen($query)."\r\n\r\n";
				$request .= $query;
				fwrite($fp,$request);
				fclose($fp);
			}
		}
	}
	
	private function getQuery($url,$opt=array()) {
		$enc = create_function('$s','return str_replace("%7E","~",rawurlencode($s));');
		$nsort = create_function('$a','uksort($a,"strnatcmp");return $a;');
		$toPairs = create_function('$a','$p=array();foreach($a as $k=>$v)$p[]=$k."=".$v;return $p;');
		$parameters = array(
			'oauth_consumer_key' => $this->consumer_key,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_nonce' => md5(microtime().mt_rand()),
			'oauth_token' => $this->access_token,
			'oauth_version' => '1.0'
		);
		$parameters += $opt;
		$body = implode('&',array_map($enc,array('POST',$url,implode('&',$toPairs($nsort(array_map($enc,$parameters)))))));
		$key = implode('&',array_map($enc,array($this->consumer_secret,$this->access_token_secret)));
		$parameters['oauth_signature'] = base64_encode(hash_hmac('sha1',$body,$key,true));
		return implode('&',$toPairs(array_map($enc,$parameters)));
	}

}