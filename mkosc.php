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

$loop = new Sampler("$dir/{$index}_$type.wav");
$loop->cut(0,'1/4');
$fps = round(15 - $loop->len());
$amps = [];

$loop->each(1/$fps,function($s) use(&$amps){
    $amp = $s->amp();
    $amps[] = $amp;
    echo $amp.PHP_EOL;
});

$amps = [...$amps, ...$amps, ...$amps, ...$amps];
$i = 0;
$out_dir = "$dir/{$index}_osc_{$type}/";
if(!is_dir($out_dir)) mkdir($out_dir);
shell_exec("rm $out_dir* 2>/dev/null");

if($type == 'sym'){
    $frames_out = glob($config['bkg_imgs'],GLOB_BRACE);
    shuffle($frames_out);
    $frames_out = array_slice($frames_out, 1, 1);
} else {
    $frames_out = ["$dir/drm.jpg"];
}

foreach($amps as $a){
    $contrast = -30 + ($a * 100);
    $frame = str_pad("$i",3,"0",STR_PAD_LEFT);
    echo $frame.PHP_EOL;
    $img = $frames_out[array_rand($frames_out)];
    shell_exec("convert -brightness-contrast $contrast '$img' $out_dir/$frame.jpg");
    $i++;
}

