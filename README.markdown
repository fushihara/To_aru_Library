# To\_aru\_Library

----------------

# ���ꉽ��
�l�I�Ɏ��v�������č����__PHP__���C�u�����B���__Twitter__�����B

----------------

# ���C�u�����ꗗ

## [Explode Tweet]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
�����c�C�[�g���ő��140�����ɁA�K���ȕ����ŕ������Ĕz��ŕԂ�

### �֐��̎d�l
array __explodeTweet__ ( string _$text_ )

### �ڍ�
�c�C�[�g�{����e�Ղɕ������邱�Ƃ��o���܂��B<br>
140�����ɃX�N���[���l�[����URL�A�p���߂��󂳂Ȃ��悤�ɋ�؂��ĕ������܂��B<br>
�S�Ă�URL��t.co�ɒZ�k����邽�߁A20�����Ƃ��Ĉ����܂��B<br>
�擪�Ƀ��v���C�w�b�_������ꍇ�A�������ꂽ�S�Ă̖{�������ɂ����t�����܂��B

## Array Slide<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
�z��̗v�f���w�肵�A�w�肵���������v�f�Ԃ��ړ�������

### �ڍ�
�T�v�̒ʂ�ł��B�z�񑀍�ɗD�ꂽPHP�̊֐��ł����A���̖ړI�ɊY������֐��������炸�A<br>
���ꂪ�ǂ����Ă��K�v�Ȏ�������A�����ėp�����������Ȃ��̂Ȃ̂ŁA���C�u�����ɂ��Ă݂܂����B<br>
�I�v�V�����ŁA�z��̗v�f�̎w����@���A�f�t�H���g��__�u�L�[�v__����__�Ԗ�__�ɕύX���邱�Ƃ��o���܂��B<br>

### �֐��̎d�l

- [Version 1.0 �n]

 array __array\_slide__ ( array _$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 �z���l�n�����A�������ꂽ�z���Ԃ��܂��B

- [Version 2.0 �n]

 bool __array\_slide__ ( array _&$array_ , mixed _$key_ , int _$amount_ [, bool _$search\_target\_with\_order = FALSE_ ] )
 
 �z���_�Q�Ɠn��_���A�����̌��ʂ�_���l�ŕԂ��܂��B

## [Entify Text]<img src="http://ishisuke007.yh.land.to/push.png" style="vertical-align:bottom;" height="50">

### �T�v
�e�L�X�g���G���e�B�e�B���������̂�Ԃ�

### �֐��̎d�l
string __entify__ ( string _$text_ [, SimpleXMLElement _$entities = NULL_ [, bool _$get\_headers = FALSE_ ]] )

### �ڍ�
Twitter��̂�����e�L�X�g���G���e�B�e�B�����܂��B<br>
*status*�̂悤�ɃG���e�B�e�B���������̂̏ꍇ�A��2�����œn���ƁA��������葬�����m�ɂȂ�܂��B<br>
SimpleXMLElement�Ə����Ă͂��܂����AstdClass�ł����Ȃ��Ǝv���܂��i�����j�B
��3������True���w�肷��ƁAURL�����d�Z�k����Ă����ꍇ�Ō�܂ŉ��������݂܂��B<br>
__�u�������a�^�O��href�����̒l�Ȃǂ͎�����p�ɂȂ��Ă���̂ŁA�K���ҏW���Ă��炨�g�����������B__

## [Virtual Form]

### �T�v
JavaScript���g���Aa�^�O�`����POST�\�ȃ����N�𐶐�����

### �N���X�E�֐��̎d�l
_$obj_ = new __VirtualForm__;<br>
echo _$obj_->__createLink__ ( array _$data_ , string _$caption_ , string _$action_ [ , string _$method = "POST"_ [ , string _$target = "\_self"_ [ , string _$linkStyle_ [ , string _$buttonStyle_ ]]]] );

### �ڍ�
�ȒP��a�^�O��POST���o���郊���N�𒣂�܂��B<br>
�������z��ɑΉ����Ă��܂��B<br>
JavaScript���g���Ȃ��ꍇ��Submit�{�^���ŕ\�����܂��B<br>
__�upostForm_1�v�upostForm_2�v�upostForm_3�v�c__�Ƃ������Ƀt�H�[���ɖ��O�����Ă����̂ŁA<br>
�����Əd������t�H�[�������Ȃ��悤�ɒ��ӂ��Ă��������B

[ExplodeTweet]: https://github.com/Certainist/To_aru_Library/blob/master/explodeTweet.php
[Version 1.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-1.1.php
[Version 2.0 �n]: https://github.com/Certainist/To_aru_Library/blob/master/arraySlide-2.1.php
[Entify Text]: https://github.com/Certainist/To_aru_Library/blob/master/entifyText.php
[Virtual Form]: https://github.com/Certainist/To_aru_Library/blob/master/VirtualForm.php
