<?php

//■Linkify Text Library Ver2.0.1■//
//
//Twitter上のあらゆるテキストを解析し、最適にリンクを張ったHTMLを出力するためのライブラリです。
//エンティティ情報がある場合はそれを忠実に再現し、
//無い場合はライブラリ側で解析します。
//
//置換するURL等が完全に自分用なので、ご自分の環境に合わせて編集し、ご利用ください。
//
//●Ver2.0.1
////・NOTICEエラーの原因が残っていたのでさらに修正
//
//●Ver2.0
////・NOTICEエラーが出ないように修正
////・API Version 1.0 のサポートを終了
////・正規表現を強化
//
//●Ver1.2.2
////・今さらながらEntifyよりLinkifyの方が名前的に合っていることに気づいたので修正
//
//●Ver1.2.1
////・スキームを取る/取らないを指定できるように改良
//
//●Ver1.2
////・クッションページリンクのスキーマを取れるように改良
///
//●Ver1.1
////・get_headers関数にエラー演算子を付加(適当なURLの場合も大丈夫なように)
///
//

mb_internal_encoding('UTF-8');

//via.me用に設定
define('VIA_ME_APP_KEY','');

//メイン関数
function linkify($text,$entities=null,$get_headers=false,$remove_scheme=true) {
	
	if (!empty($entities))
	return linkifyTextClass::linkifyByEntities($text,$entities,$get_headers,$remove_scheme);
	return linkifyTextClass::linkifyWithoutEntities($text,$get_headers,$remove_scheme);
	
}

//クラス
class linkifyTextClass {
	
	//UID取得
	public static function getUID() {
		
		switch (true) {
			//DoCoMo
			case (isset($_SERVER['HTTP_X_DCMGUID'])):
				return $_SERVER['HTTP_X_DCMGUID'];
			//au
			case (isset($_SERVER['HTTP_X_UP_SUBNO'])):
				return $_SERVER['HTTP_X_UP_SUBNO'];
			//SoftBank
			case (isset($_SERVER['HTTP_X_JPHONE_UID'])):
				return $_SERVER['HTTP_X_JPHONE_UID'];
			default:
				return null;
		
		}
		
	}
	
	//エンティティで解析
	public static function linkifyByEntities($text,$entities,$get_headers=false,$remove_scheme=true) {
		
		//変数初期化
		$sort_arr = array();
		$new_entities = array();
		
		/* ["indices"]の値を基準にソートする */
		if (!empty($entities->user_mentions)) 
		foreach ($entities->user_mentions as $user_mention) {
			$sort_arr[] = (int)$user_mention->indices[0];
			$user_mention->analyzed_type = 'user_mention';
			$new_entities[] = $user_mention;
		}
		if (!empty($entities->urls)) 
		foreach ($entities->urls as $url) {
			$sort_arr[] = (int)$url->indices[0];
			$url->analyzed_type = 'url';
			$new_entities[] = $url;
		}
		if (!empty($entities->hashtags))
		foreach ($entities->hashtags as $hashtag) {
			$sort_arr[] = (int)$hashtag->indices[0];
			$hashtag->analyzed_type = 'hashtag';
			$new_entities[] = $hashtag;
		}
		if (!empty($entities->media)) 
		foreach ($entities->media as $media) {
			$sort_arr[] = (int)$media->indices[0];
			$media->analyzed_type = 'media';
			$new_entities[] = $media;
		}

		//エンティティ情報が空の場合は改行変換のみで返す
		if (empty($new_entities)) return nl2br($text);
		
		//ソート処理
		array_multisort($sort_arr,$new_entities);
		
		/* 実際に置換していく */
		
		//1回置換するごとに起こる$posのズレに配慮(この計算を容易にするためにソートした)
		$pos_lag = 0; 
		
		foreach ($new_entities as $entity) {
			
			//indicesエレメント系
			$pos = $entity->indices[0];
			$len = $entity->indices[1] - $pos;
			
			switch ($entity->analyzed_type) {
			
				case 'user_mention':
				
					/* メンションを置換する */
					$replace_str = "<a href=\"person.php?guid=ON&person={$entity->screen_name}\">@{$entity->screen_name}</a>";
					break;
					
				case 'url':
				
					/* URLを置換する(範囲に#は含まれていない) */
					$replace_str = self::linkify_url($entity->expanded_url,$get_headers,$remove_scheme);
					break;
					
				case 'hashtag':
				
					/* ハッシュタグを置換する */
					$replace_str = "<a href=\"search.php?guid=ON&sq=".rawurlencode("#{$entity->text}")."\">#{$entity->text}</a>";
					break;
					
				case 'media':
				
					/* メディアを置換する */
					$raw_url = (is_null(self::getUID())) ? $entity->media_url : "http://web.fileseek.net/getimg.cgi?guid=ON&u=".rawurlencode($entity->media_url);
					$replace_str = "<br><a href=\"{$raw_url}\"><img src=\"{$entity->media_url}:thumb\"></a><br>";
					break;
					
			}
			
			//置換処理
			$text = mb_substr($text,0,$pos+$pos_lag).$replace_str.mb_substr($text,$pos+$pos_lag+$len);
			//ズレを加算
			$pos_lag += mb_strlen($replace_str) - $len;
			
		}
		
		//改行コードをタグに変換して返す
		return nl2br($text);
		
	}
	
