<?php
$base = array();
$files = array(
  '/home/kiang/public_html/sunshine.cy.gov.tw/list.csv',
  '/home/kiang/public_html/sunshine.cy.gov.tw/list_20140424.csv',
  '/home/kiang/public_html/sunshine.cy.gov.tw/list_20141201.csv',
);
foreach($files AS $file) {
  $fh = fopen($file, 'r');
  fgetcsv($fh, 2048);
  while($line = fgetcsv($fh, 2048)) {
    if(!isset($line[1]) || false === strpos($line[1], '議員')) {
      continue;
    }
    $reportFile = '/home/kiang/public_html/sunshine.cy.gov.tw/report2txt/' . $line[1] . '.csv';
    if(file_exists($reportFile)) {
      $fh2 = fopen($reportFile, 'r');
      while($line2 = fgetcsv($fh2, 2048)) {
        $line2[2] = intval(str_replace(',', '', $line2[2]));
        if($line2[1] === '營利事業捐贈收入' && $line2[2] != 0) {
          if(!isset($base[$line[0]])) {
            $base[$line[0]] = array();
          }
          $line[] = $line2[2];
          $line[] = 'https://github.com/kiang/sunshine.cy.gov.tw/tree/master/report2txt/' . $line[1] . '.csv';
          $base[$line[0]][] = $line;
        }
      }
    }
  }
}

$files = array(
  'raw/107年直轄市議員選舉候選人登記彙總表.csv',
  'raw/107年縣市議員選舉候選人登記彙總表.csv',
);
$dup = array();
$lines = array();
foreach($files AS $file) {
  $fh = fopen($file, 'r');
  while($line = fgetcsv($fh, 2048)) {
    $line[2] = trim($line[2]);
    if($line[2] === '姓名' || $line[2] === '' || isset($dup[$line[2]])) {
      continue;
    }
    $dup[$line[2]] = true;
    if(isset($base[$line[2]])) {
      foreach($base[$line[2]] AS $account) {
        $parts = explode('/', $account[5]);
        $parts[0] += 1911;
        $account[5] = implode('-', $parts);
        if(!isset($lines[$account[7]])) {
          $lines[$account[7]] = array();
        }
        $lines[$account[7]][] = array_merge(array($line[0]), $account);
      }
    }
  }
}
krsort($lines);
$oFh = fopen(__DIR__ . '/02_council_check.csv', 'w');
fputcsv($oFh, array('2018選區', '姓名', '歷史政治獻金帳戶', '分行', '帳號', '分行住址', '開設日期', '核准文號', '營利事業收入金額', '報告書網址'));
foreach($lines AS $accounts) {
  foreach($accounts AS $account) {
    fputcsv($oFh, $account);
  }
}
