<?php

//*****************************************************
//************** SimpleOAuth Version 1.0 **************
//*****************************************************
//
//                                   作者: @To_aru_User
//
// OAuth.phpを利用しない非常にシンプルなライブラリです。
// cURLがインストールされていない環境でも利用できます。
// 使いかたはtwitteroauthと似ていますが、こちらは
//  ・通常のリクエスト
//  ・爆撃リクエスト(レスポンスを待機しない)
//  ・画像アップロードを伴うリクエスト
// 全てに対応しております。
// OAuthRequestImageメソッドのパラメータのうち、
// ファイルパスを表すもののキーの頭に
// 「@」を付けてください。(例：@media[] @image)
// 
//
// ●使用例
//  $consumer_key        = 'xxxxxxxxxx';
//  $consumer_secret     = 'yyyyyyyyyy';
//  $access_token        = 'zzzzzzzzzz';
//  $access_token_secret = 'wwwwwwwwww';
//  $to = new SimpleOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
//  $timeline = json_decode($to->OAuthRequest('https://api.twitter.com/1.1/statuses/home_timeline.json'));
//  $res = $to->OAuthRequest('https://api.twitter.com/1.1/statuses/update.json','POST',array('status'=>'てｓ'));
//  for ($i=1;$i<=10;$i++)
//    $to->OAuthRequest('https://api.twitter.com/1.1/statuses/update.json','POST',array('status'=>"爆撃{$i}回目"),false);
//  $res = $to->OAuthRequestImage('https://api.twitter.com/1.1/statuses/update_with_media.json',array('status'=>'てｓ','@media[]'=>'./testimage.png'));
//

class SimpleOAuth {
	
	private $consumer_key;
	private $consumer_secret;
	private $oauth_token;
	private $oauth_token_secret;
	private $oauth_verifier;
	
	public function __construct($consumer_key,$consumer_secret,$oauth_token='',$oauth_token_secret='',$oauth_verifier='') {
		$this->consumer_key          = $consumer_key;
		$this->consumer_secret       = $consumer_secret;
		$this->oauth_token           = $oauth_token;
		$this->oauth_token_secret    = $oauth_token_secret;
		$this->oauth_verifier        = $oauth_verifier;
	}
	
	public function OAuthRequest($url,$method='GET',$params=array(),$waitResponse=true) {
		$method = strtoupper($method);
		$element = self::getUriElements($url);
		if ($element===false)
			return false;
		parse_str($element['query'],$temp);
		$params += $temp;
		$content = $this->getParameters($url,$method,$params);
		if ($method==='GET')
			$element['path'] .= '?'.$content;
		$request  = '';
		$request .= $method.' '.$element['path'].' HTTP/1.1'."\r\n";
		$request .= 'Host: '.$element['host']."\r\n";
		$request .= 'User-Agent: SimpleOAuth'."\r\n";
		$request .= 'Connection: Close'."\r\n";
		if ($method==='POST') {
			$request .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";
			$request .= 'Content-Length: '.strlen($content)."\r\n";
		}
		$request .= "\r\n";
		if ($method==='POST')
			$request .= $content;
		return self::connect($element['host'],$element['scheme'],$request,$waitResponse);
	}
	
