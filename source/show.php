<?php
//読み込む
require_once 'config/config.php';

//open-meteo API (ウェザーコード⇔絵文字コード) 変換関数
function toEmojiCode($num)
{
  $weatherCode = $num;
  if ($weatherCode == 0) return '&#x1f31e;';  // 0 : 快晴
  if ($weatherCode == 1) return '&#x2600;';  // 1 : 晴れ
  if ($weatherCode == 2) return '&#x26c5;';  // 2 : 時々曇り
  if ($weatherCode == 3) return  '&#x2601;';  // 3 : 曇り
  if ($weatherCode <= 49) return '&#x1f32b;';  // 45, 48 : 霧
  if ($weatherCode <= 58) return '&#x1f327;';  // 51, 53, 55, 56, 57：霧雨
  if ($weatherCode <= 68) return '&#x2614;';  // 61, 63, 65, 66, 67 :雨
  if ($weatherCode <= 78) return '&#x2744;';  // 71, 73, 75, 77 : 雪
  if ($weatherCode <= 83) return '&#x2602;';  // 80, 81, 82 : 俄雨
  if ($weatherCode <= 88) return '&#x2603;';  // 85, 86 : 雹
  if ($weatherCode <= 99) return '&#x26c8;';  // 95 , 96, 99 : 雷雨
}

//日付
$week = [
  '日', //0
  '月', //1
  '火', //2
  '水', //3
  '木', //4
  '金', //5
  '土', //6
];

//POSTで送信された変数はprefectureにしてもmunicipalitiesにしても、
//都道府県or市町村名:$address 都道府県or市町村名のテーブル名:$table_name 
if ($_POST['prefecture'] == "") {
  $address = $_POST['municipalities'];
  $table_name = 'municipalities';
  $log_table = 'municipalities_log';
} else {
  //選択された都道府県が格納される
  $address = $_POST['prefecture'];
  $table_name = 'prefecture';
  $log_table = 'prefecture_log';
}

//データベースに接続する
try {
  $db = new PDO('mysql:host=' . HOSTNAME . ';dbname=' . DATABASE, USERNAME, PASSWORD);
  $msg = "接続に成功しました。";
} catch (PDOException $e) {
  $isConnect = false;
  echo $e;
  $msg = "MySQL への接続に失敗しました。<br>(" . $e->getMessage() . ")";
}

//経度緯度情報取得
//sql文を作る
$sql_address = "SELECT * FROM " . $table_name;
$stm = $db->prepare($sql_address);
$stm->execute();
//結果の取得(連想配列で受け取る)
$address_data = $stm->fetchAll(PDO::FETCH_ASSOC);
//取得した結果（テーブル）の中を捜査していく、該当県に当たれば各情報を代入する
foreach ($address_data as $row) {
  if ($row['name'] === $address) {
    $address_latitude = $row['latitude'];
    $address_longitude = $row['longitude'];
  }
}

//履歴機能
//データを$log_tableに挿入するsql文
$sql_insert = "INSERT INTO `{$log_table}` (name) VALUES( '{$address}')";
$stm = $db->prepare($sql_insert);
$stm->execute();

//apiを使うための、urlパラメータ情報を用意する
$url_latitude = 'latitude=' . $address_latitude . '&';
$url_longitude = 'longitude=' . $address_longitude . '&';

//open-meteo API のurlを決定する
$url = 'https://api.open-meteo.com/v1/forecast?' . $url_latitude . $url_longitude . 'hourly=temperature_2m,weathercode&timezone=Asia%2FTokyo';

//urlからjsonデータを取得する
$json = file_get_contents($url);

//文字化けしないようにする
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

//連想配列に格納する
$address_array = json_decode($json, true);

//以下の情報を<body>内で利用する
//天気コード情報　　$address_array["hourly"]["weathercode"][0～167];　　24時間×7日=168  各時間ごとの天気コード
//気温情報      $address_array["hourly"]["temperature_2m"][0～167];    24時間×7日=168  各時間ごとの気温℃
//日にち情報    $address_array["hourly"]["time"][0～167];              24個ずつのデータは同じ日、それ×7

?>

<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>天気予報</title>
  <link href="./style.css" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700|Noto+Sans+JP:400,700&display=swap" rel="stylesheet">
</head>
<style>
  /* 絵文字の色をうまく表示するためフォントを指定 */
  html {
    font-family:
      "Segoe UI Emoji",
      "Segoe UI Symbol",
      "Apple Color Emoji",
      "Noto Color Emoji",
      "Noto Emoji",
      sans-serif;
  }
