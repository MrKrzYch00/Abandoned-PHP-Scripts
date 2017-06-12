<?php

 $IP='127.0.0.1';
 $port=8332;
 $user='user';
 $password='password';

class BitcoinRawTX {

 protected $IP;
 protected $port;
 protected $auth;
 protected $listreceivedbyaddress;
 protected $listunspent;
 protected $TotalU;
 protected $TotalS;
 protected $TotalO;
 protected $TotalOutput;
 protected $RAWinputs;
 protected $RAWoutputs;
 protected $OutputsChange;
 protected $InputsWeight;
 protected $TXSize;
 protected $Priority;

 public function __construct($IP = null,$port = null,$user = null, $password = null) {
  if(is_null($user)||is_null($password)) throw new Exception("You must specify username and password for JSON-RPC connections.\n");
  if(!is_null($IP)) $this->IP=$IP; else $this->IP='127.0.0.1';
  if(!is_null($port)) $this->port=$port; else $this->port='8332';
  $this->auth=base64_encode("$user:$password");
  $return=$this->SendJson('listreceivedbyaddress',array(0,true));
  if($return===false) throw new Exception("Error communicating with Bitcoin Server, make sure You connect to correct IP and port, and that username and password matches.\n");
  $this->listreceivedbyaddress=json_decode($return,true);
  unset($return);
  $return=$this->SendJson('listunspent');
  $this->listunspent=json_decode($return,true);
  unset($return);
  unset($return2);
  if(isset($this->listunspent[0]['txid'])) { 
   $this->TotalU=0;
   $this->TotalS=0;
   for($x=0,$max=count($this->listunspent);$x<$max;$x++) {
    $this->TotalU+=$this->listunspent[$x]['amount'];
    $this->listunspent[$x]['selected']=0;
   }
   for($x=0,$max=count($this->listreceivedbyaddress);$x<$max;$x++) {
    $this->listreceivedbyaddress[$x]['inInputs']=0;
   }
   $this->RAWoutputs = new stdClass;
  } else {
   throw new Exception("You need at least one unspent transaction to use this API . . .\n");
  }
 }

 private function SendJson($method,$params = array()) {
  $json = array(
   'method' => $method,
   'params' => $params,
   'id' => 1
  );
  $post=json_encode($json);
  $req ="POST / HTTP/1.1\r\n";
  $req.="Host: {$this->IP}\r\n";
  $req.="Connection: close\r\n";
  $req.="Authorization: Basic {$this->auth}\r\n";
  $req.='Content-Length: '.strlen($post)."\r\n";
  $req.="Content-Type: application/json\r\n\r\n";
  $req.="$post\r\n";
  $err=0;
  $fp = fsockopen('ssl://'.$this->IP, $this->port, $err);
  if(!$fp || $err!==0) return false;
  fputs($fp, $req);
  $reply='';
  while (!feof($fp)) $reply.=@fgets($fp, 1024);
  list($head,$reply)=explode("\r\n\r\n",$reply);
  if(strpos($head," 200 OK\r\n")===false) return false;
  $reply2=json_decode($reply,true);
  unset($reply);
  return json_encode($reply2['result']);
 }

 public function SelectInput($id) {
  $id--;
  if($id>count($this->listunspent)||$id<0) return "Input transaction $id doesn't exist . . .\n";
  if($this->listunspent[$id]['selected']==0) {
   $this->TXinInputs($id,1);
   $this->TotalS+=$this->listunspent[$id]['amount'];
   $this->TotalU-=$this->listunspent[$id]['amount'];
  } else {
   $this->TXinInputs($id,0);
   $this->TotalS-=$this->listunspent[$id]['amount'];
   $this->TotalU+=$this->listunspent[$id]['amount'];
  }
 }

 private function TXinInputs($id,$do) {
  $this->listunspent[$id]['selected']=$do;
  for($x=0,$max=count($this->listreceivedbyaddress);$x<$max;$x++) {
   if($this->listreceivedbyaddress[$x]['address']===$this->listunspent[$id]['address']) {
    $this->listreceivedbyaddress[$x]['inInputs']=$do;
   }
  }
 }

