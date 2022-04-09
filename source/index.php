<?php
//読み込み
require_once 'config/config.php';

//select box内の都道府県名配列を用意する
$prefecture_name = array(
  '北海道', '青森県', '岩手県', '宮城県',
  '秋田県', '山形県', '福島県', '茨城県',
  '栃木県', '群馬県', '埼玉県', '千葉県',
  '東京都', '神奈川県', '新潟県', '富山県',
  '石川県', '福井県', '山梨県', '長野県',
  '岐阜県', '静岡県', '愛知県', '三重県',
  '滋賀県', '京都府', '大阪府', '兵庫県',
  '奈良県', '和歌山県', '鳥取県', '島根県',
  '岡山県', '広島県', '山口県', '徳島県',
  '香川県', '愛媛県', '高知県', '福岡県',
  '佐賀県', '長崎県', '熊本県', '大分県',
  '宮崎県', '鹿児島県', '沖縄県'
);

//データベース接続
try {
  $db = new PDO('mysql:host=' . HOSTNAME . ';dbname=' . DATABASE, USERNAME, PASSWORD);
  $msg = "接続に成功しました。";
} catch (PDOException $e) {
  $isConnect = false;
  echo $e;
  $msg = "MySQL への接続に失敗しました。<br>(" . $e->getMessage() . ")";
}

//select box内の市町村名配列を用意する
$municipalities_name = array();
//市町村名取得
//sql文を作る
$sql_municipalities = "SELECT * FROM municipalities ORDER BY id DESC";
$stm = $db->prepare($sql_municipalities); //プリペアードステートメントを作る
$stm->execute(); //実行
//結果の取得(連想配列で受け取る)
$municipalities_data = $stm->fetchAll(PDO::FETCH_ASSOC);
//取得した結果（テーブル）の中を捜査していく、該当県に当たれば各情報を代入する
foreach ($municipalities_data as $row) {
  array_push($municipalities_name, $row['name']);
}

//現在地登録フォームからのポスト送信
if ($_POST["register_name"] == "" && $_POST["lat"] == "" && $_POST["lng"] == "") {
} else {
  $regist_name = $_POST["register_name"];
  $lat = $_POST["lat"];
  $lng = $_POST["lng"];

  //登録機能
  //検索する地名はmunicipalitiesにあるかどうかのsql文
  $sql_municipalities = "SELECT * FROM municipalities WHERE `name` = '{$regist_name}'";
  $stm = $db->prepare($sql_municipalities);
  $stm->execute();
  //結果の取得
  $municipalities_data = $stm->fetchAll(PDO::FETCH_ASSOC);

  //結果が0以上（存在）であれば、アラートで知らせる
  if (count($municipalities_data) > 0) {
    $alert = "<script type='text/javascript'>alert('登録済みの地名です。ほかの地名を登録してください。');</script>";
    // アラートを表示する
    echo $alert;
  } else {
    //結果が0であれば、その履歴がない、データを挿入する
    $sql_insert = "INSERT municipalities (`name`,latitude,longitude) VALUES('{$regist_name}','{$lat}','{$lng}')";
    $stm = $db->prepare($sql_insert);
    $stm->execute();
    $alert = "<script type='text/javascript'>alert('登録しました。');</script>";
    // アラートを表示する
    echo $alert;
  }
}
?>


<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>天気情報検索サービス</title>
  <link href="./style.css" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700|Noto+Sans+JP:400,700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-1.6.4.js"></script>
</head>

