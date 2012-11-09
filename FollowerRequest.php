<?php

//****************************************************************
//************* Follower Request Library Version 1.0 *************
//****************************************************************
//
//                                             作者: @To_aru_User
//
//サードパーティ向け鍵アカウントフォロワーリクエスト許可/拒否ライブラリ
//
//●使用例
//
// $username = 'hoge';
// $password = 'fuga';
//
// $f = new FollowerRequest();
//
// $res = $f->login($username,$password);
//   最初にログインしてください。
//   成功するとTrue、失敗するとFalseが返ります。
//   他のメソッドを実行するためにはログインする必要があります。
//
// $penders = getPenders();
//   profile_image_url, id, id_str, screen_name, name を要素に持つ
//   ユーザーオブジェクトの配列が返ります。
//   screen_nameの昇順にソートされます。
//   ※このメソッドを使わなくてもユーザー一覧の取得のみならば、
//     Twitterの公開しているAPIで実現可能です。
//
// $res = $f->acceptPender($id);
// $res = $f->denyPender($id);
//   指定したuser_idのリクエストを承認/拒否します。
//   成功した場合True、失敗した場合Falseが返ります。
//
// $f->acceptAll();
//   全てのリクエストを承認します。
//   必ずTrueが返ります(結果をチェックしません)。
//

class FollowerRequest {
	
	private $cookie;
	private $authenticity_token;
	
	public function __construct() {
	
		$this->cookie = array();
		$this->authenticity_token = '';
		
	}
	
	public function login($username,$password) {
	
		$response = $this->request('https://twtr.jp/login');
		if ($response===false || !preg_match('@<input.*?name="authenticity_token".*?value="(.+?)"@',$response,$matches)) return false;
		$this->authenticity_token = $matches[1];
		$param = array(
			'login'=>$username,
			'password'=>$password,
			'authenticity_token'=>$this->authenticity_token
		);
		$response = $this->request('https://twtr.jp/login','POST',$param);
		if ($response===false || !preg_match('@twtr\.jp/home@',$response)) return false;
		return true;
		
	}
	
	public function getPenders() {
	
		$param = array('authenticity_token'=>$this->authenticity_token);
		$response = $this->request('https://twtr.jp/follower_request','POST',$param);
		$pattern  = '@';
		$pattern .= '<form action="https://twtr\.jp/follower_request/(\d+).*?';
		$pattern .= '<img alt="(.+?)".*?src="(.+?)".*?';
		$pattern .= '<span class="small">(.*?)</span>';
		$pattern .= '@us';
		if ($response===false || !preg_match_all($pattern,$response,$matches,PREG_SET_ORDER)) return array();
		$penders = array();
		$sortbase = array();
		$cnt = 0;
		foreach ($matches as $match) {
			$sortbase[] = strtolower($match[2]);
			$penders[$cnt] = new stdClass;
			$penders[$cnt]->profile_image_url = $match[3];
			$penders[$cnt]->id = (int)$match[1];
			$penders[$cnt]->id_str = $match[1];
			$penders[$cnt]->screen_name = $match[2];
			$penders[$cnt]->name = $match[4];
			$cnt++;
		}
		array_multisort($sortbase,$penders);
		return array_values($penders);
		
	}
	
	public function acceptPender($id) {
	
		$param = array(
			'authenticity_token'=>$this->authenticity_token,
			'cursor'=>'-1',
			'commit'=>'許可'
		);
		$response = $this->request('https://twtr.jp/follower_request/'.$id,'POST',$param);
		if (!preg_match('@許可しました@u',$response)) return false;
		return true;
		
	}
	
	public function denyPender($id) {
	
		$param = array(
			'authenticity_token'=>$this->authenticity_token,
			'cursor'=>'-1',
			'commit'=>'拒否'
		);
		$response = $this->request('https://twtr.jp/follower_request/'.$id,'POST',$param);
		if (!preg_match('@拒否しました@u',$response)) return false;
		return true;
		
	}
	
	public function acceptAll() {
	
		$param = array(
			'authenticity_token'=>$this->authenticity_token,
			'cursor'=>'-1',
			'commit'=>'全て許可'
		);
		$response = $this->request('https://twtr.jp/follower_request/accept_all','POST',$param,false);
		return true;
		
	}
	
	private function request($url,$method='GET',$param=array(),$recursive=true) {
	
		$parsed = parse_url($url);
		$method = (strtoupper($method)=='POST') ? 'POST' : 'GET';
		$data = http_build_query($param,'','&');
		
		if (empty($parsed) || empty($parsed['host'])) return false;
		$parsed['scheme']     = empty($parsed['scheme'])   ? 'http://' :      $parsed['scheme']        ;
		$parsed['path']       = empty($parsed['path'])     ? '/'       :      $parsed['path']          ;
		$parsed['fragment']   = empty($parsed['fragment']) ? ''        : '#'. $parsed['fragment']      ;
		$parsed['query']      = empty($parsed['query'])    ? ''        :      $parsed['query']         ;
		if ($method!='POST') {
			$parsed['query'] .= (empty($parsed['query']))  ? $data     : ((empty($data))?'':'&'.$data) ;
		}
		$parsed['query']      = (empty($parsed['query']))  ? ''        : '?'. $parsed['query']         ;
		$parsed['foot']       = $parsed['path'] . $parsed['query'] . $parsed['fragment']               ;
		
		$request   = array();
		$request[] = $method.' '.$parsed['foot'].' HTTP/1.1';
		$request[] = 'Host: '.$parsed['host'];
		$request[] = 'User-Agent: Mozilla/0 (iPhone;)';
		if (!empty($this->cookie)) {
			$parts = array();
			foreach ($this->cookie as $key => $value) {
				$parts[] = $key .'='.$value;
			}
			$request[] = 'Cookie: '.implode('; ',$parts);
		}
		if ($method=='POST') {
			$request[] = 'Content-Type: application/x-www-form-urlencoded';
			$request[] = 'Content-Length: '.strlen($data);
		}
		$request[] = '';
		$request[] = ($method=='POST') ? $data : '';
		$host = ($parsed['scheme']=='https') ? 'ssl://'.$parsed['host'] : $parsed['host'];
		$port = ($parsed['scheme']=='https') ? 443 : 80;
		$fp = @fsockopen($host,$port);
		if ($fp===false) return false;
		fwrite($fp,implode("\r\n",$request));
		ob_start();
		fpassthru($fp);
		$res = ob_get_clean();
		fclose($fp);
		
		$response = explode("\r\n\r\n",$res,2);
		$headers  = isset($response[0]) ? explode("\r\n",$response[0]) : array() ;
		$body     = isset($response[1]) ? $response[1]                 : ''      ;
		foreach ($headers as $line) {
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
		if ($recursive)
		foreach ($headers as $line) {
			if (strpos($line,'Location: ')===0) {
				return $this->request(substr($line,10));
			}
		}
		return $body;
		
	}

}