<?php

//**************************************************
//************ BgOAuthMulti Version 1.0 ************
//**************************************************
//
//                                作者: @To_aru_User
//
// BgOAuthで高速に複数のログインを行うためのライブラリです。
// BgOAuth.php,BgOAuthMulti.phpを同一階層に設置してください。
// こちらはBgOAuthMulti.phpからPOSTリクエストを受け取って実行します。
//

require_once(dirname(__FILE__).'/BgOAuth.php');

if (isset($_POST['BgOAuthObject']))
	$BgOAuthObject = $_POST['BgOAuthObject'];

switch(true) {
	case (!isset($BgOAuthObject)):
		echo 'error='.rawurlencode('BgOAuthObjectがありません');
		exit();
	case (!is_array($BgOAuthObject)):
		echo 'error='.rawurlencode('BgOAuthObjectが配列ではありません');
		exit();
	case (!isset($BgOAuthObject['consumer_key'])):
		echo 'error='.rawurlencode('consumer_keyがありません');
		exit();
	case (!isset($BgOAuthObject['consumer_secret'])):
		echo 'error='.rawurlencode('consumer_secretがありません');
		exit();
	case (!isset($BgOAuthObject['username'])):
		echo 'error='.rawurlencode('usernameがありません');
		exit();
	case (!isset($BgOAuthObject['password'])):
		echo 'error='.rawurlencode('passwordがありません');
		exit();
}

$bgo = new BgOAuth($BgOAuthObject['consumer_key'],$BgOAuthObject['consumer_secret']);
$res = $bgo->getTokens($BgOAuthObject['username'],$BgOAuthObject['password']);

if (!is_array($res)) {
	echo 'error='.rawurlencode($res);
	exit();
}

echo 'access_token='.$res['access_token'];
echo '&';
echo 'access_token_secret='.$res['access_token_secret'];
 