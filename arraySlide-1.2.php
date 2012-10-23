<?php

//■Array Slide Library Ver1.1■//
//
//●概要
// * array_slide関数 *
//
//第1引数に指定した配列の、第2引数に指定したキーの要素を、
//第3引数に指定した分だけずらした配列を返します。
//存在する領域を超えてずらそうとすると自動的に補正されます。
//第4引数の「search target with order」オプションをTrueに設定した場合、
//第2引数に指定された値で「キー」に関係なく「番目」をもとに検索します。
//ずらす対象が存在しなかったときはFalseが返されます。
//
//◆Ver1.2
//・オブジェクト対応の不完全な部分を修正・NOTICEエラー回避
//
//◆Ver1.1
//・オブジェクトにも対応できるようにメソッド、プロセスの順序を変更
//
//
//
//●例
//$arr = array("ド"=>"ドーナツ","レ"=>"レモン","ミ"=>"ミカン","ファ"=>"ふぁぼれよ","ソ"=>"蒼井そら");
//
//
//
//例1. $arrのキー「レ」に該当する要素の位置を後ろに2ずらす
//
//var_dump(array_slide($arr,"レ",2));
//
//結果1.
//
//array("ド"=>"ドーナツ","ミ"=>"ミカン","ファ"=>"ふぁぼれよ","レ"=>"レモン","ソ"=>"蒼井そら")
//
//
//
//例2. $arrのキー「ソ」に該当する要素の位置を手前に50ずらす
//(領域を超えるので自動的に補正される)
//
//var_dump(array_slide($arr,"ソ",-50));
//
//結果2.
//
//array("ソ"=>"蒼井そら","ド"=>"ドーナツ","レ"=>"レモン","ミ"=>"ミカン","ファ"=>"ふぁぼれよ")
//
//
//
//例3. $arrの(0から数えて)3番目の要素の位置を手前に2ずらす
//
//var_dump(array_slide($arr,3,-2,True));
//
//結果3.
//
//array("ド"=>"ドーナツ","ファ"=>"ふぁぼれよ","レ"=>"レモン","ミ"=>"ミカン","ソ"=>"蒼井そら")
//
//
//

function array_slide($array,$key,$amount,$search_target_with_order=false) {

	//引数チェック
	if ((!is_array($array)&&!is_object($array)) || !is_integer($amount) || !is_bool($search_target_with_order)) return false;
	
	//キーを失わないように次元を上げ、連想配列でない配列を作る
	$cnt = 0;
	$parent = array();
	foreach ($array as $_key => $value) {
		//オプションの有無で場合分け
		switch($search_target_with_order) {
			case false:
				if ($_key==$key) {
					//ターゲット取得
					$target = array($_key=>$value);
					//番目を取得
					$pos = $cnt;
				}
				break;
			default:
				if ($cnt===$key) {
					//ターゲット取得
					$target = array($_key=>$value);
					//番目を取得
					$pos = $cnt;
				}
		}
		$parent[] = array($_key=>$value);
		$cnt++;
	}
	
	//ターゲットが見つからなかったときはFalseを返す
	if (!isset($target)) return false;
	
	//個数をカウント
	$count = count($parent);
	
	//必要以上にスライドする場合、必要最小限のスライド量に修正
	$new_pos = $pos + $amount;
	if ($new_pos < 0) $new_pos = 0;
	elseif ($new_pos >= $count) $new_pos = $count - 1;
	$amount = $new_pos - $pos;
	
	//要素のずらす量で場合分けし、一時的に「追加」の形を取る
	switch (true) {
		
		//±0
		case $amount == 0 :
			//配列を返す
			return $array;
		
		//＋
		case $amount > 0 :
			array_splice($parent,$pos,1);
			array_splice($parent,$new_pos,0,array($target));
			break;
			
		//－
		default :
			array_splice($parent,$new_pos,0,array($target));
			array_splice($parent,$pos+1,1);
			
	}
	
	//上げた次元をもとに戻して返す
	if (is_array($array)) {
		$new_arr = array();
		foreach ($parent as $child) {
			foreach ($child as $_key => $value) {
				$new_arr[$_key] = $value;
			}
		}
		return $new_arr;
	}
	if (is_object($array)) {
		$new_obj = new stdClass;
		foreach ($parent as $child) {
			foreach ($child as $_key => $value) {
				$new_obj->$_key = $value;
			}
		}
		return $new_obj;
	}
	
	return false;
	
}