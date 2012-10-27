<?php

//■Explode Tweet Library Ver2.1.1■//
//
//ツイートを容易に分割することが出来ます。
//140字毎にURLや英文節を壊さないように区切って分割します。
//全てのURLはt.coに短縮されるため、20文字として扱われます。
//先頭にリプライヘッダがある場合、分割された先頭以外のツイートにもそれを付加します。
//DMヘッダの場合はその文字数を除外してカウントします。
//
//
///Ver2.1.1
///・defineを1回しか行わないように外側に出した
///
///Ver2.1
///・正規表現を強化
///・NOTICEエラーが出ないように改良
///
///Ver2.0
///・DMヘッダーの分割対応
///・140字以内にカットするときの精度を上げた
///
///Ver1.6
///・関数名など若干変更
///
///Ver1.5
///・全ての文節が収まりきる場合も接尾辞を足すとオーバーしてしまう場合に、
///　次のツイートに持ち越すようになっていたバグを修正。
///　収まりきることが確定した場合、そのまま最後まで文節を付加させるようにした。
///
//
//

mb_internal_encoding('UTF-8');

//互換性維持のためdefineで定義
define('URL_MAX',20); //t.coのURLの最大文字数
define('HEAD_MAX',100); //ヘッダーの最大文字数

//メイン関数
function explodeTweet($str) {
	
	//改行コードを\nに統一
	$str = str_replace("\r\n","\n",$str);
	
	//オブジェクト生成
	$c = new explodeTweetClass($str);
	//接頭辞と接尾辞を決定
	if (mb_strlen($str)!==strlen($str)) {
		$array = $c->explodeTweet("(続き) "," (続く)");
	} else {
		$array = $c->explodeTweet("(cont..)","(..cont)");
	}
	//オブジェクト破棄
	unset($c);
	
	return $array;

}

//クラス
class explodeTweetClass {
	
	//変数宣言
	private $tweet_in;
	private $body;
	private $headers;
	private $flag_dm;
	private $flag_go_in;
	
	//コンストラクタ
	public function __construct($str) {
		
		$this->tweet_in = $str;
		
	}
	
	//ツイート分割
	public function explodeTweet($prefix="(続き) ",$suffix=" (続く)") {
		
		//変数初期化
		$this->flag_dm = false;
		$this->headers = array();
		$whole_tweets = array();
		$tweets = array(array());
		$parent = 0;
		$child = 0;
		$prefLength = mb_strlen($prefix);
		$suffLength = mb_strlen($suffix);
		
		//ヘッダーとボディに分割する
		//DMでもリプライでもなく、かつRTフォーマットが見つかった場合は適切に140字以内にカットして返す
		if (!$this->splitDMHeader())
		if (!$this->splitHeader() && preg_match("/(QB|[A-Z]T)[\s　]*@[A-Za-z0-9_]{1,15}:/us",$this->tweet_in))
		return array(self::__toStr140(self::__toArray($this->tweet_in)));
		
		//文節配列作成
		$texts = self::__toArray($this->body);
		
		//parentごとに処理
		foreach ($this->headers as $header) {
			
			//parentごとの変数初期化
			$cnt = 0;
			$tempTexts = $texts;
			$headLength = ($this->flag_dm) ? 0 : mb_strlen($header);
			$tweetLength = 0;
			$in_process = false;
			
			//childごとに処理
			while (true) {
			
				if (!$in_process) {
				
					//childごとの変数初期化
					$tweets[$parent][$child] = $header;
					$tweetLength = $headLength;
					$in_process = true;
					$flag_go_in = null;
					if ($child>0) {
						//先頭以外のchildにはprefixをつける
						$tweets[$parent][$child] .= $prefix;
						$tweetLength += $prefLength;
					}
					
				} else {
				
					//現文節が無い場合
					if (!isset($tempTexts[$cnt]))
					//whileループを脱出
					break;
					
					//現文節の長さ
					$tempLength = ($tempTexts[$cnt]['type']==='url') ? URL_MAX : mb_strlen($tempTexts[$cnt]['str']);
					
					if ($tweetLength + $tempLength <= 140) {
					
						//既成の本文＋現文節の長さが140字以内の場合
						
						switch (true) {
							
							//更にsuffixを付けても140字以内の場合
							case $tweetLength + $tempLength + $suffLength <= 140 :
							//suffixを付けると140字を超えるが、残りの文節が収まりきることが確定している場合
							case $this->go_in($tempTexts,$cnt,$tweetLength) :
							
							//現文節を追加
							$tweets[$parent][$child] .= $tempTexts[$cnt]['str'];
							$tweetLength += $tempLength;
							//次文節に進む
							$cnt++;
							//第1switchを脱出
							break;
							
							//suffixを付けると140字を超える場合
							//(以降第1switchのbreakは不要)
							default:
							
							Switch (true) {
								
								//次文節がURLの場合
								case $tempTexts[$cnt+1]['type']==='url' :
								//次々文節が無く、次文節が次child送りにした後140字以内に収まる場合
								case (!isset($tempTexts[$cnt+2]) && $headLength + $prefLength + mb_strlen($tempTexts[$cnt+1]['str']) <= 140) :
								//次々文節があり、次文節+suffixが次child送りにした後140字以内に収まる場合
								case $headLength + $prefLength + mb_strlen($tempTexts[$cnt+1]['str']) + $suffLength <= 140 : 
								
								//suffixを追加して次childに進む
								//文節は次child送りにするためそのまま
								$tweets[$parent][$child] .= $suffix;
								$child++;
								$in_process = false;
								//第2switchを脱出
								break;
								
								//次文節が非常に長く、文節を切り分けるしかない場合
								//(以降第2switchのbreakは不要)
								default:
								
								//現文節を140字以内に収まる分だけカットして追加
								$restLength = 140 - $tweetLength - $suffLength;
								$tweets[$parent][$child] .= mb_substr($tempTexts[$cnt]['str'],0,$restLength).$suffix;
								//収まらなかった分を文節配列に挿入
								array_splice($tempTexts,$cnt+1,0,
									array(
										array(
											'str'=>mb_substr($tempTexts[$cnt]['str'],$restLength),
											'type'=>false
										)
									)
								);
								//次childに進む
								$child++;
								$in_process = false;
								//次文節に進む
								$cnt++;
									
							}
							
						}
						
					} else {
						
						//既成の本文＋現文節の長さが140字を超過する場合
						
						//現文節を140字以内に収まる分だけカットして追加
						$restLength = 140 - $tweetLength - $suffLength;
						$tweets[$parent][$child] .= mb_substr($tempTexts[$cnt]['str'],0,$restLength).$suffix;
						//収まらなかった分を文節配列に挿入
						array_splice($tempTexts,$cnt+1,0,
							array(
								array('str'=>mb_substr($tempTexts[$cnt]['str'],$restLength),
								'type'=>false
								)
							)
						);
						//次childに進む
						$child++;
						$in_process = false;
						//次文節に進む
						$cnt++;
						
					}
					
				}
			
			}
			
			//完成したツイートを1次配列化して集める
			array_splice($whole_tweets,-1,0,$tweets[$parent]);
			//次parentに進む
			$parent++;
			$child = 0;
		
		}
		
		//まとめたものを返す
		return $whole_tweets;
		
	}
	
