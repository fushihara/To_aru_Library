<?php

//■Virtual Form Library Ver3.1■//
//
//簡単にaタグでPOSTが出来るリンクを張れます。
//多次元配列に対応しています。
//JavaScriptが使えない場合はSubmitボタンで表示します。
//「postForm_0」「postForm_1」「postForm_2」…という風にフォームに名前をつけていくので、
//これらと重複するフォームを作らないように注意してください。
//
//
//◆Ver3.1
////・NOTICEエラーが出ないように改良
//
//◆Ver3.0
////・HTML特殊文字を置換せずに、エスケープされた表現で記述するように改良
//
//◆Ver2.1
////・半角スペースも置換対象に
//
//◆Ver2.0
////・多次元配列に対応
////・関数をcreateLinkのみに変更
////・データ型をhiddenのみに限定
////・シングル/ダブルクオーテーションが含まれていた場合指定文字列に置換する処理を導入
//
//◆Ver1.1
////・target属性を追加
//
/*

●例↓

require_once("./VirtualForm.php");

$vf = new VirtualForm; //ファイルの中で初めに1回だけインスタンスを生成

$data = 
	array(
		"name"=>
			"名前",
		"tokens"=>
			array(
				"access_token"=>"xxxxxxxxxxxxx",
				"access_token_secret"=>"yyyyyyyyyyyyy"
			)
	);
echo $vf->createLink($arr,"sample","./sample.php");


●実行結果↓

<script type="text/javascript"> 
<!--
document.write("<a href=\"\" onClick=\"document.postForm_1.submit();return false;\" target=\"_self\">sample</a>\n");
document.write("<form name=\"postForm_0\" method=\"POST\" action=\"./sample.php\">\n");
document.write("<input name=\"name\" type=\"hidden\" value=\"名前\" />\n");
document.write("<input name=\"tokens[access_token]\" type=\"hidden\" value=\"xxxxxxxxxxxxx\" />\n");
document.write("<input name=\"tokens[access_token_secret]\" type=\"hidden\" value=\"yyyyyyyyyyyyy\" />\n");
document.write("</form>\n");
-->
</script>
<noscript>
<form method="POST" action="./sample.php">
<input name="name" type="hidden" value="名前">
<input name="tokens[access_token]" type="hidden" value="xxxxxxxxxxxxx">
<input name="tokens[access_token_secret]" type="hidden" value="yyyyyyyyyyyyy">
<input type="submit" value="sample">
</form>
</noscript>


*/

class VirtualForm {
	
	private $formCnt;
	
	public function __construct() {
		$this->formCnt = 0;
	}
	
	//createLink(送信するデータ配列,[キャプション,[アクション[,メソッド[,ターゲット[,aタグのstyle属性の値]]]]])
	public function createLink($data,$caption='submit',$action='./',$method='POST',$target='_self',$linkStyle='',$buttonStyle='') {
	
		if (!is_array($data)) return null;
		
		$parsed = $this->arrayParse($data);
		
		$str = "";
		
		$str .= "<script type=\"text/javascript\">\n";
		$str .= "<!--\n";
		
		if (!empty($linkStyle))
		$linkStyle = sprintf(" style=\\\"%s\\\"",$linkStyle);
		
		$str .= sprintf("document.write(\"<a href=\\\"\\\" onClick=\\\"document.postForm_%s.submit();return false;\\\" target=\\\"%s\\\"%s>%s</a>\\n\");\n",
				$this->formCnt,$target,$linkstyle,$caption);
		$str .= sprintf("document.write(\"<form name=\\\"postForm_%s\\\" method=\\\"POST\\\" action=\\\"%s\\\">\\n\");\n",
				$this->formCnt,$action);
		
		foreach ($parsed as $key => $value)
		$str .= sprintf("document.write(\"<input name=\\\"%s\\\" type=\\\"hidden\\\" value=\\\"%s\\\" />\\n\");\n",$key,$value);
		
		$str .= "document.write(\"</form>\\n\");\n";
		$str .= "-->\n";
		$str .= "</script>\n";
		
		$str .= "<noscript>\n";
		$str .= "<form method=\"{$method}\" action=\"{$action}\">\n";
		
		foreach ($parsed as $key => $value)
		$str .= sprintf("<input name=\"%s\" type=\"hidden\" value=\"%s\">\n",$key,$value);
		
		if (!empty($buttonStyle)) $buttonStyle = sprintf(" style=\"%s\"",$buttonStyle);
		
		$str .= sprintf("<input type=\"submit\" value=\"%s\"%s>\n",$caption,$buttonStyle);
		$str .= "</form>\n";
		$str .= "</noscript>\n";
		
		$this->formCnt++;
		
		return $str;
		
	}
	
	private function arrayParse($data) {
		
		$query_string = http_build_query($data,'','&',PHP_QUERY_RFC3986);
		$pairs = explode('&',$query);
		$ret = array();
		foreach ($array as $item) {
			$items = explode('=',$item);
			$ret[htmlspecialchars(rawurldecode($items[0]),ENT_QUOTES)] = htmlspecialchars(rawurldecode($items[1]),ENT_QUOTES);
		}
		return $ret;
		
	}

}