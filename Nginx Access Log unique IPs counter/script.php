<?php


$handle = fopen("access.log","r");
$IPs=array();
$cntr=0;
if(is_resource($handle)) {
 while(($line = fgets($handle)) !== false) {
  $IP=substr($line,0,strpos($line,' - - '));
  if(!in_array($IP,$IPs)) {
   ++$cntr;
   echo "Counting {$cntr} - {$IP}        \r";
   $IPs[]=$IP;
  }
 }
}

echo "\n",count($IPs);

?>