</style>

<body>

  <?php
  // 日付取得→表示

  $now_month = abs(date('m'));
  $now_date = abs(date('d'));
  $now_hour = abs(date('H'));
  $day = date('w');
  $today_date = $now_month . '/' . $now_date . '（' . $week[$day] . '）';
  $now_emoji = toEmojiCode($address_array['hourly']['weathercode'][$now_hour]);
  $now_temprerature = $address_array['hourly']['temperature_2m'][$now_hour] . '℃';

  echo "<div class='top_content'>";
  echo "<div class ='info'>";
  echo "<p> " . $today_date . " </p>";
  echo "<p> " . $address . " ... " . date('H:i') . " </p>";
  echo "<p> " . "今 " . $now_temprerature . " " . $now_emoji . " </p>";
  echo "</div>";

  echo "<div class='help_table'>";
  echo "<p class = 'table_name'>天気の照合表</p>";
  //照合テーブルを作る
  echo "<table class='design03'>";

  //照合テーブルのヘッダー
  echo "<thead><tr>";

  echo "<th>", '&#x1f31e;', "</th>";
  echo "<th>", '&#x2600;', "</th>";
  echo "<th>", '&#x26c5;', "</th>";
  echo "<th>", '&#x2601;', "</th>";
  echo "<th>", '&#x1f32b;', "</th>";
  echo "<th>", '&#x1f327;', "</th>";
  echo "<th>", '&#x2614;', "</th>";
  echo "<th>", '&#x2744;', "</th>";
  echo "<th>", '&#x2602;', "</th>";
  echo "<th>", '&#x2603;', "</th>";
  echo "<th>", '&#x26c8;', "</th>";

  echo "</tr></thead>";

  //照合テーブルのボディ
  echo "<tbody>";
  echo "<tr>";

  echo "<td>", "快晴", "</td>";
  echo "<td>", "晴れ", "</td>";
  echo "<td>", "時々曇り", "</td>";
  echo "<td>", "曇り", "</td>";
  echo "<td>", " 霧 ", "</td>";
  echo "<td>", "霧雨", "</td>";
  echo "<td>", " 雨 ", "</td>";
  echo "<td>", " 雪 ", "</td>";
  echo "<td>", "俄雨", "</td>";
  echo "<td>", "雪/雹", "</td>";
  echo "<td>", "雷雨", "</td>";

  echo "</tr>";
  echo "</tbody>";
  echo "</table>";
  echo "</div>";
  echo "</div>";


  echo "<div class='bottom_content'>";
  echo "<p class = 'table_name'>" . $address . "の天気予報1週間分</p>";
  //天気情報テーブルを作る
  echo "<table class='design04'>";

  //天気情報テーブルのヘッダー
  echo "<thead><tr>";

  echo "<th>", "日付", "</th>"; //日付
  for ($i = 0; $i < 24; $i++) { //〇〇時
    echo "<th>", $i . '時', "</th>";
  }
  echo "<th>", '最高気温℃', "</th>"; //最高気温
  echo "<th>", '最低気温℃', "</th>"; //最低気温

  echo "</tr></thead>";

  //天気情報テーブルのボディ
  echo "<tbody>";

  //天気情報テーブルのボディの中身(合計7行)
  for ($i = 0; $i < 168; $i += 24) {

    //天気情報テーブルのボディの各行の値 <tr></tr>
    echo "<tr>";

    //日付変換
    $ref = strtotime($address_array['hourly']['time'][$i]);
    $month = abs(date("m", $ref));
    $date = abs(date("d", $ref));
    $day = date('w', $ref);
    echo "<td>", $month . '/' . $date . '（' . $week[$day] . '）', "</td>";

    //各時間帯の絵文字表示（7日間）
    for ($j = $i; $j < ($i + 24); $j++) {
      $emoji = toEmojiCode($address_array['hourly']['weathercode'][$j]);
      echo "<td>", $emoji, "</td>";
    }

    //各日のそれぞれの時間帯の気温を格納する配列を用意する
    $temperature_array = array();
    for ($k = $i; $k < ($i + 24); $k++) {
      $temperature = $address_array['hourly']['temperature_2m'][$k];
      array_push($temperature_array, $temperature);
    }

    //最高気温表示
    echo "<td>", max($temperature_array), "</td>";
    //最低気温表示
    echo "<td>", min($temperature_array), "</td>";
    echo "</tr>";
  }
  echo "</tbody>";
  echo "</table>";
  echo "</div>";
  ?>

</body>

</html>