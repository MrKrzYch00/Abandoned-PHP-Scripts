<?php

function readline($prompt = null){
    if($prompt){
        echo $prompt;
    }
    $fp = fopen("php://stdin","r");
    $line = rtrim(fgets($fp, 1024));
    return $line;
}
/*

  successful:

'46.101.21.8:3128',
'46.101.34.135:8080',
'46.101.49.65:3128',
'46.101.61.179:8081',
'46.101.150.97:8118',
'46.101.238.67:3128',
'46.101.237.114:8080',
'93.158.193.75:3128',
'52.25.3.17:8080',
'52.8.94.233:3128',
'52.8.188.0:8118',
'188.134.19.102:3129',
'46.0.134.58:8080',
'146.185.156.102:3128',
'146.185.191.82:3128',
'92.222.169.116:3128',
'92.222.167.173:8080',
'103.4.67.68:3130',
'203.202.248.172:8080',
'203.202.249.250:8081',

last checked 87
*/

$doloop = 0; //if to loop proxy array, set to 0 when testing IPs
 $proxyarr = array(

);
 $totalproxy=count($proxyarr);
// $proxy=readline("Type in proxy address or leave empty.");
 $curproxy=0;
 $proxy=$proxyarr[$curproxy];
 ini_set('user_agent','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2376.0 Safari/537.36');
 $startiparr=array(
'121.200.61.',
'124.107.135.'
);
 $x=0;
 $custstart=readline("Custom start:");
 echo "Using {$proxy}    \r";
 $endenum=255;
 $fails=0;
 $failst=0;
 $z=0;
 $zz=0;
 foreach($startiparr as $startip) {
  $startenum=$custstart;
  $custstart=0;
  for($x=$startenum;$x<=$endenum;++$x) {
    $IP=$startip.$x;
     $opts=array('http' => array(
      'method' => 'GET',
      'protocol_version' => 1.1,
      'header' => array('User-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2376.0 Safari/537.36','Connection: close'),
      'proxy' => $proxy,
      'timeout' => 30.0,
      'request_fulluri' => True,
      ),
       'ssl' => array(
       'SNI_enabled' => false
      )
     );
     $context = stream_context_create($opts);
     if(isset($proxy[8]))
      $handle = @fopen("http://site.com/ip/{$IP}", "r",false,$context);
       else
      $handle = @fopen("http://site.com/ip/{$IP}", "r");
     if(!is_resource($handle)) {
      if($fails==0) {
//       echo "Service Temporarily Unavailable, TERMINATING\nTry again in an hour . . .\n";
//       echo "10 tries failed... Couldn't check: {$startip}{$x}\n";
//       exit;
       $z=0;
       ++$curproxy;
       if($curproxy===$totalproxy) {
        if($doloop===0) {
         echo "\n\nProxy list exhausted, exitting . . .\n";
         echo "Successful proxies: \n";
         for($x=0;$x<$zz;++$x) echo "'",$successproxy[$x],"',\n";
         exit;
        } else {
         $curproxy = 0;
        }
       }
       $proxy=$proxyarr[$curproxy];
       $fails=0;
       echo "Using {$proxy}    \r";
      } else {
       $fails++;
       echo "Fail: {$fails}  \r";
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
         ++$curproxy;
         if($curproxy===$totalproxy) {
          if($doloop===0) {
           echo "\n\nProxy list exhausted, exitting . . .\n";
           echo "Successful proxies: \n";
           for($x=0;$x<$zz;++$x) echo "'",$successproxy[$x],"',\n";
           exit;
          } else {
           $curproxy = 0;
          }
         }
         $proxy=$proxyarr[$curproxy];
         echo "Using {$proxy}    \r";
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
        echo "-A INPUT -s {$IP} -j DROP                                                 \n";
       }
       if($z===1) $successproxy[$zz++]=$proxy;
       USleep(500);
      }
     }
   }
  }
  echo "\nSuccessful proxies: \n";
  for($x=0;$x<$zz;++$x) echo "'",$successproxy[$x],"',\n";
?>
