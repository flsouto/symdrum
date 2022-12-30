<?php

if(empty($argv[1])){
    die("usage: cmd <dir>\n");
}

$dir = $argv[1]."/";

$grouped = [];
foreach(glob($dir."*.wav") as $f){
    $base = basename($f);
    $index = explode('_', $base)[0];
    if(ctype_digit($index)){
        $grouped[$index][] = $base;
    }
}

$tmp = [];
foreach(array_values($grouped) as $i => $g){
    foreach($g as $f){
        $index = $i + 1;
        $new_f = preg_replace("/^\d+/",str_pad($index,2,'0',STR_PAD_LEFT),$f);
        $tmp_new_f = '_'.$new_f;
        echo $tmp_new_f."\n";
        rename($dir.$f, $dir.$tmp_new_f);
        $tmp[$dir.$tmp_new_f] = $dir.$new_f;
    }
}

foreach($tmp as $t => $f){
    echo $f."\n";
    rename($t, $f);
}

