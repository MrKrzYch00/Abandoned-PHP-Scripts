<?php

 //scanning progress display updated every this many frames, too low value will
 //slow the script down
 $scntr=1000;

 //amount of time of video drop to detect, this value * time between 1st and 2nd frame
 //10 means usually 0.4s for 25fps
 $multi = 10;

 //skip detection if segments is only X seconds long, You can use floating point,
 //this value also skips this many seconds at the video beginning
 //Usually range of 1-3s is good since most keyframes appear every 1-2s anyway
 //Use 0 only if You are sure that the beginning of file is not chopped before audio
 //sync starts because output file may have missing audio then
 $minseconds = 1;

 //don't after this message

 $lasts=0;
 $diff = 0;
 $getkeyframetime=0;
 $ssn=0;
 $cntr=0;

 if(!isset($argv[1]) || !isset($argv[2])) {
   echo "\n\n";
   echo "Video Drop detector & Fixer v1.00 by Mr_KrzYch00";
   echo "\n\n";
   echo "This script uses FFPROB to generate prob file and then scan it\n";
   echo "in order to detect frame-skips in video file, then use FFMPEG\n";
   echo "to cut the video into working chunks and finally to concat them\n";
   echo "together producing video file without video time jumps, youtube video\n";
   echo "processing getting stuck forever or youtube video still-frames.\n";
   echo "\nDesigned to work on Windows, will require mod to run on Linux.\n";
   echo "\n";
   echo "Usage:\n\n";
   echo "php ",__FILE__," [infile] [tempdir]\n";
   echo "\nThe output will be always saved as [infile]__done__.mp4.\n";
   echo "[tempdir] is where Your ffmpeg and ffprob is located and also\n";
   echo "the directory where script will save video chunks to concat.";
   echo "\n\n";
   exit;
 }

 if(substr($argv[2],-1)!=='\\') $argv[2].='\\';

 $com[0] = '"'.$argv[2].'ffmpeg.exe" -v warning -stats ';
 $com[3] = ' -i "'.$argv[1].'" -c copy -copyts "'.$argv[2].'__';

 echo $argv[2]."ffprobe.exe -v warning -select_streams v:0 -show_frames -i \"{$argv[1]}\" >\"{$argv[2]}__ffprobe_info.txt\"\n";
 exec($argv[2]."ffprobe.exe -v warning -select_streams v:0 -show_frames -i \"{$argv[1]}\" >\"{$argv[2]}__ffprobe_info.txt\"");

 $probesize=filesize($argv[2].'__ffprobe_info.txt');

 $a = fopen($argv[2].'__ffprobe_info.txt','r');

 while(!feof($a)) {
  $test = fgets($a,9999);
  if($test==="[FRAME]\r\n") {
   do {
    $test = fgets($a,9999);
    if($getkeyframetime===1 && strpos($test, 'key_frame=1')!==false) {
      $getkeyframetime=2;
    }
    if(strpos($test,'best_effort_timestamp_time=')!==false) {
     if(!isset($frame[0])) {
      $frame[0]=trim(substr($test, 27));
     } elseif(!isset($frame[1])) {
      $frame[1]=trim(substr($test, 27));
      if($diff===0 && isset($frame[0]) && isset($frame[1])) {
       $diff=($frame[1]-$frame[0])*$multi;
      } else {
       echo "ERROR: Frame 0 or 1 not properly read, TERMINATING!\n\n";
       exit;
      }
     } else {
      $frame[0] = $frame[1];
      $frame[1] = trim(substr($test, 27));
      if($scntr===0) {
       echo "Scanning Video prob file: ",round(((ftell($a)/$probesize)*100)),"% / ",nicetime($frame[1]),"       \r";
       $scntr=1000;
      } else {
       --$scntr;
      }
      if($getkeyframetime===2) {
        $getkeyframetime=0;
        $ssn=round(($frame[1]+0.2),1);
        $com[1]="-ss ".nicetime($ssn)." ";
      }
      $testdiff = $frame[1] - $frame[0];
      if($testdiff>$diff) {
       $testval = round(($frame[0]-0.1),1)-$ssn;
       if($testval>$minseconds) {
        $com[2]="-t ".nicetime($testval);
       }
       $getkeyframetime=1;
      }
     }
    }
   } while($test!=="[/FRAME]\r\n");
   if(isset($com[2])) {
    ++$cntr;
    if(!isset($com[1])) $com[1]='';
    $com[9]=$com[0].$com[1].$com[2].$com[3].str_pad($cntr, 2, '0', STR_PAD_LEFT).'.mp4"';
    echo $com[9],"\n";
    exec($com[9]);
    $lasts=$com[1];
    unset($com[2]);
   }
  }
 }

 fclose($a);
 unlink($argv[2].'__ffprobe_info.txt');

 if(isset($com[1])) {
  if($lasts!==$com[1]) {
   ++$cntr;
   $com[9]=$com[0].$com[1].$com[3].str_pad($cntr, 2, '0', STR_PAD_LEFT).'.mp4"';
   echo $com[9],"\n";
   exec($com[9]);
   unset($com[1]);
  }
 }

 if($cntr===0) {
  $com[9]=$com[0].' -i "'.$argv[1].'" -c copy -copyts "'.$argv[1].'__done__.mp4"';
  echo $com[9],"\n";
  exec($com[9]);
 } else {

  echo "\n\nCreating Concat file . . .\n";

  $a = fopen($argv[2].'__ffmpeg_concat.txt','w');

  for($x=1;$x<=$cntr;++$x) {
   $concat = "file '{$argv[2]}__".str_pad($x, 2, '0', STR_PAD_LEFT).".mp4'\n";
   echo $concat;
   fwrite($a, $concat);
  }

  fclose($a);
  echo '"'.$argv[2]."ffmpeg.exe\"  -v warning -stats -f concat -i \"{$argv[2]}__ffmpeg_concat.txt\" -c copy \"{$argv[1]}__done__.mp4\"\n";
  exec('"'.$argv[2]."ffmpeg.exe\"  -v warning -stats -f concat -i \"{$argv[2]}__ffmpeg_concat.txt\" -c copy \"{$argv[1]}__done__.mp4\"");
  unlink($argv[2].'__ffmpeg_concat.txt');

  for($x=1;$x<=$cntr;++$x) {
   unlink($argv[2].'__'.str_pad($x, 2, '0', STR_PAD_LEFT).'.mp4');
  }
 }

 echo "\n\n";

 function nicetime($s) {
  $r='';
  $h = (int)($s/3600);
  if($h>0) $r=$h.':';
  $m = (int)($s/60) - $h*60;
  if($m>0 || ($m===0 && $h>0)) $r.=$m.':';
  $s = round(fmod($s, 60),3);
  $r.=$s;
  return $r;
 }

?>