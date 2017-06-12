<?php

function readline($prompt = null){
    if($prompt){
        echo $prompt;
    }
    $fp = fopen("php://stdin","r");
    $line = rtrim(fgets($fp, 1024));
    return $line;
}

$buffer = 2048;
$tries = 1;
$src = readline("Type in input file on CD:");
$des = readline("Type in output file on HD:");
$input = fopen($src,"r");
if(is_resource($input)) {
  stream_set_read_buffer($input, 0);
  $output = fopen($des, "w");
  if(is_resource($output)) {
    $size = filesize($src);
    $x = 0;
    for(;;) {
      echo "Reading: {$x} / {$size}                 \r";
      if($x >= $size) break;
      $y = 0;
      for(;;) {
        $data = fread($input, $buffer);
        if($data === false) {
          ++$y;
          echo "Fail reading at offset: {$x} [$y]   \r";
          Sleep(10);
          if($y > $tries) {
            echo "Fail reading at offset: {$x}. Autofill 2048 NULL bytes   \r\n";
            if($buffer > ($size - $x)) $buffer2 = $size - $x; else $buffer2 = $buffer;
            $data = strpad("", $buffer2, chr(0));
            fseek($input, $buffer2, SEEK_CUR);
            break;
          }
        } else {
          if($y > 0) echo "Successfully rescued data at offset: {$x}    \r\n";
          break;
        }
      }
      fwrite($output, $data);
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
echo "Finished!            \r\n";
fclose($input);
fclose($output);

?>