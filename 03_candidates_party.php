<?php
$lines = array();
foreach(glob('raw/*.csv') AS $file) {
  $p = pathinfo($file);
  $fh = fopen($file, 'r');
  while($line = fgetcsv($fh, 2048)) {
    foreach($line AS $k => $v) {
      $line[$k] = preg_replace("/[\\n\\r]/", '', $v);
    }
    if($line[3] === '推薦之政黨' || empty($line[3])) {
      continue;
    }
    $line[] = str_replace(array('選舉候選人登記彙總表', '107年'), '', $p['filename']);
    if(!isset($lines[$line[3]])) {
      $lines[$line[3]] = array();
    }
    $lines[$line[3]][] = $line;
  }
}
$counts = array();
foreach($lines AS $k => $v) {
  $count = count($v);
  if(!isset($counts[$count])) {
    $counts[$count] = array();
  }
  $counts[$count][] = $k;
}

ksort($counts);

$oFh = fopen(__DIR__ . '/03_candidates_party.csv', 'w');
fputcsv($oFh, array('政黨', '選舉類型', '選舉區', '姓名'));
foreach($counts AS $k => $parties) {
  foreach($parties AS $party) {
    echo "{$party}: " . count($lines[$party]) . "\n";
    foreach($lines[$party] AS $c) {
      fputcsv($oFh, array($party, $c[5], $c[0], $c[2]));
    }
  }
}
