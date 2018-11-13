<?php
$jsonPath = __DIR__ . '/bulletin/json';
$pdfPath = __DIR__ . '/bulletin/pdf';

if(!file_exists($jsonPath)) {
  mkdir($jsonPath, 0777, true);
  mkdir($pdfPath, 0777);
}

$listCities = array(
  '臺北市' => 'https://www.cec.gov.tw/mect/',
  '新北市' => 'https://www.cec.gov.tw/tpcec/',
  '桃園市' => 'https://www.cec.gov.tw/tyec/',
  '臺中市' => 'https://www.cec.gov.tw/tcec/',
  '臺南市' => 'https://www.cec.gov.tw/tnec/',
  '高雄市' => 'https://www.cec.gov.tw/khec/',
  '宜蘭縣' => 'https://www.cec.gov.tw/ilec/',
  '新竹縣' => 'https://www.cec.gov.tw/hccec/',
  '苗栗縣' => 'https://www.cec.gov.tw/mlec/',
  '南投縣' => 'https://www.cec.gov.tw/ntec/',
  '彰化縣' => 'https://www.cec.gov.tw/chec/',
  '雲林縣' => 'https://www.cec.gov.tw/ylec/',
  '嘉義縣' => 'https://www.cec.gov.tw/cycec/',
  '屏東縣' => 'https://www.cec.gov.tw/ptec/',
  '臺東縣' => 'https://www.cec.gov.tw/ttec/',
  '花蓮縣' => 'https://www.cec.gov.tw/hlec/',
  '澎湖縣' => 'https://www.cec.gov.tw/phec/',
  '基隆市' => 'https://www.cec.gov.tw/klec/',
  '新竹市' => 'https://www.cec.gov.tw/hcec/',
  '嘉義市' => 'https://www.cec.gov.tw/cyec/',
  '金門縣' => 'https://www.cec.gov.tw/kmec/',
  '連江縣' => 'https://www.cec.gov.tw/lcec',
);

foreach($listCities AS $listCity => $listUrl) {
  $cityJsonPath = $jsonPath . '/' . $listCity;
  if(!file_exists($cityJsonPath)) {
    mkdir($cityJsonPath, 0777);
  }
  $cityPath = $pdfPath . '/' . $listCity;
  if(!file_exists($cityPath)) {
    mkdir($cityPath, 0777);
  }
  $menuJson = $cityJsonPath . '/menu.json';
  if(!file_exists($menuJson)) {
    $page = file_get_contents($listUrl);
    $pos = strpos($page, 'var menuJson = ');
    $pos = strpos($page, '[{', $pos);
    $posEnd = strpos($page, ';', $pos);
    file_put_contents($menuJson, substr($page, $pos, $posEnd - $pos));
  }
  $menu = json_decode(file_get_contents($menuJson));
  $menuListItems = array();
  foreach($menu AS $lv1) {
    if($lv1->displayname === '107年選舉') {
      foreach($lv1->menu AS $lv2) {
        if(trim($lv2->displayname) === '選舉資訊') {
          foreach($lv2->menu AS $lv3) {
            //echo "{$listCity}/{$lv3->displayname}\n";
            if(trim($lv3->displayname) === '選舉公報') {
              if(!empty($lv3->menu)) {
                foreach($lv3->menu AS $menuItem) {
                  if(substr($menuItem->url, 0, 4) !== 'http') {
                    $menuListItems[] = 'https://www.cec.gov.tw' . $menuItem->url;
                  }
                }
              } elseif(substr($lv3->url, 0, 4) !== 'http') {
                $menuListItems[] = 'https://www.cec.gov.tw' . $lv3->url;
              }
            }
          }
        }
      }
    }
  }
  if(!empty($menuListItems)) {
    foreach($menuListItems AS $menuListItem) {
      $menuListItem = str_replace('/cms/', '/cmsList/', $menuListItem);
      $p = pathinfo($menuListItem);
      $menuUrl = $p['dirname'] . '/' . urlencode($p['filename']);
      $menuListJson = $cityJsonPath . '/menu-' . $p['filename'] . '.json';
      if(!file_exists($menuListJson)) {
        file_put_contents($menuListJson, file_get_contents($menuUrl));
      }
      $json = json_decode(file_get_contents($menuListJson));
      foreach($json AS $node) {
        $nodeFile = $cityJsonPath . '/node-' . $node->contentId . '.json';
        if(!file_exists($nodeFile)) {
          file_put_contents($nodeFile, file_get_contents(str_replace('/cmsList/', '/cmsData/', $menuUrl) . '/' . $node->contentId));
        }
        $nodeJson = json_decode(file_get_contents($nodeFile));
        foreach($nodeJson->files AS $f) {
          $nodeFile = $cityPath . '/' . $f->fullFileName;
          if(!file_exists($nodeFile)) {
            file_put_contents($nodeFile, file_get_contents('https://www.cec.gov.tw' . $f->fullFileUrl));
          }
        }
      }
    }
  }
}
