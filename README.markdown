# To\_aru\_Library

----------------

# これ何ぞ
個人的に需要があって作った __PHP__ ライブラリ。主に __Twitter__ 向け。

----------------

# ライブラリ一覧

## [BgOAuth]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### 概要
OAuth認証をバックグラウンドで行う(XAuth認証を再現する)

### クラス・関数の仕様
_$app_ = new __BgOAuth__ ( string _$consumer\_key_, string _$consumer\_secret_ );<br>
_$tokens_ = _$app_->__getTokens__ ( string _$username_, string _$password_ );

### 詳細
OAuth認証をバックグラウンドで行います。<br>
成功すると、 _$tokens['access\_token']_ &middot; *$tokens['access\_token\_secret']* でアクセスできます。<br>
失敗すると、エラー原因を表す文字列が返されます。<br>
__※自己責任でお願いします__

## [Explode Tweet]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### 概要
長いツイートを最大で140字毎に、適当な部分で分割して配列で返す

### 関数の仕様
array __explodeTweet__ ( string _$text_ )

### 詳細
ツイートを容易に分割することが出来ます。<br>
140字毎にURLや英文節を壊さないように区切って分割します。<br>
全てのURLはt.coに短縮されるため、20文字として扱われます。<br>
先頭にリプライヘッダがある場合、分割された先頭以外のツイートにもそれを付加します。<br>
DMヘッダの場合はその文字数を除外してカウントします。<br>


## Array Slide<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### 概要
配列の要素を指定し、指定した分だけ要素間を移動させる

### 詳細
配列の要素を指定し、指定した分だけ要素間を移動させます。<br>
オプションで配列の要素の指定方法を、デフォルトの __「キー」__ から __「番目」__ に変更することが出来ます。<br>
キーは振り直されず、維持されます。<br>
振り直したい場合は __array\_values__ 関数を適用するといいでしょう。

### 関数の仕様

- [Version 1.0 系]
 
 array __array\_slide__ ( array _$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 配列を値渡しし、処理された配列を返します。

- [Version 2.0 系]
 
 bool __array\_slide__ ( array _&$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 配列を参照渡しし、処理の結果を論理値で返します。

## [Linkify Text]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### 概要
テキストを解析し、リンクを張ったものを返す

### 関数の仕様
string __linkify__ ( string _$text_ [, object _$entities = NULL_ [, bool _$get\_headers = FALSE_ , bool _$remove\_scheme = TRUE_ ]]] )

### 詳細
Twitter上のあらゆるテキストに最適なリンクを張ります。<br>
**status**のようにエンティティ情報を持つものの場合、第2引数で渡すと、処理がより速く正確になります。<br>
第3引数にTrueを指定すると、URLが多重短縮されていた場合最後まで解決を試みます。<br>
第4引数にFalseを指定すると、URLの頭のスキームを省略しません。<br>
__置換されるaタグのhref属性の値などは自分専用になっているので、必ず編集してからお使いください。__

## [Virtual Form]

### 概要
JavaScriptを使い、aタグ形式でPOST可能なリンクを生成する

### クラス・関数の仕様
_$obj_ = new __VirtualForm__;<br>
echo _$obj_->__createLink__ ( array _$data_ [ , string _$caption = "submit"_ [ , string _$action= "./"_ [ , string _$method = "POST"_ [ , string _$target = "\_self"_ [ , string _$linkStyle = ""_ [ , string _$buttonStyle = ""_ ]]]]]] );

### 詳細
簡単にaタグでPOSTが出来るリンクを張れます。<br>
多次元配列に対応しています。<br>
JavaScriptが使えない場合はSubmitボタンで表示します。<br>
__「postForm_0」「postForm_1」「postForm_2」…__ という風にフォームに名前をつけていくので、<br>
これらと重複するフォームを作らないように注意してください。

[BgOAuth]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuth.php
[Explode Tweet]: https://github.com/Certainist/To_aru_Library/blob/master/explodeTweet.php
[Version 1.0 系]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-1.2.php
[Version 2.0 系]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-2.2.php
[Linkify Text]: https://github.com/Certainist/To_aru_Library/blob/master/linkifyText.php
[Virtual Form]: https://github.com/Certainist/To_aru_Library/blob/master/VirtualForm.php
