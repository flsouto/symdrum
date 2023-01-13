<?php

$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

use FlSouto\Sampler;

if(empty($argv[1])){
    die("usage: cmd <dir>\n");
}

$dir = $argv[1]."/";
$rmx_d = $dir . "rmx/";

$last = 0;
foreach(glob($rmx_d."*.wav") as $f){
    $base = basename($f);
    preg_match("/^\d\d/",$base,$m);
    if(!empty($m[0])){
        $i = (int)$m[0];
        if($i > $last){
            $last = $i;
        }
    }
}

$new = str_pad($last + 1, 2, '0',STR_PAD_LEFT);
rename($rmx_d."tmp.wav", $f=$rmx_d."$new.wav");
echo "Saved $f\n";
rename($rmx_d."tmp.json", $f=$rmx_d."$new.json");
echo "Saved $f\n";
