<?php

//**************************************************
//************ BgOAuthMulti Version 1.0 ************
//**************************************************
//
//                                作者: @To_aru_User
//
// BgOAuthで高速に複数のログインを行うためのライブラリです。
// BgOAuth.php,BgOAuthMultiExec.phpを同一階層に設置してください。
// cURLがサポートされている必要があります。
//
//●使用例
//
// $consumer_key    = 'xxxxxxxxxxxxxxx';
// $consumer_secret = 'yyyyyyyyyyyyyyy';
// $m = new BgOAuthMulti();
// $m->addLogin($consumer_key,$consumer_secret,'username1','password1');
// $m->addLogin($consumer_key,$consumer_secret,'username2','password2');
// $m->addLogin($consumer_key,$consumer_secret,'username3','invalid');
// $res = $m->exec();
//
//●実行結果($resの構成)
//
// $res = array(
//   [0] => array(
//     'consumer_key'        => 'xxxxxxxxxxxxxxx',
//     'consumer_secret'     => 'yyyyyyyyyyyyyyy',
//     'username'            => 'username1',
//     'password'            => 'password1',
//     'access_token'        => 'zzzzzzzzzzzzzzz',
//     'access_token_secret' => 'wwwwwwwwwwwwwww'
//   ),
//   [1] => array(
//     'consumer_key'        => 'xxxxxxxxxxxxxxx',
//     'consumer_secret'     => 'yyyyyyyyyyyyyyy',
//     'username'            => 'username2',
//     'password'            => 'password2',
//     'access_token'        => 'zzzzzzzzzzzzzzz',
//     'access_token_secret' => 'wwwwwwwwwwwwwww'
//   ),
//   [2] => array(
//     'consumer_key'        => 'xxxxxxxxxxxxxxx',
//     'consumer_secret'     => 'yyyyyyyyyyyyyyy',
//     'username'            => 'username3',
//     'password'            => 'invalid',
//     'error'               => 'oauth_verifierの取得に失敗しました'
//   )
// );
//
// addLoginでBgOAuthMultiインスタンス内部のBgOAuthObjectsという配列に
// 'consumer_key','consumer_secret','username','password' のキーから成る
// 配列が追加されます。
// execメソッドを実行すると、BgOAuthObjects配列中の各配列に、
// 'access_token'と'access_token_secret' または
// 'error' のキーが追加されて返されます。
//

class BgOAuthMulti {
	
	private $BgOAuthObjects;
	private $BgOAuthMultiExecPath = 'BgOAuthMultiExec.php';
	
	public function __construct() {
		$this->BgOAuthObjects = array();
		$this->path = self::getPath().$this->BgOAuthMultiExecPath;
	}
	
	public function addLogin($consumer_key,$consumer_secret,$username,$password) {
		$this->BgOAuthObjects[] = array(
			'consumer_key'    => $consumer_key,
			'consumer_secret' => $consumer_secret,
			'username'        => $username,
			'password'        => $password
		);
	}
	
	public function exec() {
		if (empty($this->BgOAuthObjects))
			return array();
		$count = count($this->BgOAuthObjects);
		$chs = array();
		for ($i=0;$i<$count;$i++) {
			$query = http_build_query(array('BgOAuthObject'=>$this->BgOAuthObjects[$i]),'','&');
			$chs[$i] = curl_init();
			curl_setopt($chs[$i],CURLOPT_URL,$this->path);
			curl_setopt($chs[$i],CURLOPT_POST,true);
			curl_setopt($chs[$i],CURLOPT_POSTFIELDS,$query);
			curl_setopt($chs[$i],CURLOPT_RETURNTRANSFER,true);
		}
		$mh = curl_multi_init();
		foreach ($chs as $ch)
			curl_multi_add_handle($mh,$ch);
		$active = 0;
		do {
			curl_multi_exec($mh,$active);
		} while ($active>0);
		foreach ($chs as $i => $ch) {
			if (!curl_error($ch)) {
				parse_str(curl_multi_getcontent($ch),$parsed);
				if (isset($parsed['error'])) {
					$this->BgOAuthObjects[$i]['error'] = $parsed['error'];
				} elseif (isset($parsed['access_token']) && isset($parsed['access_token_secret'])) {
					$this->BgOAuthObjects[$i]['access_token']        = $parsed['access_token'];
					$this->BgOAuthObjects[$i]['access_token_secret'] = $parsed['access_token_secret'];
				} else {
					$this->BgOAuthObjects[$i]['error'] = '取得に失敗しました';
				}
			} else {
				$this->BgOAuthObjects[$i]['error'] = '取得に失敗しました';
			}
			curl_multi_remove_handle($mh,$ch);
			curl_close($ch);
		}
		curl_multi_close($mh);
		return $this->BgOAuthObjects;
	}
	
	private static function getPath() {
		$head = 'http://'.$_SERVER['HTTP_HOST'];
		if (!isset($_SERVER['REQUEST_URI']) || strlen($_SERVER['REQUEST_URI'])===0)
			return $head.'/';
		$ruri = strrev($_SERVER['REQUEST_URI']);
		if (!preg_match('@^.*?/+(.*)@',$ruri,$matches))
			exit('BgOAuthMultiError: URIが異常です');
		return $head.strrev($matches[1]).'/';
	}
	
}