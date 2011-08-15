#!/usr/bin/php
<?
$wkt="LINESTRING(1824677.60370635 6142336.08400489,1824701.17004255 6142329.78604467,1824743.34899762 6142317.30707617,1824769.77624473 6142309.1214173,1824787.76547444 6142302.28890395,1824807.02374635 6142292.54965842,1824821.61773159 6142283.74592458,1824841.54392045 6142270.5988095,1824856.82808653 6142258.05310607,1824875.61881658 6142239.24293807,1824890.86958681 6142220.71680061,1824903.80491164 6142201.42226366,1824919.14473748 6142177.16633611,1824933.68306297 6142153.32810517,1824951.02663964 6142123.52623365,1824970.71905756 6142084.46990228,1824986.42623771 6142060.91591353,1825000.60834084 6142034.40522504,1825032.06722894 6141972.6809095,1825056.1456348 6141923.25166374,1825077.98651889 6141865.50383267,1825112.22839426 6141784.18646018,1825152.41473043 6141664.55043279,1825176.20370562 6141585.27285882,1825190.330149 6141521.29675977,1825200.66059774 6141410.85141916,1825210.51237268 6141347.0102528,1825226.09710139 6141249.51224806,1825232.89872228 6141187.27579539,1825235.6928415 6141175.43321517)";

$x_min="1824650";
$y_min="6141150";
$zoom=50; // per char

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
  global $x_min, $y_min, $zoom;
  $ret=array();

  foreach($points as $p) {
    $x=($p[0]-$x_min)/$zoom;
    $y=($p[1]-$y_min)/$zoom;
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
	print "+";
      }
    }

    print "\n";
  }
}

$geo=parse_wkt($wkt);
$geo=zoom_geo($geo);
$matrix=array();
print_geo(&$matrix, $geo);

print_matrix($matrix);
