# To\_aru\_Library

----------------

# ���ꉽ��
�l�I�Ɏ��v�������č���� __PHP__ ���C�u�����B��� __Twitter__ �����B<br>
Twitter�̗��p�K��ɒ�G������̂��ꕔ�u���Ă��܂����A���ȐӔC�ł����p���������B

----------------

# ���C�u�����ꗗ

## [SimpleOAuth]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
OAuth.php�Ɉˑ����Ȃ��A������摜�A�b�v���[�h�ɂ��Ή��������@�\���V���v���ȃ��C�u����

### �N���X�E�֐��̎d�l
_$to_ = new __SimpleOAuth__ ( string _$consumer\_key_, string _$consumer\_secret_ [, string _$oauth\_token=''_, string _$oauth\_token\_secret=''_ [, string _$oauth\_verifier=''_ ]] );<br>
_$res_->__OAuthRequest__ ( string _$url_ [, string _$method='GET'_ [, _$params=array()_ [, bool _$waitResponse=FALSE_ ]]]);

### �ڍ�
�g��������twitteroauth�Ǝ��Ă��܂����A�������<br>

- �ʏ�̃��N�G�X�g
- �������N�G�X�g(���X�|���X��ҋ@���Ȃ�)
- �摜�A�b�v���[�h�𔺂����N�G�X�g

�S�ĂɑΉ����Ă���܂��B<br>
�X�ɁAcURL���C���X�g�[������Ă��Ȃ����ł����삵�܂��B<br>
OAuthRequestImage���\�b�h�̃p�����[�^�̂����A<br>
�t�@�C���p�X��\�����̂̃L�[�̓���<br>
__�u@�v__ ��t���Ă��������B(��F@media[] @image)<br>

### ���l
BombOAuth�ŏo���邱�Ƃ͂�����Ŏ����\�Ȃ̂ŁA���ɗ��R��������΂���������g�����������B

## [BgOAuth]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
OAuth�F�؂��o�b�N�O���E���h�ōs��(XAuth�F�؂��Č�����)

### �N���X�E�֐��̎d�l
_$app_ = new __BgOAuth__ ( string _$consumer\_key_, string _$consumer\_secret_ );<br>
_$tokens_ = _$app_->__getTokens__ ( string _$username_, string _$password_ );

### �ڍ�
OAuth�F�؂��o�b�N�O���E���h�ōs���܂��B<br>
��������ƁA _$tokens['access\_token']_ &middot; *$tokens['access\_token\_secret']* �ŃA�N�Z�X�ł��܂��B<br>
���s����ƁA�G���[������\�������񂪕Ԃ���܂��B<br>

## BgOAuthMulti<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
BgOAuth�ŕ������O�C�����}���`�X���b�h�ōs��

### �N���X�E�֐��̎d�l
_$m_ = new __BgOAuthMulti__ ();<br>
_$m_ = _$m_->__addLogin__ ( string _$consumer\_key_, string _$consumer\_secret_, string _$username_, string _$password_ );<br>
_$res_ = _$m_->__exec__ ();

### �ڍ�
BgOAuth�ŕ������O�C�����}���`�X���b�h�ōs���܂��B<br>
[BgOAuthMulti.php]��[BgOAuthMultiExec.php]��[BgOAuth.php]�𓯈�K�w�ɐݒu���Ă��������B<br>

## [Follower Request]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
���A�J�E���g�̃t�H�����[���N�G�X�g���m�F/����/���ۂ���

### �N���X�E�֐��̎d�l
_$f_ = new __FollowerRequest__ ();<br>
_$res_ = _$f_->__login__ ( string _$username_, string _$password_ );<br>
_$penders_ = _$f_->__getPenders__ ();<br>
_$res_ = _$f_->__acceptPender__ ( string _$id_ );<br>
_$res_ = _$f_->__denyPender__ ( string _$id_ );<br>
$f->__acceptAll__ ();<br>

### �ڍ�
�ʏ��Twitter��API�G���h�|�C���g�ł͎����s�\�ȋ@�\�Ȃ̂ŁAKeitaiWeb�o�R�ōs���܂��B<br>
_$id_ �́Ascreen_name�ł͂Ȃ������݂̂̍P�v�I��user_id���w���܂��B<br>
�ŏ��ɕK��login���\�b�h�����s���Ă��������B<br>

## [Explode Tweet]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
�����c�C�[�g���ő��140�����ɁA�K���ȕ����ŕ������Ĕz��ŕԂ�

### �֐��̎d�l
array __explodeTweet__ ( string _$text_ )

### �ڍ�
�c�C�[�g��e�Ղɕ������邱�Ƃ��o���܂��B<br>
140������URL��p���߂��󂳂Ȃ��悤�ɋ�؂��ĕ������܂��B<br>
�S�Ă�URL��t.co�ɒZ�k����邽�߁A20�����Ƃ��Ĉ����܂��B<br>
�擪�Ƀ��v���C�w�b�_������ꍇ�A�������ꂽ�擪�ȊO�̃c�C�[�g�ɂ������t�����܂��B<br>
DM�w�b�_�̏ꍇ�͂��̕����������O���ăJ�E���g���܂��B<br>

## Array Slide<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
�z��̗v�f���w�肵�A�w�肵���������v�f�Ԃ��ړ�������

### �ڍ�
�z��̗v�f���w�肵�A�w�肵���������v�f�Ԃ��ړ������܂��B<br>
�I�v�V�����Ŕz��̗v�f�̎w����@���A�f�t�H���g�� __�u�L�[�v__ ���� __�u�Ԗځv__ �ɕύX���邱�Ƃ��o���܂��B<br>
�L�[�͐U�蒼���ꂸ�A�ێ�����܂��B<br>
�U�蒼�������ꍇ�� __array\_values__ �֐���K�p����Ƃ����ł��傤�B

### �֐��̎d�l

- [Version 1.0 �n]
 
 array __array\_slide__ ( array _$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 �z���l�n�����A�������ꂽ�z���Ԃ��܂��B

- [Version 2.0 �n]
 
 bool __array\_slide__ ( array _&$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 �z����Q�Ɠn�����A�����̌��ʂ�_���l�ŕԂ��܂��B

## [Twitter Morse]

### �T�v
���[���X�M���̃G���R�[�h/�f�R�[�h

### �֐��̎d�l
string __TwitterMorse::encode__ ( string _$str_ )<br>
string __TwitterMorse::decode__ ( string _$str_ )<br>

### �ڍ�
���[���X�M���G���R�[�h/�f�R�[�h���s���܂��B<br>
Twitter�̃��v���C�ERT�t�H�[�}�b�g�Ȃǂ��������ʂ��܂��B<br>

## [Linkify Text]

### �T�v
�e�L�X�g����͂��A�����N�𒣂������̂�Ԃ�

### �֐��̎d�l
string __linkify__ ( string _$text_ [, object _$entities = NULL_ [, bool _$get\_headers = FALSE_ , bool _$remove\_scheme = TRUE_ ]]] )

### �ڍ�
Twitter��̂�����e�L�X�g�ɍœK�ȃ����N�𒣂�܂��B<br>
**status**�̂悤�ɃG���e�B�e�B���������̂̏ꍇ�A��2�����œn���ƁA��������葬�����m�ɂȂ�܂��B<br>
��3������True���w�肷��ƁAURL�����d�Z�k����Ă����ꍇ�Ō�܂ŉ��������݂܂��B<br>
��4������False���w�肷��ƁAURL�̓��̃X�L�[�����ȗ����܂���B<br>
__�u�������a�^�O��href�����̒l�Ȃǂ͎�����p�ɂȂ��Ă���̂ŁA�K���ҏW���Ă��炨�g�����������B__

## [Virtual Form]

### �T�v
JavaScript���g���Aa�^�O�`����POST�\�ȃ����N�𐶐�����

### �N���X�E�֐��̎d�l
_$obj_ = new __VirtualForm__;<br>
echo _$obj_->__createLink__ ( array _$data_ [ , string _$caption = "submit"_ [ , string _$action= "./"_ [ , string _$method = "POST"_ [ , string _$target = "\_self"_ [ , string _$linkStyle = ""_ [ , string _$buttonStyle = ""_ ]]]]]] );

### �ڍ�
�ȒP��a�^�O��POST���o���郊���N�𒣂�܂��B<br>
�������z��ɑΉ����Ă��܂��B<br>
JavaScript���g���Ȃ��ꍇ��Submit�{�^���ŕ\�����܂��B<br>
__�upostForm_0�v�upostForm_1�v�upostForm_2�v�c__ �Ƃ������Ƀt�H�[���ɖ��O�����Ă����̂ŁA<br>
�����Əd������t�H�[�������Ȃ��悤�ɒ��ӂ��Ă��������B

[SimpleOAuth]: https://github.com/Certainist/To_aru_Library/blob/master/SimpleOAuth.php
[BgOAuth]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuth.php
[BgOAuth.php]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuth.php
[BgOAuthMulti.php]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuthMulti.php
[BgOAuthMultiExec.php]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuthMultiExec.php
[Follower Request]: https://github.com/Certainist/To_aru_Library/blob/master/FollowerRequest.php
[Explode Tweet]: https://github.com/Certainist/To_aru_Library/blob/master/explodeTweet.php
[Twitter Morse]: https://github.com/Certainist/To_aru_Library/blob/master/TwitterMorse.php
[Version 1.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-1.2.php
[Version 2.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-2.2.php
[Linkify Text]: https://github.com/Certainist/To_aru_Library/blob/master/linkifyText.php
[Virtual Form]: https://github.com/Certainist/To_aru_Library/blob/master/VirtualForm.php
