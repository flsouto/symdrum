<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(empty($argv[3])){
    die("Usage: cmd <dir> <index> <type> <args>");
}

$dir = $argv[1];
$index = $argv[2];
$type = $argv[3];

$target = "$dir/{$index}_$type.wav";

if($type == 'sym'){
    $b = "$dir/{$index}_drm.wav";
} else {
    $b = "$dir/{$index}_sym.wav";
}

$b = new Sampler($b);
$len = $b->len();
$a = Sampler::select($config["{$type}_wavs"]);
$a->resize($len);
$a->save($target);

$ab = $b()->mix($a,false);
$ab->save("$dir/{$index}_drm_sym.wav");

if($type == 'sym'){
    $track = [$a, $ab, $b];
} else {
    $track = [$b, $ab, $a];
}

$track = $track[0]()->add($track[1])->add($track[2]);
$track->save("$dir/{$index}_track.wav");
