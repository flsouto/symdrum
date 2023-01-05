<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(empty($argv[3])){
    die("Usage: cmd <dir> <index_old> <index_new>");
}

$dir = $argv[1]."/";
$index_old = $argv[2];
$index_new = $argv[3];
$rename = [];
foreach(glob("$dir{$index_old}_*.wav") as $file){
    $base = basename($file);
    $new_name = $dir.preg_replace("/^{$index_old}_/","{$index_new}_",$base);
    $rename[$file] = $new_name;
    if(file_exists($new_name)){
        die("File exists: $new_name\n");
    }
}

foreach($rename as $old => $new){
    echo "renaming $old => $new\n";
    rename($old, $new);
}
