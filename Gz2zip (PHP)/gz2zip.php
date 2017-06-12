<?php

/*

GZ2ZIP v1.02,
Copyright (c) 2015, Mr_KrzYch00

Written in PHP 5.3 under windows.

Converts GZ to ZIP file, no recompression occurs, Deflate stream is coppied to newly created ZIP file.

In order to run this tool You need PHP-CLI that can be downloaded from: http://windows.php.net .
Not tested on Linux php CLI, but may work...

Dedicated to Public Domain. Please don't delete original creator credits ;)

v1.02
- renamed script name to GZ2ZIP since it actually can work with any standard header GZ file and produces valid ZIP file.

v1.01
- added support for GZ timestamps to be converted to ZIP file standard (use current time if no timestamp is detected),
- added support for filename inside GZ archive,
- better displaying of on-screen information.

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
 return str_pad($s,56,' .');
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
 echo "\nGZ2ZIP v1.01 by Mr_KrzYch00.";
 echo "\nConverts GZ to ZIP file, will work on any gz file as long as it contains standard header.";
 echo "\nNo recompression occurs, Deflate stream is coppied.\n";
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:GZ]\n\n";
 echo "Example:\n";
 echo "php ",__FILE__," somefile.gz\n";
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
if(file_exists("{$filename}.zip")) {
 echo "ERROR: File ",$filename,".zip already exists, exitting . . .\n";
 exit;
}
$CDIRPK_HEADER="PK".chr(1).chr(2).chr(20).chr(0).chr(20).chr(0).chr(2).chr(0);
$PK_HEADER="PK".chr(3).chr(4).chr(20).chr(0).chr(2).chr(0);
$CDIRENDPK_HEADER="PK".chr(5).chr(6);
$size=filesize($file);
$handle = fopen($file,"r");
$testgz=array(31,139);
for($x=0;$x<2;++$x) {
 $test=ord(fread($handle,1));
 if($test!==$testgz[$x]) {
  echo "ERROR: Not a GZ file . . .\n";
  exit;
 }
}
$f_compmethod=fread($handle,1);
if(ord($f_compmethod)>4 && ord($f_compmethod)!==8) {
 echo "ERROR: Wrong compression method . . .\n";
 exit;
}
$f_flags=ord(fread($handle,1));
if($f_flags!==0 && $f_flags!==8) {
 echo "ERROR: Unsupported flags, this tool support only 0 bits set (0x00) or bit 3 set (0x08) - filename . . .\n";
 exit;
}

echo "\n";

$f_unix=0;
$f_unix_bin=fread($handle,4);
$f_unix=BinToNum($f_unix_bin);
if($f_unix>0) {
 echo p("Unix timestamp detected in GZ file:"),number_format($f_unix,0,'',' '),"\n";
} else {
 $f_unix=@date('U');
 echo p("No Unix timestamp detected, will use current time:"),number_format($f_unix,0,'',' '),"\n";
}
$year = str_pad(decbin(@date('Y',$f_unix)-1980),7,'0',STR_PAD_LEFT);
$month = str_pad(decbin(@date('n',$f_unix)),4,'0',STR_PAD_LEFT);
$day = str_pad(decbin(@date('j',$f_unix)),5,'0',STR_PAD_LEFT);
$hour = str_pad(decbin(@date('G',$f_unix)),5,'0',STR_PAD_LEFT);
$minute = str_pad(decbin(@date('i',$f_unix)),6,'0',STR_PAD_LEFT);
$second = str_pad(decbin(floor(@date('s',$f_unix)/2)),5,'0',STR_PAD_LEFT);
$ms_date=bindec($year.$month.$day);
$ms_time=bindec($hour.$minute.$second);
$f_zipdate=NumToBin($ms_date,2);
$f_ziptime=NumToBin($ms_time,2);


$dummy=fread($handle,2);
$h_length=18;
if($f_flags===8) {
 $filenamein='';
 $buff='';
 do {
  $filenamein.=$buff;
  $buff=fread($handle,1);
  ++$h_length;
 } while(ord($buff)!==0);
 echo p("Filename found in GZ file:"),$filenamein,"\n";
} else {
 $filenamein=$filename;
 echo p("No filename detected in GZ file, will use:"),$filenamein,"\n";
}

echo p("MS-DOS Format Date (converted):"),number_format($ms_date,0,'',' '),"\n";
echo p("MS-DOS Format Time (converted):"),number_format($ms_time,0,'',' '),"\n";

$f_fnlengthb=NumToBin(strlen($filenamein),2);
$f_stream=fread($handle,$size-$h_length);
$f_csize=strlen($f_stream);
$f_csizeb=NumToBin($f_csize,4);
$f_crc = fread($handle,4);
$f_unsize = '';
$uncsize=0;
$f_unsize=fread($handle,4);
$uncsize=BinToNum($f_unsize);
echo p("Original Size: "),number_format($uncsize,0,'',' ')," bytes\n";
echo p("Packed Size (Compressed):"),number_format($f_csize,0,'',' ')," bytes\n";
echo p("CRC32:"),strtoupper(strrev(bin2hex($f_crc))),"\n";
fclose($handle);
$writtenbytes=0;
$handle = fopen("{$filename}.zip","w");
$writtenbytes+=fwrite($handle,$PK_HEADER);
$writtenbytes+=fwrite($handle,$f_compmethod);
$writtenbytes+=fwrite($handle,chr(0));
$writtenbytes+=fwrite($handle,$f_ziptime);
$writtenbytes+=fwrite($handle,$f_zipdate);
$writtenbytes+=fwrite($handle,$f_crc);
$writtenbytes+=fwrite($handle,$f_csizeb);
$writtenbytes+=fwrite($handle,$f_unsize);
$writtenbytes+=fwrite($handle,$f_fnlengthb);
for($x=0;$x<2;++$x) $writtenbytes+=fwrite($handle,chr(0));
$writtenbytes+=fwrite($handle,$filenamein);
$writtenbytes+=fwrite($handle,$f_stream);
$cdir_offset=$writtenbytes;
$cdirwritten=0;
$cdirwritten+=fwrite($handle,$CDIRPK_HEADER);
$cdirwritten+=fwrite($handle,$f_compmethod);
$cdirwritten+=fwrite($handle,chr(0));
$cdirwritten+=fwrite($handle,$f_ziptime);
$cdirwritten+=fwrite($handle,$f_zipdate);
$cdirwritten+=fwrite($handle,$f_crc);
$cdirwritten+=fwrite($handle,$f_csizeb);
$cdirwritten+=fwrite($handle,$f_unsize);
$cdirwritten+=fwrite($handle,$f_fnlengthb);
for($x=0;$x<8;++$x) $cdirwritten+=fwrite($handle,chr(0));
$cdirwritten+=fwrite($handle,chr(32));
for($x=0;$x<7;++$x) $cdirwritten+=fwrite($handle,chr(0));
$cdirwritten+=fwrite($handle,$filenamein);
$writtenbytes+=$cdirwritten;
$writtenbytes+=fwrite($handle,$CDIRENDPK_HEADER);
for($x=0;$x<4;++$x) $writtenbytes+=fwrite($handle,chr(0));
for($x=0;$x<2;++$x) $writtenbytes+=fwrite($handle,chr(1).chr(0));
$f_csizeb=NumToBin($cdirwritten,4);
$writtenbytes+=fwrite($handle,$f_csizeb);
$f_csizeb=NumToBin($cdir_offset,4);
$writtenbytes+=fwrite($handle,$f_csizeb);
for($x=0;$x<2;++$x) $writtenbytes+=fwrite($handle,chr(0));
fclose($handle);
echo p("Written bytes:"),number_format($writtenbytes,0,'',' ')," bytes\n\n";
echo "Successfully converted GZ to ZIP file (Deflate stream coppied) . . .\n";

?>

