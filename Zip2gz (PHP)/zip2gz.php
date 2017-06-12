<?php

/*

ZIP2GZ v1.00,
Copyright (c) 2015, Mr_KrzYch00

Written in PHP 5.3 under windows.

Converts ZIP to GZ file, no recompression occurs, Deflate stream is coppied to newly created GZ file.

Stores timestamp, can also store filename.

In order to run this tool You need PHP-CLI that can be downloaded from: http://windows.php.net .
Not tested on Linux php CLI, but may work...

Dedicated to Public Domain. Please don't delete original creator credits ;)

v1.00
- initial version.

*/


set_time_limit(0);

function NumToBin($num,$chars) {
 $bin='';
 for($x=0;$x<$chars;++$x) {
  $bin.=chr($num & 255);
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

function BinToNum($bin) {
 $num=0;
 $max=strlen($bin);
 for($x=0;$x<$max;++$x) {
  $buff=substr($bin,0,1);
  $bin=substr($bin,1,$max);
  $num += ord($buff)*pow(256,$x);
 }
 return $num;
}

if(!isset($argv[1])) {
 echo "\nZIP2GZ v1.00 by Mr_KrzYch00.";
 echo "\nConverts ZIP to GZ file, will work on any zip file as long as it contains standard header.";
 echo "\nNo recompression occurs, Deflate stream is coppied (only first file found).";
 echo "\n-y: put filename in GZ file (optional, default NO).\n";
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:ZIP] (-y)\n\n";
 echo "Example:\n";
 echo "php ",__FILE__," somefile.zip\n";
 exit;
}

$file = $argv[1];
if(!file_exists($file)) {
 echo "ERROR: File ",$file," does not exists, exitting . . .\n";
 exit;
}

if(isset($argv[2]) && $argv[2]==='-y') {
 $keep_name=1;
} else {
 $keep_name=0;
}

$fileparts=explode('.',$argv[1]);
$filename='';
$max=count($fileparts)-1;
for($x=0;$x<$max;++$x) {
 $filename.=$fileparts[$x].'.';
}
$extension=$fileparts[$max];
$f_fnlength=strlen($filename)-1;
$filename=substr($filename,0,$f_fnlength);
if(file_exists("{$filename}.gz")) {
 echo "ERROR: File ",$filename,".gz already exists, exitting . . .\n";
 exit;
}

$PK_HEADER="PK".chr(3).chr(4);
//general flag that tells that zlib stream is compressed
//or is not compressed, lol. Quite convenient.
$GZ_HEADER=chr(31).chr(139);

$size=filesize($file);
$handle = fopen($file,"r");
$test=fread($handle,4);
if($test!==$PK_HEADER) {
 echo "ERROR: Not a ZIP file . . .\n";
 exit;
}
fseek($handle,4,SEEK_CUR);
$COMP_METHOD=BinToNum(fread($handle,2));
if($COMP_METHOD!==8 && $COMP_METHOD!==0) {
 echo "ERROR: Invalid compression, only STORE or Deflate are supported . . .\n";
 exit;
}
$MSDOS_TIME=BinToNum(fread($handle,2));
$MSDOS_DATE=BinToNum(fread($handle,2));
$year   = (($MSDOS_DATE >> 9) & 127)+1980;
$month  = ($MSDOS_DATE >> 5) & 15;
$day    = $MSDOS_DATE & 31;
$hour   = ($MSDOS_TIME >> 11) & 31;
$minute = ($MSDOS_TIME >> 5) & 63;
$second = ($MSDOS_TIME & 31)*2;
$MSDOS_DATE_R = "{$year}-{$month}-{$day}";
$MSDOS_TIME_R = "{$hour}:{$minute}:{$second}";
$UNIX_TIME = @strtotime("{$MSDOS_DATE_R} {$MSDOS_TIME_R}");
$CRC32_B=fread($handle,4);
$CRC32=str_pad(strtoupper(dechex(BinToNum($CRC32_B))),8,'0',STR_PAD_LEFT);
$STREAM_LENGTH=BinToNum(fread($handle,4));
$UNC_SIZE_B=fread($handle,4);
$UNC_SIZE=BinToNum($UNC_SIZE_B);
$FN_LENGTH=BinToNum(fread($handle,2));
$EF_LENGTH=BinToNum(fread($handle,2));
$FN_DATA=fread($handle,$FN_LENGTH);
if($EF_LENGTH>0) fseek($handle,$EF_LENGTH,SEEK_CUR);
$STREAM=fread($handle,$STREAM_LENGTH);
fclose($handle);
$writtenbytes=0;
$handle = fopen("{$filename}.gz","w");
$writtenbytes+=fwrite($handle,$GZ_HEADER);
$writtenbytes+=fwrite($handle,NumToBin($COMP_METHOD,1));
$writtenbytes+=fwrite($handle,chr($keep_name*8));
$writtenbytes+=fwrite($handle,NumToBin($UNIX_TIME,4));
$writtenbytes+=fwrite($handle,chr(2));
$writtenbytes+=fwrite($handle,chr(3));
if($keep_name===1) $writtenbytes+=fwrite($handle,$FN_DATA.chr(0));
$writtenbytes+=fwrite($handle,$STREAM);
$writtenbytes+=fwrite($handle,$CRC32_B);
$writtenbytes+=fwrite($handle,$UNC_SIZE_B);
fclose($handle);
echo "\n";
echo p("ZIP filename:"),$file,"\n";
echo p("GZ filename:"),"{$filename}.gz\n";
echo p("File converted from ZIP archive:"),$FN_DATA;
echo "\n\n";
echo p("ZIP size:"),number_format($size,0,'',' ')," bytes\n";
echo p("GZ size:"),number_format($writtenbytes,0,'',' ')," bytes\n";
echo p("Deflate size:"),number_format($STREAM_LENGTH,0,'',' ')," bytes\n";
echo p("Uncompressed size:"),number_format($UNC_SIZE,0,'',' ')," bytes\n";
echo p("CRC32:"),$CRC32;
echo "\n\n";
echo p("MS-DOS Format Date (detected):"),$MSDOS_DATE_R," (",$MSDOS_DATE,")\n";
echo p("MS-DOS Format Time (detected):"),$MSDOS_TIME_R," (",$MSDOS_TIME,")\n";
echo p("Unix timestamp (converted):"),$UNIX_TIME,"\n";
echo p("Storing filename in GZ:"),($keep_name===0? 'NO' : 'YES'),"\n";
echo "\n\n";
echo "Successfully converted ZIP to GZ file (Deflate stream coppied) . . .\n";

?>

