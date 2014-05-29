<?php
/*
	DataAPI(v1) でエントリーリストを取得してパラメータからフィルタリングを行う
	パラメータ:
		word: 検索ワード
		type: "or" or "and" 検索typeの指定。未指定は"or"
		exclude: 検索対象外にするカスタムフィールドのベースネーム。未指定は除外しない
		include: 検索対象にするカスタムフィールドのベースネーム。未指定は全て対象にする
 */
	header('Content-Type: text/javascript; charset=utf-8');
	ini_set("display_errors",1);

	$SEARCHI_INITIALIZE = 'searchi-initialize.php';
	$SEARCHI_CONFIG = 'searchi-config.php';

	include_once $SEARCHI_INITIALIZE;
	include_once $SEARCHI_CONFIG;

	//Delimiter
	$RANDOM_DELIMITER = random_string(10);

	//Access
	$server = $_SERVER;
	$ip = $server["REMOTE_ADDR"];
	$host = array('127.0.0.1',$TEST_IP);

	//Param
	$param_word = (isset($_GET['word'])) ? $_GET['word'] : false;
	$param_type = (isset($_GET['type'])) ? $_GET['type'] : null;
	if(is_null($param_type)){
		$param_type = ($SEARCH_MODE_OR === true) ? 'or' : 'and';
	}
	$param_exclude = (isset($_GET['exclude'])) ? explode($RANDOM_DELIMITER,str_replace($PARAM_DELIMITER,$RANDOM_DELIMITER,$_GET['exclude'])) : array();
	$param_include = (isset($_GET['include'])) ? explode($RANDOM_DELIMITER,str_replace($PARAM_DELIMITER,$RANDOM_DELIMITER,$_GET['include'])) : array();

	//API
	if(!isset($JSON_URL)){ $JSON_URL = null;}
	$jsonurl = ($USE_DATA_API) ? 'http://<mt:CGIHost><mt:CGIRelativeURL>'.$API_NAME.'/v1/sites/<mt:BlogID>/entries' : $JSON_URL ;

	//State
	$state = 0;

	//Respons
	$respons = array(
		'totalResults' => 0,
		'items' => array()
	);

	//Error判定
	if(!in_array($ip,$host)){
		//許可されていないホストアクセス
		$state = -1;
	}elseif($param_word == false){
		//検索ワードが無いアクセス
		$state = -2;
	}else{
		if(is_null($jsonurl)){
			//アクセスデータが設定されていない
			$state = -4;
		}else{
			$json = file_get_contents($jsonurl,true);
			if($json == false){
				//DataAPIが値を返せなかった
				$state = -3;
			}
		}
	}

	if($state < 0){
		//stateが負数の場合はエラー
		$respons['error'] = true;
		$respons['erroeCode'] = $state;
		switch ($state) {
			case -1:
				$respons['errorMessage'] = 'Unauthorized access';
				break;
			case -2:
				$respons['errorMessage'] = 'No search word';
				break;
			case -3:
				$respons['errorMessage'] = 'There is no API response';
				break;
			case -4:
				$respons['errorMessage'] = 'There is an error in the setting';
				break;
		}
	}else{
		//検索ワードを整理
		$words = explode($RANDOM_DELIMITER,str_replace($WORD_DELIMITER,$RANDOM_DELIMITER,$param_word));
		$data = json_decode($json,true);

		//検索するテキストを整形する
		foreach($data['items'] as $key => $entry){
			$text = '';

			//基本項目の処理
			if(isset($TITLE_SEARCH) && $TITLE_SEARCH && isset($entry['title'])){
				$text .= $entry['title'];
			}
			if(isset($AUTHOR_SEARCH) && $AUTHOR_SEARCH && isset($entry['author']['displayName'])){
				$text .= $entry['author']['displayName'];
			}
			if(isset($TAGS_SEARCH) && $TAGS_SEARCH && isset($entry['tags'])){
				$text .= ' '.implode(' ',$entry['tags']);
			}
			if(isset($CATEGORIES_SEARCH) && $CATEGORIES_SEARCH && isset($entry['categories'])){
				$text .= ' '.implode(' ',$entry['categories']);
			}
			if(isset($EXCERPT_SEARCH) && $EXCERPT_SEARCH && isset($entry['excerpt'])){
				$text .= $entry['excerpt'];
			}
			if(isset($KEYWORDS_SEARCH) && $KEYWORDS_SEARCH && isset($entry['keywords'])){
				$text .= $entry['keywords'];
			}
			if(isset($BODY_SEARCH) && $BODY_SEARCH && isset($entry['body'])){
				$text .= $entry['body'];
			}
			if(isset($MORE_SEARCH) && $MORE_SEARCH && isset($entry['more'])){
				$text .= $entry['more'];
			}

			//カスタムフィールドの処理
			if(isset($CUSTOMFIELD_SEARCH) && $CUSTOMFIELD_SEARCH){
				if(isset($SEARCHS_CUSTOMFIELD) && is_array($SEARCHS_CUSTOMFIELD) && count($SEARCHS_CUSTOMFIELD)>0){
					for($i=0;$i<count($entry['customFields']);$i++){
						if(in_array($entry['customFields'][$i],$SEARCHS_CUSTOMFIELD)){
							$text .= ' '.$entry['customFields'][$i]['value'];
						}
					}
				}else{
					for($i=0;$i<count($entry['customFields']);$i++){
						$text .= ' '.$entry['customFields'][$i]['value'];
					}
				}
			}

			if(textSearch($words,$text,$param_type)){
				array_push($respons['items'],$entry);
			}
		}
		$respons['totalResults'] = count($respons['items']);
	}
	print json_encode($respons);


	/**
	* textSearch function
	*
	* @param array $words 検索するワードの配列
	* @param string $text 検索されるテキスト
	* @param string $type 検索モード。orかandで、初期値はor
	* @return bool $return 検索条件を満たせばtrue、満たさなければfalse
	*/
	function textSearch($words,$text,$type='or'){
		$return = null;
		$count = count($words);
		$pattern = '';
		for($i=0;$i<$count;$i++){
			if($i!=0){
				$pattern .= '|';
			}
			$pattern .= str_replace('/','\/',$words[$i]);
		}
		preg_match_all("/".$pattern."/",$text,$result);
		if($type == 'and'){
			//and検索の場合はパターンマッチ数をカウントして判断する
			if(count(array_unique($result[0])) == $count){
				$return = true;
			}
		}else{
			//or検索の場合はマッチ数が正数かで判断する
			$return = (count(array_unique($result[0])) > 0) ? true : false;
		}
		return $return;
	}


	/**
	* randomText function
	*
	* @param int $length 生成するテキストの長さ
	* @param string $text サフィックステキスト
	* @return string $return ランダムなテキストを返す
	*/
	function random_string($length = 8,$text = ''){
		static $char = '0123456789abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_/~';
		$charlength = strlen($char);
		for ($i = 0;$i < $length;$i++){
			$text .= $char[mt_rand(0,$charlength-1)];
		}
		return $text;
	}