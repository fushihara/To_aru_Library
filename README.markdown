# To\_aru\_Library

----------------

# ���ꉽ��
�l�I�Ɏ��v�������č���� __PHP__ ���C�u�����B��� __Twitter__ �����B

----------------

# ���C�u�����ꗗ

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
__�����ȐӔC�ł��肢���܂�__

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

## [Linkify Text]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

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

[BgOAuth]: https://github.com/Certainist/To_aru_Library/blob/master/BgOAuth.php
[Explode Tweet]: https://github.com/Certainist/To_aru_Library/blob/master/explodeTweet.php
[Version 1.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-1.2.php
[Version 2.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-2.2.php
[Linkify Text]: https://github.com/Certainist/To_aru_Library/blob/master/linkifyText.php
[Virtual Form]: https://github.com/Certainist/To_aru_Library/blob/master/VirtualForm.php
