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

$target = "$dir/{$index}_{$type}.wav";
$a = new Sampler($target);
$len = $a->len();

switch($args){
    case ':undo' :
        $a = new Sampler("$target.bkp");
    break;
    case ':lowbd' :
        [$a,$b,$c,$d] = $a->split(4);
        $b->mod('pitch -80');
        $d->mod('pitch -80');
        $a->add($b)->add($c)->add($d);
    break;
    case ':lowd' :
        [$a,$b,$c,$d] = $a->split(4);
        $d->mod('pitch -80');
        $a->add($b)->add($c)->add($d);
    break;
    case ':revd-2' :
        [$a,$b,$c,$d] = $a->split(4);
        $d->part('-1/2')->mod('reverse')->sync();
        $a->add($b)->add($c)->add($d);
    break;
    case ':chopd-2:4' :
        [$a,$b,$c,$d] = $a->split(4);
        $d->part('-1/2')->chop(4)->sync();
        $a->add($b)->add($c)->add($d);
    break;
    case ':inf' :
        [$a,$b] = $a->cut(0,'1/4')->split(2);
        $b->fade(0,-15);
        $a->fade(-15,0);
        $a->mix($b,false)->x(8);
    break;

    default:
        if(substr($args,0,2)=='->'){
            eval('$a = $a'.$args.';');
        } else {
            $a->mod($args);
        }
    break;
}

copy($target, "$target.bkp");

$a->resize($len);
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
