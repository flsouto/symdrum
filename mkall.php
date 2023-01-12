<?php

if(!isset($argv[1])){
    die("Usage: cmd <dir>\n");
}

$dir = $argv[1]."/";
$indexes = [];
foreach(glob($dir."*.wav") as $f){
    preg_match("/^(\d+)_/", basename($f), $m);
    if(!empty($m[1])){
        $indexes[$m[1]]=1;
    }
}

$indexes = array_keys($indexes);
foreach($indexes as $i){
    passthru("php mkfull.php $dir $i");
}

passthru("php concatv.php $dir && php mkdesc.php $dir");