<body>


  <div class="main_content">
    <div class="left_block">
      <h1>天気情報検索サービス</h1>
      <div class="regist_flex">

        <h2>情報登録</h2>
        <!-- 自由登録フォーム -->
        <div class="regist_form">
          <form action="" method="post">
            <div>
              <label class="required" for="register_name">地名</label>
              <input class="text-box" id="register_name" name="register_name" maxlength="5" required>
            </div>
            <div>
              <label class="required" for="lat">緯度</label>
              <input class="text-box" id="lat" name="lat" required>
            </div>
            <div>
              <label class="required" for="lng">経度</label>
              <input class="text-box" id="lng" name="lng" required>
            </div>
            <input type="submit" value="登録する" class="btn">
          </form>


          <!-- 現在地情報自動取得ボタン -->
          <button id="btn" class="button05">
            現在地<br>情報を<br>取得する
          </button>

        </div>


      </div>
    </div>

    <div class="search_content">
      <!-- 都道府県検索フォーム -->
      <div class="prefecture_search">
        <form method="post" action="show.php">
          <div>
            <label for="name">
              <h2>都道府県から調べる</h2>
            </label>
          </div>
          <!-- セレクトボックス -->
          <div class="selectdiv">
            <select name="prefecture">
              <?php
              foreach ($prefecture_name as $name) {
                echo "<option value='{$name}'>{$name}</option>";
              }
              ?>
            </select>
          </div>
          <input type="submit" value="検索する" class="btn1">
        </form>

        <!-- 履歴スペース1 -->
        <div class="log_space">
          <div class="required log_text">履歴ワード(回数)</div>
          <div class="log_word">
            <?php
            //履歴情報取得
            //sql文を作る
            $sql_history = "SELECT name, COUNT(name) AS num FROM prefecture_log GROUP BY name ORDER BY num DESC";
            $stm = $db->prepare($sql_history);
            $stm->execute();
            //結果の取得(連想配列で受け取る)
            $history_data = $stm->fetchAll(PDO::FETCH_ASSOC);
            //取得した結果（テーブル）の中を捜査していく、該当県に当たれば各情報を代入する
            foreach ($history_data as $row) {
              echo "<form method='post' action='show.php'>
          <p><input type='hidden' name='prefecture' value='" . $row['name'] . "'></p>
          <p><input class ='log_btn' type='submit' value='" . $row['name'] . " (" . $row['num'] . ")'></p>
          </form>";
            }

            ?>
          </div>
        </div>
      </div>

      <!-- 登録地から検索するフォーム -->
      <div class="regist_place_search">
        <form method="post" action="show.php">
          <div>
            <label for="name">
              <h2>登録地から調べる</h2>
            </label>
          </div>
          <!-- セレクトボックス -->
          <div class="selectdiv">
            <select name="municipalities">
              <?php
              foreach ($municipalities_name as $name) {
                echo "<option value='{$name}'>{$name}</option>";
              }
              ?>
            </select>
          </div>
          <input type="submit" value="検索する" class="btn1">
        </form>

        <!-- 履歴スペース2 -->
        <div class="log_space">
          <div class="required log_text">履歴ワード(回数)</div>
          <div class="log_word">
            <?php
            //履歴情報取得
            //sql文を作る
            $sql_history = "SELECT name, COUNT(name) AS num FROM municipalities_log GROUP BY name ORDER BY num DESC";
            $stm = $db->prepare($sql_history);
            $stm->execute();
            //結果の取得(連想配列で受け取る)
            $history_data = $stm->fetchAll(PDO::FETCH_ASSOC);
            //取得した結果（テーブル）の中を捜査していく、該当県に当たれば各情報を代入する
            foreach ($history_data as $row) {
              echo "<form method='post' action='show.php'>
          <p><input type='hidden' name='municipalities' value='" . $row['name'] . "'></p>
          <p><input class ='log_btn' type='submit' value='" . $row['name'] . " (" . $row['num'] . ")'></p>
          </form>";
            }
            ?>
          </div>
        </div>
      </div>
    </div>


    <script>
      // 自動取得ボタンを押した時の処理
      var name_s;
      document.getElementById("btn").onclick = function() {

        //隠れボックスから緯度経度を取得し、登録フォームに設置する
        document.getElementById('lat').value = document.getElementById('lat_data').value;
        document.getElementById('lng').value = document.getElementById('lng_data').value;

        let lat = document.getElementById('lat').value;
        let lng = document.getElementById('lng').value;

        //緯度経度から市町村情報を取得するAPI
        let api_url = 'http://geoapi.heartrails.com/api/json?method=searchByGeoLocation&x=' + lng + '&y=' + lat;

        //jqueryを使用してurlからjsonデータを取得し、必要のデータを登録フォームに設置する
        (function($) {
          $(function() {
            $.ajax({
              url: api_url,
              type: 'GET',
              dataType: "json",
            }).done(function(data) {
              // success
              //取得jsonデータ
              var data_stringify = JSON.stringify(data);
              var data_json = JSON.parse(data_stringify);
              //jsonデータから各データを取得
              var data_city = data_json.response.location[0].city; // jsonの構造ごとに変更してください
              //出力
              $("#register_name").val(data_city);
            }).fail(function(data) {
              // error
              console.log('error');
            });
          });
        })(jQuery);
      };

      //Geolocation API
      if (navigator.geolocation) {
        // 現在地を取得
        navigator.geolocation.getCurrentPosition(

          // [第1引数] 取得に成功した場合の関数
          function(position) {
            // 取得したデータの整理
            var data = position.coords;

            // データの整理
            var lat = data.latitude;
            var lng = data.longitude;

            // HTMLへの書き出し
            document.getElementById('lat_data').value = lat;
            document.getElementById('lng_data').value = lng;
          },

          // [第2引数] 取得に失敗した場合の関数
          function(error) {
            // エラーコード(error.code)の番号
            // 0:UNKNOWN_ERROR				原因不明のエラー
            // 1:PERMISSION_DENIED			利用者が位置情報の取得を許可しなかった
            // 2:POSITION_UNAVAILABLE		電波状況などで位置情報が取得できなかった
            // 3:TIMEOUT					位置情報の取得に時間がかかり過ぎた…

            // エラーコードに対応したメッセージ
            var errorInfo = [
              // 0:UNKNOWN_ERROR
              "原因不明のエラーが発生しました…。\n手動で情報入力してください。",
              // 1:PERMISSION_DENIED
              "位置情報の取得が許可されませんでした…。\n位置情報を許可するか、手動で入力してください。",
              // 2:POSITION_UNAVAILABLE
              "電波状況などで位置情報が取得できませんでした…。\nもう一度試してください。",
              // 3:TIMEOUT
              "位置情報の取得に時間がかかり過ぎてタイムアウトしました…。\n手動で情報入力してください。"
            ];

            // エラー番号
            var errorNo = error.code;

            // エラーメッセージアラート
            var errorMessage = "[エラー番号: " + errorNo + "]\n" + errorInfo[errorNo];
            alert(errorMessage);
          },
        );
      }
      // 対応していない場合
      else {
        // エラーメッセージアラート
        var errorMessage = "お使いの端末は、GeoLacation APIに対応していません。\n手動で情報入力してください。";
        alert(errorMessage);
      }
    </script>

    <!-- 隠れボックス -->
    <form name="form1">
      <input type="hidden" value="" id="lat_data" />
      <input type="hidden" value="" id="lng_data" />
    </form>

</body>

</html>