 public function ApplyInputs() {
  unset($this->RAWinputs);
  $cntr=0;
  $this->InputsWeight=0;
  $this->TXSize=10;
  for($x=0,$max=count($this->listunspent);$x<$max;$x++) {
   if($this->listunspent[$x]['selected']==1) {
    $this->TXSize+=148;
    $this->InputsWeight+=($this->listunspent[$x]['amount']*100000000)*$this->listunspent[$x]['confirmations'];
    $this->RAWinputs[$cntr]['txid']=$this->listunspent[$x]['txid'];
    $this->RAWinputs[$cntr]['vout']=$this->listunspent[$x]['vout'];
    $cntr++;
   }
  }
 }

 public function PrepareRAW() {
  $return = $this->SendJson('createrawtransaction',array($this->RAWinputs,$this->RAWoutputs));
  return trim($return,'"');
 }

 public function SignRAW($hex) {
  $result = $this->SendJson('signrawtransaction',array($hex));
  $return=json_decode($result,true);
  if(!isset($return['complete'])) return false;
  if($return['complete']!==true) return false;
  return trim($return['hex'],'"');
 }

 public function SendRAW($hex) {
  $return = trim($this->SendJson('sendrawtransaction',array($hex)),'"');
  if(ctype_xdigit($return)) {
   return $return;
  } else {
   return false;
  }
 }

 public function Recipent($btc = null,$amount = null) {
  if(is_null($btc)||is_null($amount)) return false;
  if($this->checkAddress($btc)===true) {
   for($x=0,$max=count($this->listreceivedbyaddress);$x<$max;$x++) {
    if($btc===$this->listreceivedbyaddress[$x]['inInputs']) return false;
   }
   if($amount==0) {
    if(isset($this->RAWoutputs->{$btc})) {
     $this->TotalO-= $this->RAWoutputs->{$btc};
     unset($this->RAWoutputs->{$btc});
     $this->TXSize-=34;
     return true;
    } else {
     return false;
    }
   }
   if(!is_numeric($amount)) return false;
   if($amount<0.00001000) return false;
   $amount=round($amount,8);
   $max=$this->TotalS-$this->TotalO;
   if(isset($this->RAWoutputs->{$btc}))  {
    $max+=$this->RAWoutputs->{$btc};
    if($amount>$max) return false;
    $this->TotalO-= $this->RAWoutputs->{$btc};
   } else {
    $this->TXSize+=34;
    if($amount>$max) return false;
   }
   $this->Priority=$this->InputsWeight/$this->TXSize;
   $this->RAWoutputs->{$btc} = $amount;
   $this->TotalO+= $amount;
   return true;
  } else {
   return false;
  }
 }

 public function ChangeAddress($id) {
  $id--;
  if($id<0||$id>count($this->listreceivedbyaddress)) return false;
  if($this->listreceivedbyaddress[$id]['inInputs']==1) return false;
  $temppriority=$this->InputsWeight/($this->TXSize+34);
  $assignleft=($this->TotalS-$this->TotalO);
  if($temppriority<57600000) $assignleft=$assignleft-(ceil($this->TXSize/1000)*0.0001);
  $this->OutputsChange=$this->listreceivedbyaddress[$id]['address'];
  return $this->Recipent($this->listreceivedbyaddress[$id]['address'],$assignleft);
 }

/* public function SortRecipents() {
  return ksort($this->RAWoutputs);
 }
*/
 public function ShowRecipents() {
 $return='';
 $return.="\n  ID  |             ADDRESS                |  AMOUNT BTC   | AS CHANGE |\n";
 $x=1;
 if(!empty($this->RAWoutputs)) {
  foreach($this->RAWoutputs as $k => $v) {
   $line=$x.' |';
   while(strlen($line)<7) $line=' '.$line;
   $line2=$k.' |';
   while(strlen($line2)<37) $line2=' '.$line2;
   $line=$line.$line2;
   $line2=$this->f($v).' |';
   while(strlen($line2)<16) $line2=' '.$line2;
   $return.="$line$line2         ".($this->OutputsChange===$k? '1' : '0')." |\n";
   $x++;
  }
 }
 $line2=$this->f($this->TotalO);
 while(strlen($line2)<16) $line2=' '.$line2;
 $return.="                                    SUM =>$line2\n";
 $line2=$this->f(($this->TotalS-$this->TotalO));
 while(strlen($line2)<16) $line2=' '.$line2;
 return $return."                   LEFT IN INPUTS (fee) =>$line2\n\n     Transaction Size: ~".number_format($this->TXSize,0,'.',',')." bytes (lower is better)\n Transaction Priority: ~".number_format($this->Priority,2,'.',',')." (higher is better, if above 57,600,000 then no need for FEE)\n";

}

