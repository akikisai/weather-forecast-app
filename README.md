## 概要
地名情報から検索し、天気情報を取得するアプリで、履歴ワードからも便利に天気情報が取得できます。

## 使用した技術
使用した技術は以下になります。

| 項目 | 使用したもの |
|:---:|:---|
|環境 |Docker, nginx|
|言語 |PHP, Javascript |
|DB |Mysql(phpMyAdmin) |
|ライブラリー |jQuery |
|API |open-meteo, Geolocation, HeartRailsGeo |
|その他 |html, css, vscode |

今回使用したAPIの機能は以下になります。
- open-meteo：緯度と経度から、天気情報（天気、気温などなど）を取得できます。
- Geolocation：現在地の緯度と経度の情報を取得できます。
- HeartRailsGeo：緯度と経度から、市町村情報などを取得できます。

## 制作期間
企画を含め、五日間かかりました。

## 外観
**1ページめ、主に検索及び登録をします。**
![page1](https://user-images.githubusercontent.com/85460645/162576337-1383fbd8-8c10-4a04-ab36-a4b51fedad06.png)

**2ページ目、天気を取得して表示します。**
![page2](https://user-images.githubusercontent.com/85460645/162576338-dc68b187-5cbf-4874-9541-912f834bb803.png)

## 機能
- 機能1
![func1](https://user-images.githubusercontent.com/85460645/162576340-5d667cf3-2f11-4132-8254-72b0221a0e9d.png)
1. 都道府県から検索できます。
2. 検索したら、履歴が残り、セレクトボックスの下に名前（検索回数）という形で表示されます。
3. この履歴をタッチすると検索でき、天気情報が表示できます。

![func2](https://user-images.githubusercontent.com/85460645/162576341-829948ed-c859-42fc-8cbc-421e51c8b673.png)

![func3](https://user-images.githubusercontent.com/85460645/162576336-b23fd4ec-1e54-493c-b069-c2dfdf58ab32.png)
