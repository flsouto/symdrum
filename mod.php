<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(empty($argv[4])){
    die("Usage: cmd <dir> <index> <type> <args>");
}

$dir = $argv[1];
$index = $argv[2];
$type = $argv[3];
$args = implode(' ',array_slice($argv,4));

$target = "$dir/{$index}_$type.wav";
$a = new Sampler($target);
$a->mod($args);
$a->save($target);

if($type == 'sym'){
    $b = "$dir/{$index}_drm.wav";
} else {
    $b = "$dir/{$index}_sym.wav";
}

$b = new Sampler($b);
$ab = $b()->mix($a,false);
$ab->save("$dir/{$index}_drm_sym.wav");

if($type == 'sym'){
    $track = [$a, $ab, $b];
} else {
    $track = [$b, $ab, $a];
}

$track = $track[0]()->add($track[1])->add($track[2]);
$track->save("$dir/{$index}_track.wav");