	//エンティティを使わずに解析
	public static function linkifyWithoutEntities($text,$get_headers=false,$remove_scheme=true) {
		
		//配列に変換
		$array = self::__toArray($text);
		
		$new_text = '';
		
		//文節ごとに処理
		foreach ($array as $element) {
		
			switch ($element['type']) {
			
			//通常
			case false:
			case 'word':
				$new_text .= $element['str'];
				break;
			
			//URL
			case 'url':
				$parsed = parse_url($element['str']);
				$parsed['scheme'] = (isset($parsed['scheme'])) ? $parsed['scheme']."://" : "http://";
				$url = $parsed['scheme'];
				if (isset($parsed['host'])) $url .= $parsed['host'];
				if (isset($parsed['path'])) $url .= $parsed['path'];
				if (isset($parsed['query'])) $url .= $parsed['query'];
				if (isset($parsed['fragment'])) $url .= $parsed['fragment'];
				$new_text .= self::linkify_url($url,$get_headers,$remove_scheme);
				break;
			
			//スクリーンネーム
			case 'screen_name':
				$new_text .= "<a href=\"person.php?guid=ON&person={$element['str']}\">{$element['str']}</a>";
				break;
				
			//ハッシュタグ
			case 'hashtag':
				$new_text .= "<a href=\"search.php?guid=ON&sq=".rawurlencode($element['str'])."\">{$element['str']}</a>";
				break;
			
			}
			
		}
		
		//処理済みの文字列を返す
		return $new_text;
		
	}
	
