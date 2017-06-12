<?php

function EncodeNIDX($in,$out) {
  $replacing = array(
  "\x0d"         => "",
  "\x0a"         => "",
  "\xef\xbb\xbf" => "",
  "\xc2\x81"     => "\x81",
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
    $offset = 8;
    $lines = 0;
    $output = "";
    while($buf = fgets($a)) {
      $hasmore = 0;
      $offset += 4;
      $pos[] = strlen($output);
      ++$lines;
      $buf = strtr($buf,$replacing);
      $hasmore = substr_count($buf,"<MORE->");
      if($hasmore > 0) {
        $subbuf = explode("<MORE->",$buf);
        $subs = count($subbuf);
      } else {
        $subs = 1;
      }
      for($x=0;$x<$subs;++$x) {
        if(isset($subbuf[$x])) {
          $buf = $subbuf[$x];
          unset($subbuf[$x]);
        }
        $nullpads = substr_count($buf,"<NULLPAD>");
        $buf = str_replace("<NULLPAD>","",$buf);
        $length = strlen($buf);
        if($length > 0) ++$length;
        $output .= IntToBinary($length);
        $output .= $buf."\x00";
        for($z=0;$z<$nullpads;++$z) {
          $output .= "\x00";
        }
      }
    }
    if($lines > 0) {
      fwrite($b,"NIDX");
      $liness = LongToBinary($lines);
      fwrite($b,$liness);
      for($x=0;$x<$lines;++$x) {
        $buf = LongToBinary(($pos[$x] + $offset));
        fwrite($b,$buf);
      }
      fwrite($b,$output);
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

function IntToBinary($num) {
 $buf = chr($num & 255);
 $buf .= chr(($num >> 8) & 255);
 return $buf;
}

function DecodeNIDX($in,$out) {
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
    $buf = fread($a,4);
    if($buf === "NIDX") {
      fseek($a,0,SEEK_END);
      $size = ftell($a);
      fseek($a,8,SEEK_SET);
      $b = fopen($out,"w");
      $buf = fread($a,4);
      $start = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
      $pos[] = $start;
      $filpos = 8;
      while($start > $filpos) {
        $buf = fread($a,4);
        $pos[] = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
        $filpos = ftell($a);
      }
      $y=count($pos);
      $pos[] = $size;
      for($x=0;$x<$y;++$x) {
        if($x>0) fwrite($b,PHP_EOL);
        fseek($a,$pos[$x],SEEK_SET);
        $buf = fread($a,2);
        $len = (ord($buf[1]) << 8) + ord($buf[0]) - 1;
        if($len>0) $buf = fread($a,$len); else $buf="";
        fwrite($b,$buf);
        fseek($a,1,SEEK_CUR);
        while(ftell($a)<$pos[$x+1]) {
          $buf = fread($a,1);
          $byte = ord($buf[0]);
          if($byte > 0) {
            $buf = fread($a,1);
            $len = (ord($buf[0]) << 8) + $byte - 1;
            if($len > 0) $buf=fread($a,$len); else $buf="";
            fwrite($b,"<MORE->");
            fwrite($b,$buf);
            fseek($a,1,SEEK_CUR);
          } else {
            fwrite($b,"<NULLPAD>");
          }
        }
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
    EncodeNIDX($argv[2],$argv[3]);
    break;
  case "d":
    DecodeNIDX($argv[2],$argv[3]);
    break;
  default:
    echo "Error!\n";
    exit;
}
?>