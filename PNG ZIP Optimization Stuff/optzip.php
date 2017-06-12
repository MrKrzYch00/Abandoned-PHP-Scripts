<?php
set_time_limit(0);

if(!isset($argv[1])) {
 echo "\nArguments:\n";
 echo "php ",__FILE__," [In:ZIP] (number of tries, default 100)\n";
 echo "    SIP argument must NOT conain any spaces!\n\n";
 echo "Examples:\n";
 echo "php ",__FILE__," game.zip 50\n";
 echo "php ",__FILE__," script.zip 1000\n";
 echo "php ",__FILE__," program.zip\n\n";
 exit;
}

$file = $argv[1];
if(!file_exists($file)) {
 echo "File ",$file," does not exists, exitting . . .\n";
}

list($filename,$extension)=explode('.',$argv[1],2);

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
 echo " Appending ",$extraargs," to kzip . . .";
 if(strpos($extraargs,"/n1 ")===false) {
  $nomix=0;
 } else {
  echo " Skipping huffmix due to /n1 used . . .";
  $nomix=0;
 }
} else {
 $nomix=0;
 $extraargs='';
}
echo "\n\n";

exec("(7z x {$file} -y -r- -o{$filename} & kzip2gz {$filename}.zip {$filename}.gz) >NUL");
echo "Extracted {$file} to {$filename} dir . . .\n";
$t=scandir($filename);
if(isset($t[3])) {
 echo "CRITICAL ERROR: more files detected in ",$filename,"\\ directory, exitting . . .\n";
 exit;
}

$orig_size=filesize("{$filename}.gz");
$starting_size=$orig_size;

echo "File: ",$filename,".gz\n";
echo "Size: ",$starting_size," B\n\n";
echo "PLEASE NOTE, You need to convert this file back to ZIP without recompression if You plan to use the result with zipmix . . .\n\n";

while($tries) {
 --$tries;
 $ztries=$otries-$tries;
 echo "Try: ",$ztries," / ",$otries,", verbose: ";
 exec("(kzip {$filename}_t1.zip {$filename}\\{$t[2]} /rn /force /y {$extraargs} & kzip2gz {$filename}_t1.zip {$filename}_t1.gz) >NUL 2>NUL");
 unlink("{$filename}_t1.zip");
 $dotimes=5;
 while($dotimes) {
  exec("(deflopt /s {$filename}_t1.gz & defluff <{$filename}_t1.gz >{$filename}_t2.gz & huffmix -q -f {$filename}_t1.gz {$filename}_t2.gz {$filename}_t3.gz & move /y {$filename}_t3.gz {$filename}_t1.gz & del /y {$filename}_t2.gz & deflopt /s /b {$filename}_t1.gz) >NUL 2>NUL");
  --$dotimes;
 }

 exec("(huffmix -q -f {$filename}.gz {$filename}_t1.gz {$filename}_t2.gz & move /y {$filename}_t2.gz {$filename}_t1.gz) >NUL 2>NUL");
 $best=0;
 $max=2;
 for($x=1;$x<$max;++$x) {
  if(file_exists("{$filename}_t{$x}.gz")) {
   $filesizes=filesize("{$filename}_t{$x}.gz");
   if($filesizes<$orig_size) {
    unlink("{$filename}.gz");
    rename("{$filename}_t{$x}.gz","{$filename}.gz");
    $os=$orig_size;
    $orig_size=$filesizes;
    $best=$x;
   } else {
     unlink("{$filename}_t{$x}.gz");
   }
  } else {
   $filesizes=-1;
  }
  echo $filesizes;
  if($x!==($max-1)) echo ' / '; else echo "       \r";
 }
 if($best!==0) {
  echo "Produced smaller file on try #",$ztries,": t",$best,", NEW: ",$orig_size," LAST: ",$os," ORIG: ",$starting_size," . . .                    \n";
 }
}


echo "Optimisation completed!\n\n";
echo "File: ",$file," -> ",$filename,".gz\n";
echo "Size before: ",$starting_size," B\n";
echo "Size after : ",$orig_size," B\n";
echo "Reduced by : ",($starting_size-$orig_size)," B\n\n";
unlink("{$filename}\\{$t[2]}");
rmdir($filename);


?>

