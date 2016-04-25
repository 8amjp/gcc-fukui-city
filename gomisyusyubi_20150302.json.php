<?php
header("Content-Type: application/json; charset=utf-8");

$t = new DateTime();
$lines = file('./gomisyusyubi_20150302.txt', FILE_IGNORE_NEW_LINES);
$json = array();
foreach ($lines as $line) {
  if ( !preg_match('/^#/', $line) ) {
    $a = explode("\t", $line);
    $json[$a[0]] = array (
      'initial'  => $a[1], // 音
      'address'  => $a[2], // 町名
      'phonetic' => $a[3], // よみがな
      'district' => $a[4], // 地区
      'bottles'          => $a[5],
      'cans'             => $a[6],
      'cardboardboxes'   => $a[7],
      'petbottles'       => $a[8],
      'fluorescentbulbs' => $a[9].$a[10],
      'plastic'          => $a[11],
      'burnable'         => $a[12],
      'nonburnable'      => $a[13],
      'dates' => array (
        array ( 'id' => 'bottles',          'title' => '空きびん',                       'date' => parse($a[5]) ),
        array ( 'id' => 'cans',             'title' => '空き缶',                         'date' => parse($a[6]) ),
        array ( 'id' => 'cardboardboxes',   'title' => 'ダンボール・紙製容器・紙パック', 'date' => parse($a[7]) ),
        array ( 'id' => 'petbottles',       'title' => 'ペットボトル類',                 'date' => parse($a[8]) ),
        array ( 'id' => 'fluorescentbulbs', 'title' => '蛍光灯',                         'date' => parse($a[9].$a[10]) ),
        array ( 'id' => 'plastic',          'title' => 'プラスチック製容器包装',         'date' => parse($a[11]) ),
        array ( 'id' => 'burnable',         'title' => '燃やせるごみ',                   'date' => parse($a[12]) ),
        array ( 'id' => 'nonburnable',      'title' => '燃やせないごみ',                 'date' => parse($a[13]) )
      ),
      'note' => $a[14]
    );
  }
}

echo sprintf("gomisyusyubi_20150302(%s)",json_encode($json));
exit;

function parse($s) {
  global $t;
  $o = array('', 'first', 'second', 'third', 'fourth', 'fifth');
  switch(true) {
    case preg_match('/^[日月火水木金土]$/u', $s): //ex.金
      $d = array(
        "next ".getDayOfTheWeek($s)
      );
      break;
    case preg_match('/^([日月火水木金土])･([日月火水木金土])$/u', $s, $m): //ex.月･木
      $d = array(
        "next ".getDayOfTheWeek($m[1]),
        "next ".getDayOfTheWeek($m[2])
      );
      break;
    case preg_match('/^([\d])([日月火水木金土])$/u', $s, $m): //ex.2水
      $d = array(
        $o[$m[1]]." ".getDayOfTheWeek($m[2])." of this month",
        $o[$m[1]]." ".getDayOfTheWeek($m[2])." of next month"
      );
      break;
    case preg_match('/^([\d])･([\d])([日月火水木金土])$/u', $s, $m): //ex.1･3水
      $d = array(
        $o[$m[1]]." ".getDayOfTheWeek($m[3])." of this month",
        $o[$m[2]]." ".getDayOfTheWeek($m[3])." of this month",
        $o[$m[1]]." ".getDayOfTheWeek($m[3])." of next month",
        $o[$m[2]]." ".getDayOfTheWeek($m[3])." of next month"
      );
      break;
    case preg_match('/^([偶奇])([\d])([日月火水木金土])$/u', $s, $m): //ex.偶2水
      $oe = (($m[1] == "奇")? 1 : 0) == ((int)$t->format('m') % 2);
      if ($oe) {
        $date = new DateTime("first day +2 month");
        $d = array (
          $o[$m[2]]." ".getDayOfTheWeek($m[3])." of this month",
          $o[$m[2]]." ".getDayOfTheWeek($m[3])." of ".$date->format('Y-m')
        );
      } else {
        $date = new DateTime("first day +1 month");
        $d = array (
          $o[$m[2]]." ".getDayOfTheWeek($m[3])." of ".$date->format('Y-m')
        );
      }
      break;
    default:
      return $s;
  }
  $d = array_map( 'parseDate', $d );
  sort($d);
  foreach ($d as $date) {
    if ($date > $t) {
      return $date->format(DateTime::ATOM);
      break;
    }
  };
}

function parseDate($d) {
  return new DateTime($d);
}

function getDayOfTheWeek($s) {
  $w = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
  return $w[mb_strpos("日月火水木金土", $s, 0, "utf-8")];
}
?>
