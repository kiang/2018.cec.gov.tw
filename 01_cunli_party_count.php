<?php
$fh = fopen('raw/107年村里長選舉候選人登記彙總表.csv', 'r');
$result = array();
while($line = fgetcsv($fh, 2048)) {
  $city = mb_substr($line[0], 0, 3, 'utf-8');
  if(!isset($result[$city])) {
    $result[$city] = array();
  }
  if(!isset($result[$city][$line[3]])) {
    $result[$city][$line[3]] = 0;
  }
  ++$result[$city][$line[3]];
}

print_r($result);
