#!/usr/bin/php
<?
$objects=array(
  "LINESTRING(1824677.60370635 6142336.08400489,1824701.17004255 6142329.78604467,1824743.34899762 6142317.30707617,1824769.77624473 6142309.1214173,1824787.76547444 6142302.28890395,1824807.02374635 6142292.54965842,1824821.61773159 6142283.74592458,1824841.54392045 6142270.5988095,1824856.82808653 6142258.05310607,1824875.61881658 6142239.24293807,1824890.86958681 6142220.71680061,1824903.80491164 6142201.42226366,1824919.14473748 6142177.16633611,1824933.68306297 6142153.32810517,1824951.02663964 6142123.52623365,1824970.71905756 6142084.46990228,1824986.42623771 6142060.91591353,1825000.60834084 6142034.40522504,1825032.06722894 6141972.6809095,1825056.1456348 6141923.25166374,1825077.98651889 6141865.50383267,1825112.22839426 6141784.18646018,1825152.41473043 6141664.55043279,1825176.20370562 6141585.27285882,1825190.330149 6141521.29675977,1825200.66059774 6141410.85141916,1825210.51237268 6141347.0102528,1825226.09710139 6141249.51224806,1825232.89872228 6141187.27579539,1825235.6928415 6141175.43321517)",
//  "LINESTRING(1825000.60834084 6142034.40522504,1824916.75136842 6141974.76900491,1824906.96638518 6141968.45460595,1824800.03288233 6141894.30252874,1824692.52051812 6141804.51573083)",
//  "LINESTRING(1825141.12693407 6141152.282605,1825137.80961324 6141161.70319206,1825084.61002859 6141370.02776126,1825059.39616393 6141482.20977022,1825042.26409429 6141550.76245457,1825024.33052433 6141626.28128881,1824966.34420157 6141805.36765695,1824916.75136842 6141974.76900491,1824812.05538733 6142127.40180749,1824773.65016301 6142184.95094448)",
);

$x_min="1824650";
$y_min="6141550";
$zoom_x=25; // per char
$zoom_y=50;

function parse_wkt($wkt) {
  if(preg_match("/^LINESTRING\((.*)\)$/", $wkt, $m)) {
    $points=explode(",", $m[1]);
    foreach($points as $i=>$p) {
      $x=explode(" ", $p);
      $points[$i]=array((float)$x[0], (float)$x[1]);
    }
  }

  return $points;
}

function zoom_geo($points) {
  global $x_min, $y_min, $zoom_x, $zoom_y;
  $ret=array();

  foreach($points as $p) {
    $x=($p[0]-$x_min)/$zoom_x;
    $y=($p[1]-$y_min)/$zoom_y;
    $ret[]=array($x, $y);
  }

  return $ret;
}

function print_geo($matrix, $points) {
  foreach($points as $poi) {
    $x=round($poi[0]);
    $y=round($poi[1]);

    $matrix[$y][$x]="*";
  }
}

function line_from_to($matrix, $poi1, $poi2) {
  $x_diff=$poi2[0]-$poi1[0];
  $y_diff=$poi2[1]-$poi1[1];

  if(abs($y_diff)>=abs($x_diff)) {
    $x_inc=$x_diff/abs($y_diff);
    if($poi1[1]<$poi2[1]) {
      $y_row=array($poi1[1], $poi2[1]);
      $x=$poi1[0];
    }
    else {
      $y_row=array($poi2[1], $poi1[1]);
      $x=$poi2[0];
      $x_inc=-$x_inc;
    }
    for($y=$y_row[0]; $y<=$y_row[1]; $y++) {
      $matrix[round($y)][round($x)]="+";
      $x+=$x_inc;
    }
  }
  else {
    $y_inc=$y_diff/abs($x_diff);
    if($poi1[0]<$poi2[0]) {
      $x_row=array($poi1[0], $poi2[0]);
      $y=$poi1[1];
    }
    else {
      $x_row=array($poi2[0], $poi1[0]);
      $y_inc=-$y_inc;
      $y=$poi2[1];
    }
    $y=$poi1[1];
    for($x=$x_row[0]; $x<=$x_row[1]; $x++) {
      $matrix[round($y)][round($x)]="+";
      $y+=$y_inc;
    }
  }
}

function draw_line($matrix, $geo) {
  for($i=0; $i<sizeof($geo)-1; $i++) {
    line_from_to(&$matrix, $geo[$i], $geo[$i+1]);
  }
}

function print_matrix($matrix) {
  $matrix_y=array_keys($matrix);
  $y1=min($matrix_y);
  $y2=max($matrix_y);
  if($y1<0) $y1=0;
  if($y2>20) $y2=20;

  for($y=$y1; $y<=$y2; $y++) {
    if(!isset($matrix[$y])) {
      print "\n";
      continue;
    }

    $matrix_x=array_keys($matrix[$y]);
    sort($matrix_x);

    $x1=min($matrix_x);
    $x2=max($matrix_x);
    if($x1<0) $x1=0;
    if($x2>79) $x2=79;
    print str_repeat(" ", $x1);
    for($x=$x1; $x<=$x2; $x++) {
      if(isset($matrix[$y])&&isset($matrix[$y][$x])) {
	print "{$matrix[$y][$x]}";
      }
      else {
	print " ";
      }
    }

    print "\n";
  }
}

$matrix=array();
foreach($objects as $wkt) {
  $geo=parse_wkt($wkt);
  $geo=zoom_geo($geo);
  draw_line(&$matrix, $geo);
//  print_geo(&$matrix, $geo);
}

print_matrix($matrix);
