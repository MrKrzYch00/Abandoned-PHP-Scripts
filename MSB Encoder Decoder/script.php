<?php

function EncodeMSB($in,$out) {
  $replacing = array(
  "\x0d"         => "",
  "\x0a"         => "",
  "\xef\xbb\xbf" => "",
  "\xc4\x85"     => "\x82\x9f",
  "\xc4\x84"     => "\x82\xa0",
  "\xc4\x87"     => "\x82\xa1",
  "\xc4\x86"     => "\x82\xa2",
  "\xc4\x99"     => "\x82\xa3",
  "\xc4\x98"     => "\x82\xa4",
  "\xc5\x82"     => "\x82\xa5",
  "\xc5\x81"     => "\x82\xa6",
  "\xc5\x84"     => "\x82\xa7",
  "\xc5\x83"     => "\x82\xa8",
  "\xc3\xb3"     => "\x82\xa9",
  "\xc3\x93"     => "\x82\xaa",
  "\xc5\x9b"     => "\x82\xab",
  "\xc5\x9a"     => "\x82\xac",
  "\xc5\xba"     => "\x82\xad",
  "\xc5\xb9"     => "\x82\xae",
  "\xc5\xbc"     => "\x82\xaf",
  "\xc5\xbb"     => "\x82\xb0");
  $a = fopen($in,"r");
  if(is_resource($a)) {
    $b = fopen($out,"w");
    $start = 0;
    $offset = 0;
    $lines = 0;
    while($buf = fgets($a)) {
      ++$lines;
      $start += 8;
      $buf = strtr($buf,$replacing);
      $length = strlen($buf);
      $text[] = $buf;
      $len[] = $length;
      $pos[] = $offset;
      $offset += $length;
      if($length>0) echo $buf,"\n"; else echo "--BLANK--\n";
    }
    if($lines > 0) {
      $buf = LongToBinary($lines);
      fwrite($b,$buf);
      for($x=0;$x<$lines;++$x) {
        $buf = LongToBinary(($pos[$x] + $start));
        fwrite($b,$buf);
        $buf = LongToBinary($len[$x]);
        fwrite($b,$buf);
      }
      for($x=0;$x<$lines;++$x) {
        fwrite($b,$text[$x]);
      }
    }
  }
}

function LongToBinary($num) {
 $buf = chr($num & 255);
 $buf .= chr(($num >> 8) & 255);
 $buf .= chr(($num >> 16) & 255);
 $buf .= chr(($num >> 24) & 255);
 return $buf;
}

function DecodeMSB($in,$out) {
  $replacing = array(
      "\x82\x9f" => "\xc4\x85",
      "\x82\xa0" => "\xc4\x84",
      "\x82\xa1" => "\xc4\x87",
      "\x82\xa2" => "\xc4\x86",
      "\x82\xa3" => "\xc4\x99",
      "\x82\xa4" => "\xc4\x98",
      "\x82\xa5" => "\xc5\x82",
      "\x82\xa6" => "\xc5\x81",
      "\x82\xa7" => "\xc5\x84",
      "\x82\xa8" => "\xc5\x83",
      "\x82\xa9" => "\xc3\xb3",
      "\x82\xaa" => "\xc3\x93",
      "\x82\xab" => "\xc5\x9b",
      "\x82\xac" => "\xc5\x9a",
      "\x82\xad" => "\xc5\xba",
      "\x82\xae" => "\xc5\xb9",
      "\x82\xaf" => "\xc5\xbc",
      "\x82\xb0" => "\xc5\xbb");
  $a = fopen($in,"r");
  if(is_resource($a)) {
    $b = fopen($out,"w");
    $buf = fread($a,4);
    $amount = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
    if($amount > 0) {
      for($x=0;$x<$amount;++$x) {
        $buf = fread($a,4);
        $pos[] = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]) + 4;
        $buf = fread($a,4);
        $len[] = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
      }
    }
    fwrite($b,"\xef\xbb\xbf");
    for($x=0;$x<$amount;++$x) {
      if($x>0) fwrite($b,PHP_EOL);
      if($len[$x]>0) {
        fseek($a,$pos[$x],SEEK_SET);
        $text = strtr(fread($a,$len[$x]),$replacing);
        fwrite($b,$text);
        echo $text,"\n";
      } else {
        echo "--BLANK--";
      }
    }
  }
}

if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
  echo "php ",__FILE__," c/d [infile] [outfile]\n\n";
  exit;
}

switch($argv[1]) {
  case "c":
    EncodeMSB($argv[2],$argv[3]);
    break;
  case "d":
    DecodeMSB($argv[2],$argv[3]);
    break;
  default:
    echo "Error!\n";
    exit;
}
?>