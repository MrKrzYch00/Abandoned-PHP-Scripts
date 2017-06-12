<?php

function readline($prompt = null){
    if($prompt){
        echo $prompt;
    }
    $fp = fopen("php://stdin","r");
    $line = rtrim(fgets($fp, 1024));
    return $line;
}

function getproxyadd() {
    echo "\x07";
    usleep(200000);
    echo "\x07";
    echo "\n";
    return readline('Type in new proxy address: ');
}

function pingProxy($proxy){
    list($domain,$port) = explode(':',$proxy);
    $starttime = microtime(true);
    $file      = @fsockopen ($domain, $port, $errno, $errstr, 5);
    $stoptime  = microtime(true);
    $status    = 0;

    if (!$file) $status = -1;  // Site is down
    else {
        fclose($file);
        $status = ($stoptime - $starttime) * 1000;
        $status = floor($status);
    }
    return $status;
}


ini_set('user_agent','Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2539.0 Safari/537.36');
$doloop = 1; //if to loop proxy array, set to 0 when testing IPs
$manual = 0; //if to type in proxy manual
$dosummary = 1;
 $startiparr=array(
'91.209.136.'
/* to do this on next run from ptpbotcheck:

*/
);
 require "proxyget.php";
 echo "\n";
 $localtime=time()+3600;
 if($manual===1) {
   $proxy=getproxyadd();
 } else {
   $proxy='';
 }
 $x=0;
 echo "\x07";
 Sleep(1);
 echo "\x07";
 $custstart=readline("Custom start:");
 echo "Using {$proxy}    \r";
 $endenum=255;
 $fails=0;
 $failst=0;
 $z=0;
 $zz=0;
 $ping = 0;
 foreach($startiparr as $startip) {
  $startenum=$custstart;
  $custstart=0;
  for($x=$startenum;$x<=$endenum;++$x) {
    $IP=$startip.$x;
     $opts=array('http' => array(
      'method' => 'GET',
      'protocol_version' => 1.1,
      'header' => array('User-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2539.0 Safari/537.36','Connection: close'),
      'proxy' => $proxy,
      'timeout' => 30.0,
      'request_fulluri' => True,
      ),
       'ssl' => array(
       'SNI_enabled' => false
      )
     );
     $context = stream_context_create($opts);
     if(isset($proxy[8])) {
      if($ping<1) $ping = pingProxy($proxy);
      if($ping>0) $handle = @fopen("http://site.com/ip/{$IP}", "r",false,$context);
     } else {
      $ping = 1;
      $handle = @fopen("http://site.com/ip/{$IP}", "r");
     }
     if(!is_resource($handle)) {
      if($fails>2 || $ping<1) {
       $z=0;
       $curTime=time();
       if($manual===1) {
         $proxy=getproxyadd();
       } elseif($localtime<$curTime) {
         $localtime=$curTime+3600;
         $proxy='';
       } else {
         ++$curproxy;
         if($curproxy===$totalproxy) {
          if($doloop===0) {
           if($dosummary===1) {
             echo "\x07";
             usleep(200000);
             echo "\x07";
             echo "\n\nProxy list exhausted, exitting . . .\n";
             echo "Successful proxies: \n";
             for($x=0;$x<$zz;++$x) echo "'",$successproxy[$x],"',\n";
           }
           exit;
          } else {
           $curproxy = 0;
          }
         }
         $proxy=$proxyarr[$curproxy];
       }
       $fails=0;
       $ping = 0;
       $curTime-=$localtime;
       echo "Using {$proxy} [{$curTime}s]   \r";
      } else {
       $fails++;
       echo "+",$fails,"+\r";
      }
       --$x;
     } else {
      if($failst===3) $failst=0;
      $fails=0;
      $contents='';
      stream_set_timeout($handle, 30);
      stream_set_blocking($handle, false);
      $info = stream_get_meta_data($handle);
      $maxloop=300;
      while (!$info['timed_out'] && !feof($handle) && $maxloop) {
       $temp = @fgets($handle, 1160);
       if($temp) $contents .= $temp;
       $info = stream_get_meta_data($handle);
       usleep(1000);
       --$maxloop;
      }
      fclose($handle);
      $servicesstart=strpos($contents,'<tr><th>Services:</th><td>')+26;
      if($servicesstart===false || $servicesstart<200) {
        ++$failst;
        echo "-",$failst,"-\r";
        if($failst===3) {
         echo "Invalid proxy data!\r";
         $z=0;
         $curTime=time();
         if($manual===1) {
           $proxy=getproxyadd();
         } elseif($localtime<$curTime) {
           $localtime=$curTime+3600;
           $proxy='';
         } else {
           ++$curproxy;
           if($curproxy===$totalproxy) {
            if($doloop===0) {
             if($dosummary===1) {
              echo "\x07";
              usleep(200000);
              echo "\x07";
              echo "\n\nProxy list exhausted, exitting . . .\n";
              echo "Successful proxies: \n";
              for($x=0;$x<$zz;++$x) echo "'",$successproxy[$x],"',\n";
             }
             exit;
            } else {
             $curproxy = 0;
            }
           }
           $proxy=$proxyarr[$curproxy];
           $ping = 0;
         }
         $curTime-=$localtime;
         echo "Using {$proxy} [{$curTime}s]   \r";
        }
        --$x;
      } else {
       $failst=0;
       ++$z;
       $servicesend=strpos($contents,'</table>');
       $textlength=strlen($contents);
       $services=trim(substr($contents,$servicesstart,($textlength-$servicesstart)-($textlength-$servicesend)));
       if(substr($services,0,13)==='None detected') {
        echo "                                      ",$IP,": None detected.                   \r";
       } else {
        $services = str_replace("<br>"," - ",$services);
        $services = str_replace("</td></tr><tr><th>",", ",$services);
        $services = str_replace("</th><td>"," ",$services);
        $services = strip_tags($services);
        $iplen=strlen($IP);
        $spaces = '';
        for($lol=$iplen;$lol<23;++$lol) $spaces .= ' ';
        echo "{$IP}{$spaces}1;                                       \n";
       }
       if($z===1) $successproxy[$zz++]=$proxy;
       USleep(500);
      }
     }
   }
  }

while(1) { echo "\x07"; sleep(5); }

?>
