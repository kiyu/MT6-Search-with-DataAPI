MT6-Search-with-DataAPI
=======================

MovableType6のDataAPIを使った検索をします

## 使い方

1. templatesにあるphpファイルをMTのインデックステンプレートに登録し出力
2. mt6swd.phpに検索したワードをgetパラメータwordとして渡す

これでmt6swd.phpがDataAPIの全てのエントリー結果からwordでフィルタリングした結果を返します。
同梱のsearch.jsのようなコードを書いてちょいちょいすると非同期通信で検索できたり、mt6swd.phpを組み込んだ検索結果ページを作る等して使ってください。

## 細かい検索設定

mt6swdは以下の2ファイルを編集する事で細かい検索設定を変更する事が出来ます。

### searchi-config.php

テストIPの設定や各種デリミタなど数値的な設定が行われています。

### searchi-initialize.php

どの値を検索対象にするか等のスイッチ的な設定が行われています。