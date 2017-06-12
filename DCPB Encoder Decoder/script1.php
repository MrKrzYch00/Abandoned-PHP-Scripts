<?php

function EncodeDCPB($in,$out,$replacerstart = 1) {
  $replacing = array(
  "\x0d"         => "",
  "\x0a"         => "",
  "\xef\xbb\xbf" => "",
  "\xef\xbf\xbd\xef\xbf\xbd\xef\xbf\xbd\xd4\x96\xef\xbf\xbd\xef\xbf\xbd" => "\x92\x87\x8a\xd4\x96\xbc\x82",
  "\xef\xbf\xbd\xef\xbf\xbd\x6c\xef\xbf\xbd\xef\xbf\xbd\xef\xbf\xbd\xef\xbf\xbd" => "\x8e\xe5\x90\x6c\x8c\xf6\x96\xbc",
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
  $replacing2 = array('\n' => ' ', '\p' => ' ');
  $maxlen = 230;
  $dividors = array('\p','\n');
  $charlen = array("!" => 2, "'" => 2, "." => 2, ":" => 2,
                   " " => 3, "," => 3, ";" => 3, "`" => 3, '|' => 3,
                   '"' => 4, "(" => 4, ")" => 4, "I" => 4, "[" => 4, "]" => 4, "i" => 4, "l" => 4, "\x82\xa5" => 4,
                   "<" => 5, ">" => 5, "^" => 5, "j" => 5, "r" => 5,
                   "0" => 6, "1" => 6, "2" => 6, "3" => 6, "4" => 6, "5" => 6, "6" => 6, "7" => 6, "8" => 6, "9" => 6, "#" => 6, "$" => 6, "&" => 6, "*" => 6, "+" => 6, "-" => 6, "/" => 6, "=" => 6, "?" => 6, "A" => 6, "B" => 6, "C" => 6, "D" => 6, "E" => 6, "F" => 6, "G" => 6, "H" => 6, "J" => 6, "K" => 6, "L" => 6, "M" => 6, "N" => 6, "O" => 6, "P" => 6, "Q" => 6, "R" => 6, "S" => 6, "T" => 6, "U" => 6, "V" => 6, "W" => 6, "X" => 6, "Y" => 6, "Z" => 6, "_" => 6, "a" => 6, "b" => 6, "c" => 6, "d" => 6, "e" => 6, "f" => 6, "g" => 6, "h" => 6, "k" => 6, "m" => 6, "n" => 6, "o" => 6, "p" => 6, "q" => 6, "s" => 6, "t" => 6, "u" => 6, "v" => 6, "w" => 6, "x" => 6, "y" => 6, "z" => 6, "\x82\x9f" => 6, "\x82\xa0" => 6, "\x82\xa1" => 6, "\x82\xa2" => 6, "\x82\xa3" => 6, "\x82\xa4" => 6, "\x82\xa7" => 6, "\x82\xa8" => 6, "\x82\xa9" => 6, "\x82\xaa" => 6, "\x82\xab" => 6, "\x82\xac" => 6, "\x82\xad" => 6, "\x82\xae" => 6, "\x82\xaf" => 6, "\x82\xb0" => 6,
                   "%" => 7, "{" => 7, "}" => 7, "\x82\xa6" => 7,
                   "~" => 9,
                   "@" => 11,
                   "\x8e\xe5\x90\x6c\x8c\xf6\x96\xbc" => 112,
                   "\x92\x87\x8a\xd4\x96\xbc\x82\x51" => 112,
                   "\x92\x87\x8a\xd4\x96\xbc\x82\x52" => 112,
                   "\x92\x87\x8a\xd4\x96\xbc\x82\x53" => 112,
"\tc(0)" => 0,
"\tc(1)" => 0,
"\tc(2)" => 0,
"\tc(3)" => 0,
"\tc(4)" => 0,
"\tc(5)" => 0,
"\tc(6)" => 0,
"\tc(7)" => 0,
"\tc(8)" => 0,
"\tc(9)" => 0,
"\tc(10)" => 0,
"\tc(11)" => 0,
"\tc(12)" => 0,
"\tc(13)" => 0,
"\tc(14)" => 0,
"\tc(15)" => 0,
"\tc(16)" => 0,
"\tc(17)" => 0,
"\tc(18)" => 0,
"\tc(19)" => 0,
"\tc(20)" => 0,
"\tc(21)" => 0,
"\tc(22)" => 0,
"\tc(23)" => 0,
"\tc(24)" => 0);
  $a = fopen($in,"r");
  if(is_resource($a)) {
    $b = fopen($out,"w");
    $start = 0;
    $offset = 0;
    $lines = 0;
    if($buf = fgets($a)) {
      $buf = strtr($buf,$replacing);
      $buf = fread($a,$buf);
      fseek($a,2,SEEK_CUR);
      $data = gzuncompress(base64_decode(strtr($buf,$replacing)));
      $length = strlen($data);
      fwrite($b,"DCPB");
      $buf = LongToBinary($length);
      fwrite($b,$buf);
      fwrite($b,$data);
      while($buf = fgets($a)) {
        $cntr = 1;
        ++$lines;
        $start += 8;
        $buf = strtr($buf,$replacing);
        if($replacerstart <= $lines) {
          $buf = strtr($buf,$replacing2);
          $length = strlen($buf);
          $buf2 = "";
          $linelength = 0;
          $words = explode(" ",$buf);
          $wordscount = count($words);
          for($y=0;$y<$wordscount;++$y) {
            $wordlength = strlen($words[$y]);
            $tempstring = "";
            $wlength = 0;
            for($x=0;$x<$wordlength;++$x) {
              $tempstring .= $words[$y][$x];
              if(isset($charlen[$tempstring])) {
                $wlength += $charlen[$tempstring];
                $tempstring = "";
              }
            }
            if(($linelength+$wlength) > $maxlen) {
              $linelength = 0;
              $buf2 .= $dividors[$cntr % 2] . $words[$y];
              ++$cntr;
            } else {
              if($linelength>0) $buf2 .= " ";
              $buf2 .= $words[$y];
              $linelength += $wlength;
            }
            $wlength = 0;
            if($y !== ($wordscount-1)) { $linelength += 3; }
          }
          $buf = $buf2;
        }
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
}


function LongToBinary($num) {
 $buf = chr($num & 255);
 $buf .= chr(($num >> 8) & 255);
 $buf .= chr(($num >> 16) & 255);
 $buf .= chr(($num >> 24) & 255);
 return $buf;
}

function DecodeDCPB($in,$out) {
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
    if($buf === "DCPB") {
      fwrite($b,"\xef\xbb\xbf");
      $buf = fread($a,4);
      $rawdatalength = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
      $buf = fread($a,$rawdatalength);
      $b64 = base64_encode(gzcompress($buf,9));
      $b64length = strlen($b64);
      fwrite($b,$b64length);
      fwrite($b,PHP_EOL);
      fwrite($b,$b64);
      fwrite($b,PHP_EOL);
      $buf = fread($a,4);
      $amount = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
      if($amount > 0) {
        for($x=0;$x<$amount;++$x) {
          $buf = fread($a,4);
          $pos[] = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]) + 12 + $rawdatalength;
          $buf = fread($a,4);
          $len[] = (ord($buf[3]) << 24) + (ord($buf[2]) << 16) + (ord($buf[1]) << 8) + ord($buf[0]);
        }
      }
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
}

if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
  echo "php ",__FILE__," c/d [infile] [outfile]\n\n";
  exit;
}

switch($argv[1]) {
  case "c":
    EncodeDCPB($argv[2],$argv[3],$argv[4]);
    break;
  case "d":
    DecodeDCPB($argv[2],$argv[3]);
    break;
  default:
    echo "Error!\n";
    exit;
}
?>