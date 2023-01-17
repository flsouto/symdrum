<?php

if(empty($argv[1])){
    die("usage: cmd <dir>");
}

$dir = $argv[1]."/rmx/";

$w = function($str){
    return "file '$str'";
};

$files = [];
$glob = glob($dir."[0-9][0-9].mp4");
if(file_exists($f=$dir."../concatv.mp4")){
    array_unshift($glob, realpath($f));
}
foreach($glob as $f){
    $files[] = $w(realpath($f));
}

file_put_contents("/tmp/concatv.txt", implode("\n",$files));
$out_f = "$dir/catv.mp4";
passthru("ffmpeg -y -f concat -safe 0 -i /tmp/concatv.txt -c copy $out_f");
