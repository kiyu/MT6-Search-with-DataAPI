<?php
/* ------------------------------------------------
 * MovableType DataAPI v1 Search Module Config File
 * ------------------------------------------------ */

//テストアクセス用IP。テストで直に叩く事があれば使用しているIPを入れておく
$TEST_IP = '210.248.202.32';

//DataAPI.CGIのファイル名。リネームしている場合は変更
$API_NAME = 'mt-data-api.cgi';

//パラメータデリミタ。パラメータ内で複数指定する場合の区切りとなるデリミタにする
$PARAM_DELIMITER = array(
	','
);

//検索ワードデリミタ。検索ワードを複数指定する場合の区切りとなるデリミタにする
$WORD_DELIMITER = array(
	',',
	'　',
	' '
);

//検索対象に含めるカスタムフィールドのベースネームセット
/* CUSTOMFIELD_SEARCH が ture の場合のみ有効
 * 空の場合や定義されていない場合は全てのフィールドを検索対象にします */
//$SEARCH_CUSTOMFIELDS = array('basename1','basename2','basename3');