<?php
$mapFiles = array(
  '直轄市長' => 'http://2018.cec.gov.tw/json/DistrictId/20182010001.json',
  '直轄市議員' => 'http://2018.cec.gov.tw/json/DistrictId/20182020001.json',
  '直轄市山地原住民區長' => 'http://2018.cec.gov.tw/json/DistrictId/20184030001.json',
  '直轄市山地原住民區代表' => 'http://2018.cec.gov.tw/json/DistrictId/20184040001.json',
  '縣(市)長' => 'http://2018.cec.gov.tw/json/DistrictId/20183010001.json',
  '縣(市)議員' => 'http://2018.cec.gov.tw/json/DistrictId/20183020001.json',
  '鄉鎮市長' => 'http://2018.cec.gov.tw/json/DistrictId/20184010001.json',
  '鄉鎮市民代表' => 'http://2018.cec.gov.tw/json/DistrictId/20184020001.json',
  '村里長' => 'http://2018.cec.gov.tw/json/DistrictId/20185010001.json',
);
$mapTitles = array(
  '直轄市長' => array('CountyCityName'),
  '直轄市議員' => array('CountyCityName', 'DistrictName'),
  '直轄市山地原住民區長' => array('CountyCityName', 'ScopeTownshipName'),
  '直轄市山地原住民區代表' => array('CountyCityName', 'ScopeTownshipName', 'DistrictName'),
  '縣(市)長' => array('CountyCityName'),
  '縣(市)議員' => array('CountyCityName', 'DistrictName'),
  '鄉鎮市長' => array('CountyCityName', 'ScopeTownshipName'),
  '鄉鎮市民代表' => array('CountyCityName', 'ScopeTownshipName', 'DistrictName'),
  '村里長' => array('CountyCityName', 'ScopeTownshipName', 'ScopeVillageName'),
);
$mapPath = dirname(__DIR__) . '/areas_map';
$ref = array();
foreach($mapFiles AS $mapType => $mapFile) {
  $info = pathinfo($mapFile);
  $localFile = $mapPath . '/' . $info['basename'];
  if(!file_exists($localFile)) {
    file_put_contents($localFile, file_get_contents($mapFile));
  }
  $localText = file_get_contents($localFile);
  $json = json_decode($localText, true);
  foreach($json['data'] AS $item) {
    if(!isset($ref[$item['DistrictId']])) {
      $ref[$item['DistrictId']] = '[' . $mapType . ']';
      foreach($mapTitles[$mapType] AS $itemKey) {
        $ref[$item['DistrictId']] .= $item[$itemKey];
      }
    }
  }
}
$counter = array();
foreach(glob(dirname(__DIR__) . '/05_raw/*.json') AS $jsonFile) {
  $jsonString = file_get_contents($jsonFile);
  $json = json_decode(substr($jsonString, strpos($jsonString, '[')), true);
  foreach($json AS $p) {
    $key = $ref[$p['DistrictId']];
    if(false === strpos($key, '村里長')) {
      if(!isset($counter[$key])) {
        $counter[$key] = 0;
      }
      ++$counter[$key];
    }
  }
}

$fh = fopen(__DIR__ . '/02_count.csv', 'w');
fputcsv($fh, array('選舉類型', '候選人數量'));
foreach($counter AS $k => $v) {
  fputcsv($fh, array($k, $v));
}
