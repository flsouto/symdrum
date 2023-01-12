<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(empty($argv[4])){
    die("Usage: cmd <dir> <index> <type> <method>");
}

$dir = $argv[1];
$index = $argv[2];
$type = $argv[3];
$method = $argv[4];

$target = "$dir/{$index}_$type.wav";
$a = new Sampler($target);
$len = $a->len();

switch($method){
    case 'undo' :
        $a = new Sampler("$target.bkp");
    break;
    case 'pitch' :
        [$a,$b,$c,$d] = $a->split(4);
        $b->mod('pitch -80');
        $d->mod('pitch -80');
        $a->add($b)->add($c)->add($d);
    break;
}

copy($target, "$target.bkp");
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
