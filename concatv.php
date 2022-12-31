<?php

if(empty($argv[1])){
    die("usage: cmd <dir> [index]");
}

$dir = $argv[1]."/";

$grouped = [];
foreach(glob($dir."*.mp4") as $f){
    $base = basename($f);
    $index = explode('_', $base)[0];
    $indexes[$index] = 1;
}

$indexes = array_keys($indexes);

$files = [];

$w = function($str){
    return "file '$str'";
};

foreach($indexes as $i){
    $files[] = $w(__DIR__."/$dir/{$i}_vid.mp4");
}

file_put_contents("/tmp/concatv.txt", implode("\n",$files));
$out_f = "$dir/concatv.mp4";
passthru("ffmpeg -y -f concat -safe 0 -i /tmp/concatv.txt -c copy $out_f");
