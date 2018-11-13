<?php
$rawPath = __DIR__ . '/05_raw';
if(!file_exists($rawPath)) {
  mkdir($rawPath, 0777);
}
$cities = array(
  '63000' => '臺北市',
'65000' => '新北市',
'68000' => '桃園市',
'66000' => '臺中市',
'67000' => '臺南市',
'64000' => '高雄市',
'10004' => '新竹縣',
'10005' => '苗栗縣',
'10007' => '彰化縣',
'10008' => '南投縣',
'10009' => '雲林縣',
'10010' => '嘉義縣',
'10013' => '屏東縣',
'10002' => '宜蘭縣',
'10015' => '花蓮縣',
'10014' => '臺東縣',
'10016' => '澎湖縣',
'09020' => '金門縣',
'09007' => '連江縣',
'10017' => '基隆市',
'10018' => '新竹市',
'10020' => '嘉義市',
);

foreach($cities AS $cityId => $city) {
  $jsonFile = $rawPath . '/' . $cityId . '.json';
  if(!file_exists($jsonFile)) {
    file_put_contents($jsonFile, file_get_contents('http://2018.cec.gov.tw/json/candidate/' . $cityId . '.json'));
  }
  $raw = file_get_contents($jsonFile);
  $json = json_decode(substr($raw, strpos($raw, '[')), true);
  foreach($json AS $p) {
    if(substr($p['src'], 0, 4) === 'data') {
      $pic = $rawPath . '/' . $cityId . '/' . implode('_', array($p['DistrictId'], $p['voterTypeId'], $p['DrawNo'])) . '.jpg';
      $info = pathinfo($pic);
      if(!file_exists($info['dirname'])) {
        mkdir($info['dirname'], 0777, true);
      }
      if(!file_exists($pic) || filesize($pic) === 0) {
        if(file_exists($pic)) {
          unlink($pic);
        }
        $imgStr = substr($p['src'], strpos($p['src'], ',') + 1);
        if(!empty($imgStr)) {
          $fp = fopen($pic, 'wb');
          fwrite($fp, base64_decode($imgStr));
          fclose($fp);
        }
      }
    } else {
      $picUrl = 'http://2018.cec.gov.tw/json/candidatepic/' . str_replace('\\', '/', $p['src']);
      $pic = $rawPath . '/' . str_replace('\\', '/', $p['src']);
      $info = pathinfo($pic);
      if(!file_exists($info['dirname'])) {
        mkdir($info['dirname'], 0777, true);
      }
      if(!file_exists($pic) || filesize($pic) === 0) {
        file_put_contents($pic, file_get_contents($picUrl));
      }
    }
  }
}
