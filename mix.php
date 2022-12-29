<?php
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);
use FlSouto\Sampler;

$out_dir = __DIR__."/tmp/";
shell_exec("rm $out_dir -R 2>&1");
mkdir($out_dir,0777);

$syms = glob($config['sym_wavs'],GLOB_BRACE);
$drums = glob($config['drm_wavs'],GLOB_BRACE);

shuffle($syms);
shuffle($drums);

foreach($drums as $i => $f){
    echo $i."\n";
    $track = Sampler::silence(0);
    $drm = new Sampler($f);
    $sym = new Sampler(array_shift($syms));
    $sym->resize($drm->len());
    $drm_sym = $drm()->mix($sym,false);
    $track = $sym()->add($drm_sym)->add($drm);
    $drm->save("$out_dir/{$i}_drm.wav");
    $drm_sym->save("$out_dir/{$i}_drm_sym.wav");
    $track->save("$out_dir/{$i}_track.wav");
    $sym->save("$out_dir/{$i}_sym.wav");
}
