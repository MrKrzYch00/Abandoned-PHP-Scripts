<?php

  $gotit = array(0,0,0,0,0,0);
  $i = 0;
  $VIII = 0;
  $VII = 0;
  $VI = 0;
  $V = 0;
  $IV = 0;
  $III = 0;
  $II = 0;
  $I = 0;

  $a1 = mt_rand(1,35);
  do {
    $a2 = mt_rand(1,35);
  } while($a1 == $a2);
  do {
    $a3 = mt_rand(1,35);
  } while($a1 == $a3 || $a2 == $a3);
  do {
    $a4 = mt_rand(1,35);
  } while($a1 == $a4 || $a2 == $a4 || $a3 == $a4);
  do {
    $a5 = mt_rand(1,35);
  } while($a1 == $a5 || $a2 == $a5 || $a3 == $a5 || $a4 == $a5);
  $a6 = mt_rand(1,4);

  do {
    $k = 0;
    ++$i;
    $b1 = mt_rand(1,35);
    do {
      $b2 = mt_rand(1,35);
    } while($b1 == $b2);
    do {
      $b3 = mt_rand(1,35);
    } while($b1 == $b3 || $b2 == $b3);
    do {
      $b4 = mt_rand(1,35);
    } while($b1 == $b4 || $b2 == $b4 || $b3 == $b4);
    do {
      $b5 = mt_rand(1,35);
    } while($b1 == $b5 || $b2 == $b5 || $b3 == $b5 || $b4 == $b5);
    $b6 = mt_rand(1,4);

    if($a1 == $b1 || $a1 == $b2 || $a1 == $b3 || $a1 == $b4 || $a1 == $b5) $gotit[0] = 1;
    if($a2 == $b1 || $a2 == $b2 || $a2 == $b3 || $a2 == $b4 || $a2 == $b5) $gotit[1] = 1;
    if($a3 == $b1 || $a3 == $b2 || $a3 == $b3 || $a3 == $b4 || $a3 == $b5) $gotit[2] = 1;
    if($a4 == $b1 || $a4 == $b2 || $a4 == $b3 || $a4 == $b4 || $a4 == $b5) $gotit[3] = 1;
    if($a5 == $b1 || $a5 == $b2 || $a5 == $b3 || $a5 == $b4 || $a5 == $b5) $gotit[4] = 1;
    if($a6 == $b6) $gotit[5] = 1;
    for($j=0;$j<5;++$j) {
      if($gotit[$j] == 1) ++$k;
    }
    if(!$gotit[5]) {
      if($k==2) ++$VIII;
      elseif($k==3) ++$VI;
      elseif($k==4) ++$IV;
      elseif($k==5) ++$II;
    } else {
      if($k==2) ++$VII;
      elseif($k==3) ++$V;
      elseif($k==4) ++$III;
      elseif($k==5) break;
    }
    $gotit[0] = 0; $gotit[1] = 0; $gotit[2] = 0; $gotit[3] = 0; $gotit[4] = 0; $gotit[5] = 0;
    echo "{$i} --- 25 000zl: {$II}, 1 000zl: {$III}, 200zl: {$IV}, 80zl: {$V}, 25zl: {$VI}, 10zl: {$VII}, 5zl: {$VIII}\r";
  } while(1);

  echo "Got it in {$i} tries, 25 000zl: {$II}, 1 000zl: {$III}, 200zl: {$IV}, 80zl: {$V}, 25zl: {$VI}, 10zl: {$VII}, 5zl: {$VIII}\n";

?>