	//文節配列を140字以内に適切にカットして返す($prev変数は再帰的に内部で利用)
	public static function __toStr140($array,$prev=null) {
		
		//変数初期化
		$str = '';
		$len = 0;
		//全ての文節を結合、長さを測る
		foreach ($array as $element) {
			$str .= $element['str'];
			$len += ($element['type']==='url') ? URL_MAX : mb_strlen($element['str']);
		}
		//140字以内の場合
		if ($len<=140) {
			switch ($prev['type']) {
				case 'url': 
				case 'screen_name':
				case 'hashtag':
					//収まった分をそのまま返す
					return $str;
				default:
					//受け取った文節を収まる分だけ付随させて返す
					return $str.mb_substr($prev['str'],0,140-$len);
			}
		}
		//140字以上の場合、末尾の文節を取り去って$prev変数に渡し、再帰させる
		$prev = array_pop($array);
		return self::__toStr140($array,$prev);
	
	}
	
	//テキストを解析して成分ごとに分割して配列に格納
	public static function __toArray($text) {
		
		//配列を初期化
		$array = array(array());
		$array[0]['str'] = $text;
		$array[0]['type'] = false;
		
		/*URLで分割*/
		$delimiter = "@";
		$modifier = "u";
		$ex1 = "(?<![\\w])";
		$ex2 = "(?<!/)";
		$scheme = "(?:https?://)";
		$part = "(?:(?:[\\w]|[^ -~])+(?:(?:[\\w\\-]|[^ -~])+(?:[\\w]|[^ -~]))?\\.)";
		$gTLD = "(?:aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|xxx)";
		$ccTLD = "(?:".
		"ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|".
		"bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cs|cu|cv|cx|cy|cz|".
		"dd|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|".
		"gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|".
		"je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|".
		"md|me|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|".
		"np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|".
		"sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|".
		"to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|za|zm|zw".
		")";
		$path = "(?![\\w])(?:/(?:[\\w\\.\\-\\$&%/:=#~!]*\\??[\\w\\.\\-\\$&%/:=#~!]*[\\w\\-\\$/#])?)?";
		$host1 = $ex1.$scheme.$part."+"."(?:".$gTLD."|".$ccTLD.")";
		$host2 = $ex2.$ex1.$part."+".$gTLD;
		$host3 = $ex2.$ex1.$part."{2,}".$ccTLD;
		$pattern = $delimiter."(?:".$host1."|".$host2."|".$host3.")".$path.$delimiter.$modifier;
		
		//URLを見つけて配列に区切って入れていく
		while (true) {
			$temp_arr = array_pop($array);
			if (preg_match($pattern,$temp_arr['str'],$matches)) {
				$url_pos = mb_strpos($temp_arr['str'],$matches[0]);
				$url_len = mb_strlen($matches[0]);
				$url_str = $matches[0];
				$array[] = array('str'=>mb_substr($temp_arr['str'],0,$url_pos),'type'=>false);
				$array[] = array('str'=>$url_str,'type'=>'url');
				$array[] = array('str'=>mb_substr($temp_arr['str'],$url_pos+$url_len),'type'=>false);
				continue;
			}
			$array[] = $temp_arr;
			break;
		}
		
		/*ハッシュタグ・英単語で分割*/
		
		//パターン(優先順位：メンション→ハッシュタグ→英単語)
		$pattern = "/(?<![\\w])(@[A-Za-z0-9_]{1,15})|(?<![\\w])([#♯][ー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]{1,139})|([A-Za-z0-9\-_.,:;]{1,140})/u";
		
		//実際に分割
		$cnt=0;
		while (true) {
			//残りの文節が無ければwhileループ脱出
			if (!isset($array[$cnt])) break;
			$temp_arr = $array[$cnt];
			//既にURL属性のあるものは無視
			if ($temp_arr['type']!==false) {
				$cnt++;
				continue;
			}
			if (preg_match($pattern,$temp_arr['str'],$matches)) {
				$pos = mb_strpos($temp_arr['str'],$matches[0]);
				$len = mb_strlen($matches[0]);
				$str = $matches[0];
				//種類を判定
				switch (true) {
					case isset($matches[1]) : $type = 'screen_name'; break;
					case isset($matches[2]) : $type = 'hashtag'; break;
					default                 : $type = 'word';
				}
				array_splice($array,$cnt,1,
					array(
						array('str'=>mb_substr($temp_arr['str'],0,$pos),'type'=>false),
						array('str'=>$str,'type'=>$type),
						array('str'=>mb_substr($temp_arr['str'],$pos+$len),'type'=>false)
					)
				);
				$cnt += 2;
				continue;
			}
			$cnt++;	
		}
		
		//配列を返す
		return $array;
		
	}
	
