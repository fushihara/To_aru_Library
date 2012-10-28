<?php

//■Twitter Morse Library Ver1.0■//
//
//モールス信号エンコード/デコードを行います。
//Twitterのリプライ・RTフォーマットなどを自動判別します。
//

mb_internal_encoding('UTF-8');

class TwitterMorse {
	
	//デコード
	public static function decode($str) {
		if (preg_match('/^([\s.]*((<a .*?>)?@[A-Za-z0-9_]{1,15}(<\/a>)?[\s　]*)+)(.*)$/us',$str,$matches)) {
			return $matches[1].self::decode_part($matches[5]);
		}
		$parts = preg_split("/([\s　]*([A-Z]T|QB)[\s　]*?(<a .*?>)?@[A-Za-z0-9_]{1,15}(<\/a>)?:[\s　]*)/u",$str,null,PREG_SPLIT_DELIM_CAPTURE);
		$ret = '';
		$flag = false;
		foreach ($parts as $part) {
			if ($flag) {
				$ret .= $part;
			} else {
				$ret .= self::decode_part($part);
			}
			$flag = !$flag;
		}
		return $ret;
	}
	
	//エンコード
	public static function encode($str) {
		$origin = $str;
		if (preg_match("/^(@[A-Za-z0-9_][\s　]*)(.*)/us",$str,$matches)) {
			$head = $matches[1];
			$str = $matches[2];
			$foot = '';
		} elseif (preg_match("/(.*?)([\s　]*(QB|[A-Z]T)[\s　]*@[A-Za-z0-9_]{1,15}:.*)/us",$str,$matches)) {
			$head = '';
			$str = $matches[1];
			$foot = $matches[2];
		} else {
			$head = '';
			$foot = '';
		}
		$str = mb_convert_kana($str,'KVC');
		$str = str_replace('　',' ',$str);
		$replace_pairs = array(
			'イ' => '・－ ',
			'ィ' => '・－ ',
			'ロ' => '・－・－ ',
			'ハ' => '－・・・ ',
			'ニ' => '－・－・ ',
			'ホ' => '－・・ ',
			'ヘ' => '・ ',
			'ト' => '・・－・・ ',
			'チ' => '・・－・ ',
			'リ' => '－－・ ',
			'ヌ' => '・・・・ ',
			'ル' => '－・－－・ ',
			'ヲ' => '・－－－ ',
			'ワ' => '－・－ ',
			'ヮ' => '－・－ ',
			'カ' => '・－・・ ',
			'ヨ' => '－－ ',
			'ョ' => '－－ ',
			'タ' => '－・ ',
			'レ' => '－－－ ',
			'ソ' => '－－－・ ',
			'ツ' => '・－－・ ',
			'ッ' => '・－－・ ',
			'ネ' => '－－・－ ',
			'ナ' => '・－・ ',
			'ラ' => '・・・ ',
			'ム' => '－ ',
			'ウ' => '・・－ ',
			'ゥ' => '・・－ ',
			'ヰ' => '・－・・－ ',
			'ノ' => '・・－－ ',
			'オ' => '・－・・・ ',
			'ォ' => '・－・・・ ',
			'ク' => '・・・－ ',
			'ヤ' => '・－－ ',
			'ャ' => '・－－ ',
			'マ' => '－・・－ ',
			'ケ' => '－・－－ ',
			'ヶ' => '－・－－ ',
			'フ' => '－－・・ ',
			'コ' => '－－－－ ',
			'エ' => '－・－－－ ',
			'ェ' => '－・－－－ ',
			'テ' => '・－・－－ ',
			'ア' => '－－・－－ ',
			'ァ' => '－－・－－ ',
			'サ' => '－・－・－ ',
			'キ' => '－・－・・ ',
			'ユ' => '－・・－－ ',
			'ュ' => '－・・－－ ',
			'メ' => '－・・・－ ',
			'ミ' => '・・－・－ ',
			'シ' => '－－・－・ ',
			'ヱ' => '・－－・・ ',
			'ヒ' => '－－・・－ ',
			'モ' => '－・・－・ ',
			'セ' => '・－－－・ ',
			'ス' => '－－－・－ ',
			'ン' => '・－・－・ ',
			'゛' => '・・ ',
			'゜' => '・・－－・ ',
			'ー' => '・－－・－ ',
			'、' => '・－・－・－ ',
			'」' => '・－・－・・ ',
			'（' => '－・－－・－ ',
			'）' => '・－・・－・ ',
			'ガ' => '・－・・ ・・ ',
			'ギ' => '－・－・・ ・・ ',
			'グ' => '・・・－ ・・ ',
			'ゲ' => '－・－－ ・・ ',
			'ゴ' => '－－－－ ・・ ',
			'ザ' => '－・－・－ ・・ ',
			'ジ' => '－－・－・ ・・ ',
			'ズ' => '－－－・－ ・・ ',
			'ゼ' => '・－－－・ ・・ ',
			'ゾ' => '－－－・ ・・ ',
			'ダ' => '－・ ・・ ',
			'ヂ' => '・・－・ ・・ ',
			'ヅ' => '・－－・ ・・ ',
			'デ' => '・－・－－ ・・ ',
			'ド' => '・・－・・ ・・ ',
			'バ' => '－・・・ ・・ ',
			'ビ' => '－・－・ ・・ ',
			'ブ' => '－－・・ ・・ ',
			'ベ' => '・ ・・ ',
			'ボ' => '－・・ ・・ ',
			'パ' => '－・・・ ・・－－・ ',
			'ピ' => '－－・・－ ・・－－・ ',
			'プ' => '－－・・ ・・－－・ ',
			'ペ' => '・ ・・－－・ ',
			'ポ' => '－・・ ・・－－・ '
		);
		if (preg_match("/[^\s　".implode('',array_keys($replace_pairs))."]/u",$str)) return $origin;
		$str = strtr($str,$replace_pairs);
		return $head.$str.$foot;
	}
	