	public function OAuthRequestImage($url,$params=array(),$waitResponse=true) {
		$element = self::getUriElements($url);
		if ($element===false)
			return false;
		parse_str($element['query'],$temp);
		$params += $temp;
		$boundary = '------------------'.md5(time());
		$content = '';
		foreach ($params as $key => $value) {
			$content .= '--'.$boundary."\r\n";
			if (strpos($key,'@')===0) {
				$binary = @file_get_contents($value);
				$content .= 'Content-Disposition: form-data; name="'.substr($key,1).'"; filename="'.basename($value)."\"\r\n";
				$content .= 'Content-Type: '.self::getMimeType($binary)."\r\n";
				$content .= "\r\n";
				$content .= $binary."\r\n";
			} else {
				$content .= 'Content-Disposition: form-data; name="'.$key."\"\r\n";
				$content .= "\r\n";
				$content .= $value."\r\n";
			}
		}
		$content .= '--'.$boundary.'--';
		$request  = '';
		$request .= 'POST '.$element['path'].' HTTP/1.1'."\r\n";
		$request .= 'Host: '.$element['host']."\r\n";
		$request .= 'User-Agent: SimpleOAuth'."\r\n";
		$request .= 'Connection: Close'."\r\n";
		$request .= 'Authorization: OAuth '.$this->getParameters($url,'POST',array(),true)."\r\n";
		$request .= 'Content-Type: multipart/form-data; boundary='.$boundary."\r\n";
		$request .= 'Content-Length: '.strlen($content)."\r\n";
		$request .= "\r\n";
		$request .= $content;
		return self::connect($element['host'],$element['scheme'],$request,$waitResponse);
	}
	
	private function getParameters($url,$method='GET',$opt=array(),$asHeader=false) {
		$method = strtoupper($method);
		$enc = create_function('$s','return str_replace("%7E","~",rawurlencode($s));');
		$nsort = create_function('$a','uksort($a,"strnatcmp");return $a;');
		$toPairs = create_function('$a','$p=array();foreach($a as $k=>$v)$p[]=$k."=".$v;return $p;');
		$parameters = array(
			'oauth_consumer_key' => $this->consumer_key,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_nonce' => md5(microtime().mt_rand()),
			'oauth_version' => '1.0'
		);
		if (!empty($this->oauth_token)    && strpos($url,'oauth/request_token')===false)
			$opt['oauth_token']    = $this->oauth_token;
		if (!empty($this->oauth_verifier) && strpos($url,'oauth/access_token') !==false)
			$opt['oauth_verifier'] = $this->oauth_verifier;
		$parameters += $opt;
		$body = implode('&',array_map($enc,array($method,$url,implode('&',$toPairs($nsort(array_map($enc,$parameters)))))));
		$key = implode('&',array_map($enc,array($this->consumer_secret,empty($this->oauth_token_secret)?'':$this->oauth_token_secret)));
		$parameters['oauth_signature'] = base64_encode(hash_hmac('sha1',$body,$key,true));
		return implode(($asHeader)?', ':'&',$toPairs(array_map($enc,$parameters)));
	}
	
	private static function connect($host,$scheme,$request,$waitResponse=true) {
		if (strtolower($scheme)==='https') {
			$host = 'ssl://'.$host;
			$port = 443;
		} else {
			$port = 80;
		}
		$fp = @fsockopen($host,$port);
		if ($fp===false)
			return false;
		fwrite($fp,$request);
		$ret = '';
		if ($waitResponse) {
			ob_start();
			fpassthru($fp);
			$res = ob_get_clean();
			$res = explode("\r\n\r\n",$res,2);
			$ret = isset($res[1])?$res[1]:$res[0];
		}
		fclose($fp);
		return $ret;
	}
	
	private static function getUriElements($url) {
		$parsed = parse_url($url);
		if (empty($parsed) || !isset($parsed['host']))
			return false;
		$parsed['scheme'] = !isset($parsed['scheme']) ? 'http://' : $parsed['scheme'] ;
		$parsed['path']   = !isset($parsed['path'])   ? '/'       : $parsed['path']   ;
		$parsed['query']  = !isset($parsed['query'])  ? ''        : $parsed['query']  ;
		return $parsed;
	}
	
	private static function getMimeType($binary) {
		if (!preg_match('/(^\x00\x00\x01\x00)|(^\x89PNG\x0d\x0a\x1a\x0a)|(^GIF8[79]a)|(^\xff\xd8)|(^BM)/',$binary,$matches))
			return 'application/octet-stream';
		switch (true) {
			case (!empty($matches[1])):
				return 'image/x-icon';
			case (!empty($matches[2])):
				return 'image/png';
			case (!empty($matches[3])):
				return 'image/gif';
			case (!empty($matches[4])):
				return 'image/jpeg';
			default:
				return 'image/bmp';
		}
	}

}