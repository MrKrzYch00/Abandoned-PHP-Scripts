<?php

  $gotit = array(0,0,0,0,0,0);
  $i = 0;
  $trojki = 0;
  $czworki = 0;
  $piatki = 0;

  do {
    $k = 0;
    ++$i;

  $a1 = mt_rand(1,49);
  do {
    $a2 = mt_rand(1,49);
  } while($a1 == $a2);
  do {
    $a3 = mt_rand(1,49);
  } while($a1 == $a3 || $a2 == $a3);
  do {
    $a4 = mt_rand(1,49);
  } while($a1 == $a4 || $a2 == $a4 || $a3 == $a4);
  do {
    $a5 = mt_rand(1,49);
  } while($a1 == $a5 || $a2 == $a5 || $a3 == $a5 || $a4 == $a5);
  do {
    $a6 = mt_rand(1,49);
  } while($a1 == $a6 || $a2 == $a6 || $a3 == $a6 || $a4 == $a6 || $a5 == $a6);

    $b1 = mt_rand(1,49);
    do {
      $b2 = mt_rand(1,49);
    } while($b1 == $b2);
    do {
      $b3 = mt_rand(1,49);
    } while($b1 == $b3 || $b2 == $b3);
    do {
      $b4 = mt_rand(1,49);
    } while($b1 == $b4 || $b2 == $b4 || $b3 == $b4);
    do {
      $b5 = mt_rand(1,49);
    } while($b1 == $b5 || $b2 == $b5 || $b3 == $b5 || $b4 == $b5);
    do {
      $b6 = mt_rand(1,49);
    } while($b1 == $b6 || $b2 == $b6 || $b3 == $b6 || $b4 == $b6 || $b5 == $b6);

    if($a1 == $b1 || $a1 == $b2 || $a1 == $b3 || $a1 == $b4 || $a1 == $b5 || $a1 == $b6) $gotit[0] = 1;
    if($a2 == $b1 || $a2 == $b2 || $a2 == $b3 || $a2 == $b4 || $a2 == $b5 || $a2 == $b6) $gotit[1] = 1;
    if($a3 == $b1 || $a3 == $b2 || $a3 == $b3 || $a3 == $b4 || $a3 == $b5 || $a3 == $b6) $gotit[2] = 1;
    if($a4 == $b1 || $a4 == $b2 || $a4 == $b3 || $a4 == $b4 || $a4 == $b5 || $a4 == $b6) $gotit[3] = 1;
    if($a5 == $b1 || $a5 == $b2 || $a5 == $b3 || $a5 == $b4 || $a5 == $b5 || $a5 == $b6) $gotit[4] = 1;
    if($a6 == $b1 || $a6 == $b2 || $a6 == $b3 || $a6 == $b4 || $a6 == $b5 || $a6 == $b6) $gotit[5] = 1;
    for($j=0;$j<6;++$j) {
      if($gotit[$j] == 1) ++$k;
    }
    if($k == 3) ++$trojki;
    elseif($k == 4) ++$czworki;
    elseif($k == 5) ++$piatki;
    elseif($k == 6) break;
    $gotit[0] = 0; $gotit[1] = 0; $gotit[2] = 0; $gotit[3] = 0; $gotit[4] = 0; $gotit[5] = 0;
    echo "{$i} --- 3-ki: {$trojki}, 4-ki: {$czworki}, 5-ki: {$piatki}\r";
  } while(1);

  echo "Got it in {$i} tries, trojki: {$trojki}, czworki: {$czworki}, piatki: {$piatki}\n";

?>