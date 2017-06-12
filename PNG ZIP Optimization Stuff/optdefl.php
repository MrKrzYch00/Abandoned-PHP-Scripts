<?php
set_time_limit(0);

if(!isset($argv[1])) {
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:PNG] (number of tries, default 100)\n";
 echo "    PNG argument must NOT conain any spaces!\n\n";
 echo "Examples:\n";
 echo "php ",__FILE__," pic.png 50\n";
 echo "php ",__FILE__," small_icon.png 1000\n";
 echo "php ",__FILE__," house.png\n\n";
 exit;
}

$file = $argv[1];
if(!file_exists($file)) {
 echo "File ",$file," does not exists, exitting . . .\n";
}

list($filename,$extension)=explode('.',$argv[1],2);

$orig_size=filesize($file);
$starting_size=$orig_size;

if(isset($argv[2])) {
 $tries = (int)$argv[2];
 if($tries<1) $tries=1;
 $otries=$tries;
 echo "Using ",$otries," tries . . .";
} else {
 echo "No amount of tries passed, will use 100 . . .";
 $tries=100;
 $otries=100;
}

if(isset($argv[3])) {
 $extraargs=str_replace(";"," ",$argv[3]);
 echo " Appending ",$extraargs," to pngout . . .";
} else {
 $extraargs='';
}
echo "\n";

while($tries) {
 --$tries;
 $ztries=$otries-$tries;
 echo "Try: ",$ztries," / ",$otries,", verbose: ";
 exec("(pngout {$file} {$filename}_t1.png /r /force /y /q {$extraargs} & defluff <{$filename}_t1.png >{$filename}_t2.png & deflopt /s /b {$filename}_t1.png & defluff <{$filename}_t1.png >{$filename}_t3.png & copy /y {$filename}_t3.png {$filename}_t4.png & deflopt /s /b {$filename}_t4.png & huffmix -q {$file} {$filename}_t4.png {$filename}_t5.png & defluff <{$filename}_t5.png >{$filename}_t6.png & copy /y {$filename}_t5.png {$filename}_t7.png & deflopt /s /b {$filename}_t7.png & defluff <{$filename}_t7.png >{$filename}_t8.png & copy /y {$filename}_t8.png {$filename}_t9.png & deflopt /s /b {$filename}_t9.png) >NUL 2>NUL");
 $best=0;
 for($x=1;$x<10;++$x) {
  if(file_exists("{$filename}_t{$x}.png")) {
   $filesizes=filesize("{$filename}_t{$x}.png");
   if($filesizes<$orig_size) {
    unlink($file);
    rename("{$filename}_t{$x}.png",$file);
    $orig_size=$filesizes;
    $new_size=$filesizes;
    $best=$x;
   } else {
     unlink("{$filename}_t{$x}.png");
   }
  } else {
   $filesizes=-1;
  }
  echo $filesizes;
  if($x!==9) echo ' / '; else echo "       \r";
 }
 if($best!==0) {
  echo "\n\nProduced smaller file on try #",$ztries,": t",$best,", NEW: ",$new_size," LAST: ",$orig_size," ORIG: ",$starting_size," . . .\n\n";
 }
}


echo "Optimisation completed!\n\n";
echo "File: ",$file,"\n";
echo "Size before: ",$starting_size," B\n";
echo "Size after : ",$orig_size," B\n";
echo "Reduced by : ",($starting_size-$orig_size)," B\n\n";


?>

