<?php

// Turn on error reporting
//error_reporting(E_ALL);

// Turn off all error reporting
//error_reporting(0);

//POST
$data = $_POST["c"];

include 'wordfilename.php';

$file = fopen($wordfile,"w");
//$file = fopen("word.txt","w");
fwrite($file,$data);
fclose($file);

echo "SAVED";

?>