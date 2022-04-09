## ソフト
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/install/)

## ディレクトリ構成
nginx,php,db: 各環境の設定ファイル  
source: phpのソース

## コンテナ一覧
nginx: HTTPサーバ  
php: アプリケーションサーバ  
mysql: データベースサーバ(接続情報は[ファイル](./internship/docker-compose.yml)参照)  
phpmyadmin: MySQLのGUIツール