 public function ChangeAddresses() {
  $a=count($this->listreceivedbyaddress);
  for($x=0;$x<$a;$x++) {
   if($this->listreceivedbyaddress[$x]['inInputs']==1) $a--;
  }
  return $a;
 }
 
 public function ShowAddresses() {
  $return='';
  $return.="\n  ID  |             ADDRESS                | IN INPUTS |\n";
  for($x=0,$max=count($this->listreceivedbyaddress);$x<$max;$x++) {
   $line=($x+1).' |';
   while(strlen($line)<7) {
    $line=' '.$line;
   }
   $line2=$this->listreceivedbyaddress[$x]['address'].' |';
   while(strlen($line2)<37) {
    $line2=' '.$line2;
   }
   $return.="$line$line2         {$this->listreceivedbyaddress[$x]['inInputs']} |\n";
  }
  return $return;
 }

 public function ShowInputs() {
  $return='';
  $return.="\n  ID  |                       TRANSACTION ID                             | VOUT (INPUT ID) |             ADDRESS                |  AMOUNT BTC   | SELECTED |\n";
  for($x=0,$max=count($this->listunspent);$x<$max;$x++) {
   $line=($x+1).' |';
   while(strlen($line)<7) {
    $line=' '.$line;
   }
   $line.=' '.$this->listunspent[$x]['txid'].' |';
   $line2=$this->listunspent[$x]['vout'].' |';
   while(strlen($line2)<18) {
    $line2=' '.$line2;
   }
   $line=$line.$line2;
   $line2=$this->listunspent[$x]['address'].' |';
   while(strlen($line2)<37) {
    $line2=' '.$line2;
   }
   $line=$line.$line2;
   $line2=$this->f($this->listunspent[$x]['amount']).' |';
   while(strlen($line2)<16) {
    $line2=' '.$line2;
   }
   $return.="$line$line2        {$this->listunspent[$x]['selected']} |\n";
  }
  return $return.'Total unselected: '.$this->f($this->TotalU)." BTC \n  Total selected: ".$this->f($this->TotalS)." BTC \n";
 }

 private function f($a) {
  return sprintf('%.8F',$a);
 }

 public function inSelected() {
  return $this->TotalS;
 }

 private function checkAddress($address) {
  return true;
  $origbase58 = $address;
  $dec = "0";
  for ($i = 0; $i < strlen($address); $i++) {
   $dec = bcadd(bcmul($dec,"58",0),strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz",substr($address,$i,1)),0);
  }
  $address = "";
  while (bccomp($dec,0) == 1) {
   $dv = bcdiv($dec,"16",0);
   $rem = (integer)bcmod($dec,"16");
   $dec = $dv;
   $address = $address.substr("0123456789ABCDEF",$rem,1);
  }

  $address = strrev($address);

  for ($i = 0; $i < strlen($origbase58) && substr($origbase58,$i,1) == "1"; $i++) {
   $address = "00".$address;
  }

  if (strlen($address)%2 != 0) {
   $address = "0".$address;
  }

  if (strlen($address) != 50) {
   return false;
  }

  if (hexdec(substr($address,0,2)) > 0) {
   return false;
  }

  return substr(strtoupper(hash("sha256",hash("sha256",pack("H*",substr($address,0,strlen($address)-8)),true))),0,8) == substr($address,strlen($address)-8);
 }

}

 echo "Getting BTC Addresses and Input Transactions Data . . .\n\n";
 $BitcoindAPI = new BitcoinRawTX($IP,$port,$user,$password);
 echo $BitcoindAPI->ShowAddresses();

