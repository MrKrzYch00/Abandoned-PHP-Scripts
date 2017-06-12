<?php

/*

ZLIB2PNG v1.03,
Copyright (c) 2015, Mr_KrzYch00

Written in PHP 5.3 under windows.

Read original PNG file and a file with IDAT enclosed in ZLIB contained, usually optimized
with zopfli or other tool that can output well compressed file as ZLIB.

In order to run this tool You need PHP-CLI that can be downloaded from: http://windows.php.net .
Not tested on Linux php CLI, but may work...

v1.03
- don't use strrev just to be safe (write reversed string by NumToBin function when 3rd parameter is 1).

v1.02
- fixed a bug while creating some PNG files (never use substr lol, better to fseek backwards a bit).

v1.01
- fixed IDAT length being ACTUAL zlib file size (should not include +4 bytes),
- fixed IEND CRC BUG (used hex instead of decimals by a mistake).

v1.00
- initial version.


Some reference data I was collecting:

{
 PNG_START_HEADER
 chunk length 4 bytes
 "IHDR" (string)
 4 bytes width
 4 bytes height
 1 byte bit depth
 1 byte color
 1 byte compression
 1 byte filter
 1 byte interlace
 4 bytes chunk crc32
} -----> copy from original png

4 bytes length of IDAT (zopfli ZLIB file size) in reverse order
"IDAT" (string)
ZLIB file data
4 bytes CRC32 ("IDAT"+ZLIB stream)
PNG_END_HEADER

*/


set_time_limit(0);

function NumToBin($num,$chars,$rev=0) {
 $bin='';
 for($x=0;$x<$chars;++$x) {
  $c=chr($num & 255);
  if($rev===0) $bin.=$c; else $bin=$c.$bin;
  $num/=256;
 }
 return $bin;
}

function p($s) {
 switch(strlen($s) % 2) {
  case 0:
   $s.=' ';
   break;
  case 1:
   break;
 }
 return str_pad($s,40,' .');
}

 echo "\nZLIB2PNG v1.03 by Mr_KrzYch00\n\n";

if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
 echo "Reads original PNG file headers, appends IDAT with a stream";
 echo "\nfrom ZLIB file and saves as new file.\n";
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:PNG] [In:ZLIB] [Out:PNG]\n\n";
 echo "Example:\n";
 echo "php ",__FILE__," pic.png pic_zopfli.zlib pic_opt.png\n";
 exit;
}

$input1 = $argv[1];
$input2 = $argv[2];
$output = $argv[3];

$input1_s=filesize($input1);
$input2_s=filesize($input2);

$PNG_END_HEADER=chr(0).chr(0).chr(0).chr(0).'IEND'.chr(174).chr(66).chr(96).chr(130);

if(!file_exists($input1) || !file_exists($input2)) {
 echo "ERROR: Either ",$input1," or ",$input2," doesn't exists, exitting . . .\n";
 exit;
}

if(file_exists($output)) {
 echo "ERROR: File ",$output," already exists, exitting . . .\n";
 exit;
}

$handle1 = fopen($input1,"r");
$PNG_START_HEADER=fread($handle1, 8);
if($PNG_START_HEADER!==(chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))) {
 echo $PNG_START_HEADER,"\n";
 echo "ERROR: Not a valid PNG file, exitting . . .\n";
 exit;
}
do {
 $buff=fread($handle1,1);
 $PNG_START_HEADER.=$buff;
 $l=strlen($PNG_START_HEADER);
} while($PNG_START_HEADER[$l-4]!=='I' || $PNG_START_HEADER[$l-3]!=='D' || $PNG_START_HEADER[$l-2]!=='A' || $PNG_START_HEADER[$l-1]!=='T');
fclose($handle1);
$handle = fopen($output,"w");
$writtenbytes=0;
$writtenbytes+=fwrite($handle,$PNG_START_HEADER)-8;
fseek($handle,-8,SEEK_CUR);
$IDAT_SIZE_B=NumToBin($input2_s,4,1);
$writtenbytes+=fwrite($handle,$IDAT_SIZE_B);
$handle2 = fopen($input2,"r");
$IDAT_DATA='IDAT'.fread($handle2, $input2_s);
fclose($handle2);
$writtenbytes+=fwrite($handle,$IDAT_DATA);
$IDAT_CRC=crc32($IDAT_DATA);
$IDAT_CRC_B=NumToBin($IDAT_CRC,4);
$writtenbytes+=fwrite($handle,$IDAT_CRC_B);
$writtenbytes+=fwrite($handle,$PNG_END_HEADER);

echo p("Original PNG filename",50),$input1,"\n";
echo p("Original ZLIB filename",50),$input2,"\n";
echo p("New PNG filename",50),$output;
echo "\n\n";
echo p("Old PNG Size",50),number_format($input1_s,0,'',' ')," bytes\n";
echo p("ZLIB Size",50),number_format($input2_s,0,'',' ')," bytes\n";
echo p("New IDAT CRC32"),str_pad(strtoupper(dechex($IDAT_CRC)),8,'0',STR_PAD_LEFT),"\n";
echo p("New PNG Size",50),number_format($writtenbytes,0,'',' ')," bytes";
echo "\n\n";
echo "Successfully produced new PNG file from PNG + ZLIB !\n\n";


?>