	private static function decode_part($str) {
		if (empty($str) || preg_match("/[^\s　・－]/u",$str)) return $str;
		$replace_pairs = array(
			'・－・－・－' => '、',
			'・－・－・・' => '」',
			'－・－－・－' => '（',
			'・－・・－・' => '）',
			'－・・－－－' => '[本文]',
			'・・・－・' => '[訂正/終了]',
			'－・－－－' => 'エ',
			'・－・－－' => 'テ',
			'－－・－－' => 'ア',
			'－・－・－' => 'サ',
			'－・－・・' => 'キ',
			'－・・－－' => 'ユ',
			'－・・・－' => 'メ',
			'・・－・－' => 'ミ',
			'－－・－・' => 'シ',
			'・－－・・' => 'ヱ',
			'－－・・－' => 'ヒ',
			'－・・－・' => 'モ',
			'・－－－・' => 'セ',
			'－－－・－' => 'ス',
			'・－・－・' => 'ン',
			'・・－－・' => '゜',
			'・－－・－' => 'ー',
			'・・－・・' => 'ト',
			'・－・・・' => 'オ',
			'・－・・－' => 'ヰ',
			'－・－－・' => 'ル',
			'・・・・' => 'ヌ',
			'－・・－' => 'マ',
			'－・－－' => 'ケ',
			'－－・・' => 'フ',
			'－－－－' => 'コ',
			'－－－・' => 'ソ',
			'・－－・' => 'ツ',
			'－－・－' => 'ネ',
			'・－・－' => 'ロ',
			'－・・・' => 'ハ',
			'－・－・' => 'ニ',
			'・－－－' => 'ヲ',
			'・－・・' => 'カ',
			'・・－・' => 'チ',
			'・・－－' => 'ノ',
			'・・・－' => 'ク',
			'－・－' => 'ワ',
			'・－－' => 'ヤ',
			'－・・' => 'ホ',
			'－－－' => 'レ',
			'・－・' => 'ナ',
			'・・・' => 'ラ',
			'・・－' => 'ウ',
			'－－・' => 'リ',
			'－－' => 'ヨ',
			'－・' => 'タ',
			'・・' => '゛',
			'・－' => 'イ',
			'・' => 'ヘ',
			'－' => 'ム',
		);
		$ret = strtr($str,$replace_pairs);
		$ret = mb_convert_kana($ret,'KVC');
		$ret = preg_replace("/[\s　]/u",'',$ret);
		return $str.'<span style="font-size:75%;">(Morse:'.$ret.')</span>';
	}

}