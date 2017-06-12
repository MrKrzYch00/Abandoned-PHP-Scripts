<?php

/*

ZIP2ZLIB v1.00,
Copyright (c) 2015, Mr_KrzYch00

Written in PHP 5.3 under windows.

Converts ZIP to ZLIB file, no recompression occurs, Deflate stream is coppied to newly created ZLIB file.

Mainly to be used with ZLIB2PNG later.

In order to run this tool You need PHP-CLI that can be downloaded from: http://windows.php.net .
Not tested on Linux php CLI, but may work...

Dedicated to Public Domain. Please don't delete original creator credits ;)

v1.00
- initial version.

*/


set_time_limit(0);

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
 echo "\nZIP2ZLIB v1.00 by Mr_KrzYch00.";
 echo "\nConverts ZIP to ZLIB file, will work on any zip file as long as it contains standard header.";
 echo "\nNo recompression occurs, Deflate stream is coppied (only first file found).\n";
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:ZIP]\n\n";
 echo "Example:\n";
 echo "php ",__FILE__," somefile.zip\n";
 exit;
}

$file = $argv[1];
if(!file_exists($file)) {
 echo "ERROR: File ",$file," does not exists, exitting . . .\n";
 exit;
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
if(file_exists("{$filename}.zlib")) {
 echo "ERROR: File ",$filename,".zlib already exists, exitting . . .\n";
 exit;
}

$PK_HEADER="PK".chr(3).chr(4);
//general flag that tells that zlib stream is compressed
//or is not compressed, lol. Quite convenient.
$ZLIB_HEADER=chr(120).chr(1);

$size=filesize($file);
$handle = fopen($file,"r");
$test=fread($handle,4);
if($test!==$PK_HEADER) {
 echo "ERROR: Not a ZIP file . . .\n";
 exit;
}
fseek($handle,14,SEEK_CUR);
$STREAM_LENGTH=BinToNum(fread($handle,4));
fseek($handle,4,SEEK_CUR);
$FN_LENGTH=BinToNum(fread($handle,2));
$EF_LENGTH=BinToNum(fread($handle,2));
$FN_DATA=fread($handle,$FN_LENGTH);
if($EF_LENGTH>0) fseek($handle,$EF_LENGTH,SEEK_CUR);
$STREAM=fread($handle,$STREAM_LENGTH);
fclose($handle);
$STREAM_CRC=mhash(MHASH_ADLER32,$STREAM);
$writtenbytes=0;
$handle = fopen("{$filename}.zlib","w");
$writtenbytes+=fwrite($handle,$ZLIB_HEADER);
$writtenbytes+=fwrite($handle,$STREAM);
$writtenbytes+=fwrite($handle,$STREAM_CRC);
fclose($handle);
echo "\n";
echo p("ZIP filename"),$file,"\n";
echo p("ZLIB filename"),"{$filename}.zlib\n";
echo p("File converted from ZIP archive"),$FN_DATA;
echo "\n\n";
echo p("ZIP size"),number_format($size,'',' '),"\n";
echo p("ZLIB size"),number_format($writtenbytes,'',' '),"\n";
echo p("Deflate size"),number_format($STREAM_LENGTH,'',' ');
echo "\n\n";
echo "Successfully converted ZIP to ZLIB file (Deflate stream coppied) . . .\n";

?>

