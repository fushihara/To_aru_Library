<?php

//*************************************************
//************** BgOAuth Version 2.0 **************
//*************************************************
//
//　　　　　　　　　　　　　　作者: @To_aru_User
//　　　　　　　　　　　　　　協力: @re4k
//
//バックグラウンドOAuth認証(疑似XAuth認証)ライブラリ
//
//
//●使用例
//
// $consumer_key = 'xxxxxxxxxxxxxxx';
// $consumer_secret = 'yyyyyyyyyyyyyy';
// $username = 'hoge';
// $password = 'fuga';
//
// $app = new BgOAuth($consumer_key,$consumer_secret);
// $tokens = $app->getTokens($username,$password);
//
// 成功すると、$tokens['access_token']・$tokens['access_token_secret']でアクセスできます。
// 失敗すると、エラー原因を表す文字列が返されます。
//
//
//●更新履歴
//
// 2.0
// ・内部的な構造を美しくした
//   Content-Lengthをヘッダに含むようにした
//
// 1.2.1
// ・authenticityTokenにスラッシュが含まれる場合にエラーが発生する可能性があったので修正
//
// 1.2
// ・NOTICEエラーを回避
//
// 1.1
// ・PIN入力方式にも対応
//
// 1.0.1
// ・Private宣言を忘れていたクラス内変数があったので修正
//
//

class BgOAuth {
	
	private $cookie;
	private $consumer_key;
	private $consumer_secret;
	private $oauth_token;
	private $oauth_token_secret;
	private $oauth_verifier;
	private $error;
	
	private $url_authorize     = 'https://api.twitter.com/oauth/authorize';
	private $url_request_token = 'https://api.twitter.com/oauth/request_token';
	private $url_access_token  = 'https://api.twitter.com/oauth/access_token';
	
	public function __construct($consumer_key,$consumer_secret) {
		$this->cookie = array();
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}
	
	public function getTokens($username,$password) {
		$data = $this->prepare();
		if ($data===false) return $this->error;
		$data['session[username_or_email]'] = $username;
		$data['session[password]'] = $password;
		$response = $this->request($this->url_authorize,'POST',$data);
		if ($response===false) {
			$this->error = 'ログイン時、サーバーから応答がありませんでした';
			return $this->error;
		}
		$pattern = '@oauth_verifier=(.+?)"|<code>(.+?)</code>@';
		if (!preg_match($pattern,$response,$matches)) {
			$this->error = 'oauth_verifierの取得に失敗しました';
			return $this->error;
		}
		$this->oauth_verifier = (!empty($matches[1])) ? $matches[1] : $matches[2];
		$q = $this->getQuery($this->url_access_token);
		$response = $this->request($this->url_access_token.'?'.$q);
		if ($response===false) {
			$this->error = 'access_token取得時、サーバーから応答がありませんでした';
			return $this->error;
		}
		parse_str($response,$oauth_tokens);
		return array(
			'access_token' => $oauth_tokens['oauth_token'],
			'access_token_secret' => $oauth_tokens['oauth_token_secret']
		);
	}
	
	private function prepare() {
		$q = $this->getQuery($this->url_request_token);
		$response = $this->request($this->url_request_token.'?'.$q);
		if ($response===false) {
			$this->error = 'request_token取得時、サーバーから応答がありませんでした';
			return false;
		}
		parse_str($response,$request_tokens);
		$this->oauth_token = $request_tokens['oauth_token'];
		$this->oauth_token_secret = $request_tokens['oauth_token_secret'];
		$q = 'force_login=true&oauth_token='.$request_tokens['oauth_token'];
		$response = $this->request($this->url_authorize.'?'.$q);
		if ($response===false) {
			$this->error = 'ログインページへの遷移時、サーバーから応答がありませんでした';
			return false;
		}
		$pattern = '@<input name="authenticity_token" type="hidden" value="(.+?)" />@';
		if (!preg_match($pattern,$response,$matches)) {
			$this->error = 'authenticity_tokenの取得に失敗しました';
			return false;
		}
		return array(
			'authenticity_token' => $matches[1],
			'oauth_token' => $this->oauth_token,
			'force_login' => '1'
		);
	}
	
	private function request($url,$method='GET',$data=array()) {
		$method = strtoupper($method);
		$toPairs = create_function('$a','$p=array();foreach($a as $k=>$v)$p[]=$k."=".$v;return $p;');
		$toLines = create_function('$a','return implode("\r\n",$a);');
		$http = array();
		$temp = array();
		$temp[] = 'Cookie: '.implode('; ',$toPairs($this->cookie));
		if ($method==='POST') {
			$http['content'] = http_build_query($data,'','&');
			$temp[] = 'Content-Type: application/x-www-form-urlencoded';
			$temp[] = 'Content-Length: '.strlen($http['content']);
		}
		$http['user_agent'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:18.0) Gecko/18.0 Firefox/18.0 FirePHP/0.7.1';
		$http['header'] = $toLines($temp);
		$http['method'] = $method;
		$response = @file_get_contents($url,false,stream_context_create(array('http'=>$http)));
		if ($response===false) return false;
		foreach ($http_response_header as $line) {
			if (strpos($line,'Set-Cookie')!==0) continue;
			$temp = explode(':',$line,2);
			$all = $temp[1];
			$parts = explode(';',$all);
			foreach ($parts as $part) {
				$part = trim($part);
				if (strpos($part,'=')<1 || substr_count($part,'=')!=1) continue;
				list($key,$value) = explode('=',$part,2);
				if (in_array($key,array('expires','path','domain','secure'))) continue;
				$this->cookie[$key] = $value;
			}
		}
		return $response;
	}
	
	private function getQuery($url,$method='GET',$opt=array()) {
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
		if (!empty($this->oauth_token)) $opt['oauth_token'] = $this->oauth_token;
		if (!empty($this->oauth_verifier)) $opt['oauth_verifier'] = $this->oauth_verifier;
		$parameters += $opt;
		$body = implode('&',array_map($enc,array($method,$url,implode('&',$toPairs($nsort(array_map($enc,$parameters)))))));
		$key = implode('&',array_map($enc,array($this->consumer_secret,empty($this->oauth_token_secret)?'':$this->oauth_token_secret)));
		$parameters['oauth_signature'] = base64_encode(hash_hmac('sha1',$body,$key,true));
		return implode('&',$toPairs(array_map($enc,$parameters)));
	}
	
}