	//ヘッダーと本文を分割(リプライ)
	private function splitHeader() {
		
		//ヘッダーが見つからなければヘッダーと本文を初期化してfalseを返す
		if (!preg_match("/^([\s　]*\.?[\s　]*)((@[A-Za-z0-9_]{1,15}[\s　]?)+)(.+)*/us",$this->tweet_in,$first_matches)) {
			$this->headers[] = "";
			$this->body = $this->tweet_in;
			return false;
		}
			
		//ヘッダーが見つかれば本文を初期化、スクリーンネーム1つ1つを取り出す
		preg_match_all("/@[A-Za-z0-9_]{1,15}/us",$first_matches[2],$second_matches);
		$headChar = $first_matches[1];
		$headScreenNames = array();
		$headScreenNames = $second_matches[0];
		$this->body = $first_matches[4];
		
		//変数初期化
		$in_process = false;
		$prev = false;
		
		//制限字数内でヘッダーを連結していく
		for ($cnt=0;;$cnt++) {
			if (!$in_process) {
				$temp = $headChar;
				$in_process = true;
			}
			$temp .= $headScreenNames[$cnt]." ";
			if (mb_strlen($temp) > HEAD_MAX) {
				$this->headers[] = $prev;
				$temp = "";
				$in_process = false;
				$cnt--;
			} elseif (!isset($headScreenNames[$cnt+1])) {
				$this->headers[] = $temp;
				break;
			}
			$prev = $temp;
		}
		
		//trueを返す
		return true;
	
	}
	
	//ヘッダーと本文を分割(DM)
	private function splitDMHeader() {
		
		//ヘッダーが見つからなければfalseを返す
		if (!preg_match("/^([\s\r\n]*?[DM]\s+[^\s\r\n]+[\s\r\n]+)(.+)/usi",$this->tweet_in,$matches))
		return false;
		
		//ヘッダーが見つかれば本文を初期化、ヘッダーも設定してtrueを返す
		$this->headers[] = $matches[1];
		$this->body = $matches[2];
		$this->flag_dm = true;
		return true;
	
	}
	
	//残りの文節を全て結合して140字に収まりきるかどうかを判断
	private function go_in($texts,$currentCnt,$currentLength) {
		
		//判定済みの場合はすぐに返す
		if ($this->flag_go_in===true) return true;
		if ($this->flag_go_in===false) return false;
		
		//残っている文字数を計算
		$count = count($texts);
		$sum = 0;
		for ($cnt=$currentCnt;$cnt<$count;$cnt++) {
			$sum += ($texts[$cnt]['type']==='url') ? URL_MAX : mb_strlen($texts[$cnt]['str']);
		}
		
		//140字以内に収まりきる場合は判定済みフラグをtrueに設定し、trueを返す
		if ($sum+$currentLength<=140) {
			$this->flag_go_in = true;
			return true;
		}
		
		//140字を超過する場合は判定済みフラグをfalseに設定し、falseを返す
		$this->flag_go_in = false;
		return false;
		
	}
	
}