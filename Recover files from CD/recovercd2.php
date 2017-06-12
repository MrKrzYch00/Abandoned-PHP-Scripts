<?php

class getsec extends Thread {
  public $id2;
  public $done;
  public $offset;
  public $file;
  public $data;

  public function __construct($id,$offset,$file) {
    $this->id2 = (int)$id;
    $this->done = false;
    $this->offset = $offset;
    $this->file = $file;
  }

  public function run() {
    $input = fopen($this->file,"r");
    if(is_resource($input)) {
      stream_set_blocking($input, 0);
      stream_set_read_buffer($input, 0);
      fseek($input, $this->offset);
      $this->data = fread($input, 2048);
      fclose($input);
      $this->done = true;
    } else {
      echo "Error opening {$this->file}   \r\n";
      exit;
    }
  }
}

function readline($prompt = null){
    if($prompt){
        echo $prompt;
    }
    $fp = fopen("php://stdin","r");
    $line = rtrim(fgets($fp, 1024));
    return $line;
}

 if(!isset($argv[1]) || !isset($argv[2])) {
   echo "php ",__FILE__," inputonCD outputonHD\r\n";
   exit;
 }

$failed = array();

$buffer = 2048;
$tries = 1;
$src = $argv[1];
$des = $argv[2];
$desstats = $des.".stats";
$input = fopen($src,"r");
if(is_resource($input)) {
  fclose($input);
  if(file_exists($des)) {
    $output = fopen($des, "a");
    fseek($output,0,SEEK_END);
    $x = ftell($output);
  } else {
    $x = 0;
    $output = fopen($des, "w");
  }
  if(is_resource($output)) {
    if(!file_exists($desstats)) {
      $outputstats = fopen($desstats, "w");
      fclose($outputstats);
    }
    $outputstats = fopen($desstats, "a");
    fseek($outputstats,0,SEEK_END);
    $size = filesize($src);
    for(;;) {
      echo "Reading: {$x} / {$size}                 \r";
      if($x >= $size) break;
      unset($thread);
      $thread = new getsec(0,$x,$src);
      $thread->start();
      $z = 0;
      for(;;) {
        USleep(100000);
        ++$z;
        if($thread->done === true) {
          $data = $thread->data;
          break;
        } else {
          echo "Waiting for offset: {$x} [$z]  \r";
          if($z > 1800) {
            $failed[] = $x;
            echo "Fail reading at offset: {$x}. Autofill 2048 NULL bytes   \r\n";
            if($buffer > ($size - $x)) $buffer2 = $size - $x; else $buffer2 = $buffer;
            $data = str_pad("", $buffer2, chr(0));
            $thread->kill();
            break;
          }
        }
      }
      fwrite($output, $data);
      if($z > 600) {
        fwrite($outputstats,"{$x},");
        echo "If it doesn't exit, press CTRL+C\r\n";
        exit;
      }
      $x += 2048;
    }
  } else {
    echo "Can't open output file! \r\n";
    exit;
  }
} else {
  echo "Can't open input file! \r\n";
  exit;
}
var_dump($x);
echo "Finished!            \r\n";
fclose($output);

?>