	//画像情報を取得
	public static function get_image_details($url) {
		
		//変数初期化
		$img_thumb = null;
		$img_raw = null;
		$youtube = null;
		
		switch (true) {
			
			//Twitpic
			case (preg_match('/^http:\/\/twitpic[.]com\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://twitpic.com/show/mini/'.$matches[1];
				$img_raw = 'http://twitpic.com/show/full/'.$matches[1];
				break;
			
			//Mobypicture
			case (preg_match('/^http:\/\/moby[.]to\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://moby.to/'.$matches[1].':thumbnail';
				$img_raw = 'http://moby.to/'.$matches[1].':medium';
				break;
				
			//yFrog
			case (preg_match('/^http:\/\/yfrog[.]com\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://yfrog.com/'.$matches[1].':small';
				$img_raw = 'http://yfrog.com/'.$matches[1].':medium';
				break;
			
			//携帯百景
			case (preg_match('/^http:\/\/movapic[.]com\/pic\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://image.movapic.com/pic/t_'.$matches[1].'.jpeg';
				$img_raw = 'http://image.movapic.com/pic/s_'.$matches[1].'.jpeg';
				break;
			
			//はてなフォトライフ
			case (preg_match('/^http:\/\/f[.]hatena[.]ne[.]jp\/(([\w\-])[\w\-]+)\/((\d{8})\d+)$/',$url,$matches)):
				$img_thumb = 'http://img.f.hatena.ne.jp/images/fotolife/'.$matches[2].'/'.$matches[1].'/'.$matches[4].'/'.$matches[3].'_m.jpg';
				$img_raw = 'http://img.f.hatena.ne.jp/images/fotolife/'.$matches[2].'/'.$matches[1].'/'.$matches[4].'/'.$matches[3].'.jpg';
				break;
			
			//PhotoShare1
			case (preg_match('/^http:\/\/(?:www[.])?bcphotoshare[.]com\/photos\/\d+\/(\d+)$/',$url,$matches)):
				$img_thumb = 'http://images.bcphotoshare.com/storages/'.$matches[1].'/thumb68.jpg';
				$img_raw = 'http://images.bcphotoshare.com/storages/'.$matches[1].'/large.jpg';
				break;
			
			//PhotoShare2
			case (preg_match('/^http:\/\/bctiny[.]com\/p(\w+)$/',$url,$matches)):
				$img_thumb = 'http://images.bcphotoshare.com/storages/'.base_convert($matches[1],36,10).'/thumb68.jpg';
				$img_raw = 'http://images.bcphotoshare.com/storages/'.base_convert($matches[1],36,10).'/large.jpg';
				break;
			
			//img.ly
			case (preg_match('/^http:\/\/img[.]ly\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://img.ly/show/thumb/'.$matches[1];
				$img_raw = 'http://img.ly/show/full/'.$matches[1];
				break;
			
			//Twitgoo
			case (preg_match('/^http:\/\/twitgoo[.]com\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://twitgoo.com/'.$matches[1].'/mini';
				$img_raw = 'http://twitgoo.com/'.$matches[1].'/img';
				break;
			
			//YouTube
			case (preg_match('/^http:\/\/(?:www[.]youtube[.]com\/watch(?:\?|#!)v=|youtu[.]be\/)([\w\-]+)(?:[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]*)$/',$url,$matches)):
				$img_thumb = 'http://i.ytimg.com/vi/'.$matches[1].'/default.jpg';
				$img_raw = 'http://i.ytimg.com/vi/'.$matches[1].'/hqdefault.jpg';
				$youtube = 'http://youtu.be/'.$matches[1];
				break;
			
			//imgur
			case (preg_match('/^http:\/\/imgur[.]com\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://i.imgur.com/'.$matches[1].'s.jpg';
				$img_raw = 'http://i.imgur.com/'.$matches[1].'.jpg';
				break;
				
			//TweetPhoto,Plixi,Lockerz
			case (preg_match('/^http:\/\/tweetphoto[.]com\/\d+|http:\/\/plixi[.]com\/p\/\d+|http:\/\/lockerz[.]com\/s\/\d+$/',$url,$matches)):
				$img_thumb = 'http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=thumbnail&url='.$matches[0];
				$img_raw = 'http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=big&url='.$matches[0];
				break;
				
			//Ow.ly
			case (preg_match('/^http:\/\/ow[.]ly\/i\/(\w+)$/',$url,$matches)):
				$img_thumb = 'http://static.ow.ly/photos/thumb/'.$matches[1].'.jpg';
				$ow_jpg = 'http://static.ow.ly/photos/original/'.$matches[1].'.jpg';
				$ow_png = 'http://static.ow.ly/photos/original/'.$matches[1].'.png';
				$ow_gif = 'http://static.ow.ly/photos/original/'.$matches[1].'.gif';
				switch (true) {
					case self::url_exists($ow_jpg): $img_raw = $ow_jpg; break;
					case self::url_exists($ow_png): $img_raw = $ow_png; break;
					case self::url_exists($ow_gif): $img_raw = $ow_gif; break;
					default                       : $img_raw = $ow_gif;
				}
				break;
			
			//Instagram
			case (preg_match('/^http:\/\/instagr[.]am\/p\/([\w\-]+)\/$/',$url,$matches)):
				$img_thumb = 'http://instagr.am/p/'.$matches[1].'/media/?size=t';
				$img_raw = 'http://instagr.am/p/'.$matches[1].'/media/?size=l';
				break;
				
			//フォト蔵
			case (preg_match('/^http:\/\/photozou[.]jp\/photo\/show\/\d+\/([\d]+)$/',$url,$matches)):
				$img_thumb = 'http://photozou.jp/p/thumb/'.$matches[1];
				$img_raw = 'http://photozou.jp/p/img/'.$matches[1];
				break;
			
			//ついっぷるフォト
			case (preg_match('/^http:\/\/p[.]twipple[.]jp\/([\w]+)$/',$url,$matches)):
				$img_thumb = 'http://p.twpl.jp/show/thumb/'.$matches[1];
				$img_raw = 'http://p.twpl.jp/show/large/'.$matches[1];
				break;
				
			//via.me
			case (preg_match('/^http:\/\/via[.]me\/-(\w+)$/',$url,$matches)):
				$request_url = 'http://api.via.me/v1/posts/'.$matches[1]."?client_id=".VIA_ME_APP_KEY;
				$res = json_decode(@file_get_contents($request_url));
				if (!empty($res->response)) { 
					$img_thumb = $res->response->post->thumb_url;
					$img_raw = $res->response->post->media_url;
				}
				break;
			
			//その他直接表示の画像
			case (preg_match('/^http:\/\/.+?\.(jpg|png|gif|jpeg)$/ui',$url,$matches)):
				$img_thumb = 'http://i.tinysrc.mobi/80/'.$matches[0];
				$img_raw = $matches[0];
				break;
				
		}
		
		return array('thumb'=>$img_thumb,'raw'=>$img_raw,'youtube'=>$youtube);
		
	}
	
	//指定されたURLが存在するかどうかを確認する
	public static function url_exists($url) {
	
		$headers = @get_headers($url);
		switch (true) {
			case (!isset($headers[0])):
			case (strpos($headers[0],'4')===0):
			case (strpos($headers[0],'5')===0):
				return false;
			default:
				return true;
		}
		
	}
	
	//(短縮)URLを解析してHTMLエンティティを付加したものを返す
	public static function linkify_url($url,$get_headers=false,$remove_scheme=true){
		
		//画像URLパターンと照合
		$details = self::get_image_details($url);
		
		switch (true) {
			
			//YouTube
			case (isset($details['youtube'])):
				return "<br><a href=\"{$details['youtube']}\"><img src=\"{$details['thumb']}\"></a><br>";
			//画像＋ガラケー
			case (isset($details['thumb']) && !is_null(self::getUID())):
				return "<br><a href=\"http://web.fileseek.net/getimg.cgi?guid=ON&u=".rawurlencode($details['raw'])."\"><img src=\"{$details['thumb']}\"></a><br>";
			//画像＋PC
			case (isset($details['thumb'])):
				return "<br><a href=\"{$url}\"><img src=\"{$details['thumb']}\"></a><br>";
			
			//ツイートのURL(またはそのメディア)
			case (preg_match("#^https?://twitter\.com/([A-Za-z0-9_]{1,15})/status(es)?/(\d+)#",$url,$matches)):
				return "<a href=\"showTweet.php?guid=ON&id={$matches[3]}\" style=\"color:blue;\">{$matches[1]}のツイート</a>";
				
			//ヘッダー取得が無効か失敗した場合クッションページ用URLを適用
			case ($get_headers===false):
			case (($headers=@get_headers($url))===false):
				return self::make_jump_url($url,$remove_scheme);
			
		}
		
		//ヘッダーを逆順に並び替えてLocationがあれば再帰させる
		$headers = array_reverse($headers);
		foreach ($headers as $h) {
			if (strpos($h,"Location: http")!==0) continue;
			return self::linkify_url(substr($h,10),true,$remove_scheme);
		}
		
		//それ以上Locationが見つからなくなったら停止してクッションページ用URLを適用
		return self::make_jump_url($url,$remove_scheme);
		
	}
	
	//URLクッションページ用
	public static function make_jump_url($url,$remove_scheme=true) {
		
		$enc_url = rawurlencode(base64_encode($url));
		if ($remove_scheme) $url = preg_replace('@https?://@','',$url);
		$url = mb_strimwidth($url,0,35,"...");
		$back = rawurlencode(base64_encode(getenv('REQUEST_URI')));
		return "<a href=\"jump.php?guid=ON&back={$back}&url={$enc_url}\">{$url}</a>";
		
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
					case (!empty($matches[1])) : $type = 'screen_name'; break;
					case (!empty($matches[2])) : $type = 'hashtag'; break;
					default                    : $type = 'word';
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

}