 do {
  echo $BitcoindAPI->ShowInputs();
  echo "\nStep 1) Select / Unselect inputs by typing ID nr (leave empty to stop): ";
  $inadd=preg_replace('/[^0-9]/', '', fgets(STDIN));
  if($inadd!=='') $BitcoindAPI->SelectInput($inadd);
 } while($inadd!=='');
 if($BitcoindAPI->inSelected()>0) {
  $result = $BitcoindAPI->ChangeAddresses();
  echo $BitcoindAPI->ShowAddresses();
  if($result<1) {
   echo "ERROR: Creating rawtransaction requires AT LEAST ONE spare change address. Please create additional BTC address and restart. Exitting.\n\n";
   exit;
  }
  $BitcoindAPI->ApplyInputs();
  do {
   $sendbtc='';
   echo "\nStep 2.1) Type BTC nr You want to send coins to, type the same one to correct, empty to end: ";
   $sendbtc=trim(fgets(STDIN));
   if($sendbtc!=='') {
    echo "Step 2.2) Type amount of BTC to send, type 0 to delete recipent: ";
    $tempsend=preg_replace('/[^0-9.]/', '', fgets(STDIN));
    $result = $BitcoindAPI->Recipent($sendbtc,$tempsend);
    if($result===false) {
      echo "Invalid BTC address or amount, please try again . . .\n";
    } else {
      echo $BitcoindAPI->ShowRecipents();
    }
   }
  } while($sendbtc!==''||$result===false);
  echo $BitcoindAPI->ShowAddresses();
  do {
   $result=true;
   echo "Step 3) >ID< of Your Bitcoin address to send change to, leave empty to use change as a fee: ";
   $id=preg_replace('/[^0-9.]/', '', fgets(STDIN));
   if($id!=='') $result=$BitcoindAPI->ChangeAddress($id);
   if($result===false) {
    echo "Error occured. Make sure You picked correct ID and that address is not already used in inputs, if You don't have at least 0.00005400BTC left then leave empty, same if Your transaction is too big and there is not enough fee to pay.\n";
   } else {
    $BitcoindAPI->ShowRecipents();
   }
  } while($result===false);
  echo "\nAutomatically adding TX Size KB*0.0001 BTC fee, if You wish to change the fee edit list below again or leave empty to proceed.\n";
  echo "WARNING the FEE should be transaction size in KB*0.0001 BTC, if the priority is > 57,600,000 You don't need to pay any FEE.\n";
  echo $BitcoindAPI->ShowRecipents();
  do {
   $sendbtc='';
   echo "\nStep 4) Last corrections: Type BTC nr You want to send coins to, type the same one to correct, empty to end: ";
   $sendbtc=trim(fgets(STDIN));
   if($sendbtc!=='') {
    echo "Type amount of BTC to send, type 0 to delete recipent: ";
    $tempsend=preg_replace('/[^0-9.]/', '', fgets(STDIN));
    $result = $BitcoindAPI->Recipent($sendbtc,$tempsend);
    if($result===false) {
     echo "Invalid BTC address or amount, please try again . . .\n";
    } else {
     echo $BitcoindAPI->ShowRecipents();
    }
   }
  } while($sendbtc!==''||$result===false);
  echo "Generating command . . .\n";
  $rawhex=$BitcoindAPI->prepareRAW();
  do {
   $custom_message='';
   echo "\nStep 5) Add cutom message? Maximum 40 characters, empty to skip: ";
   $custom_message=trim(fgets(STDIN));
   if(strlen($custom_message)>40) {
    echo "Message too long! . . .\n";
   }
  } while(strlen($custom_message)>40);
  if(strlen($custom_message)>0) {
   list($outputs,$inputs)=explode('ffffffff',strrev($rawhex),2);
   $outputs=strrev($outputs);
   $inputs=strrev($inputs);
   $number_of_outputs = hexdec(substr($outputs,0,2))+1;
   $outputs = substr($outputs,2);
   $meslength = strlen($custom_message);
   $scriptlength = $meslength+2;
   $data = '0000000000000000'.substr('00'.dechex($scriptlength),-2).'6a'.substr('00'.dechex($meslength),-2).bin2hex($custom_message);
   $rawhex = $inputs.'ffffffff'.substr('00'.dechex($number_of_outputs),-2).$data.$outputs;
  }
  $rawsignedhex=$BitcoindAPI->SignRAW($rawhex);
  if($rawsignedhex===false) {
   echo "FATAL: Couldn't sign RAW transaction . . .";
  } else {
   echo "\nStep 5) RAW transaction is ready to be sent . . . Send now? [y/n]: ";
   $send = trim(fgets(STDIN));
   if($send==='y') {
    $result=$BitcoindAPI->SendRaw($rawsignedhex);
    if($result===false) {
     echo "FATAL: Couldn't send RAW transaction . . .";
    } else {
     echo 'SUCCESS: Successfully sent RAW transaction . . .';
     echo "\n\n Copy paste this in Your browser URL to see Your transaction: https://blockchain.info/tx/$result\n\n";
    }
   } else {
    echo "\nNOTICE: Sending raw transaction aborted! Here is Your command to send it manually:\n\n";
    echo "sendrawtransaction $rawsignedhex\n\n";
   }
  }
 } else {
  echo "ERROR: No inputs selected! Exitting . . .\n\n";
 }

?>

