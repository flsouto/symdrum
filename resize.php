<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(empty($argv[3])){
    die("Usage: cmd <dir> <index> <len>");
}

$dir = $argv[1];
$index = $argv[2];
$len = $argv[3];

$drm = new Sampler($drm_f="$dir/{$index}_drm.wav");
$sym = new Sampler($sym_f="$dir/{$index}_sym.wav");

$drm->resize($len);
$drm->save($drm_f);

$sym->resize($len);
$sym->save($sym_f);

$drm_sym = $drm()->mix($sym,false);
$drm_sym->save("$dir/{$index}_drm_sym.wav");

$track = $sym()->add($drm_sym)->add($drm);
$track->save("$dir/{$index}_track.wav");

