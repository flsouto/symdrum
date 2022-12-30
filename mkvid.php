<?php

use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(!isset($argv[2])){
    die("Usage: cmd <dir> <index>\n");
}

$dir = $argv[1];
$index = $argv[2];

$target = "$dir/{$index}_drm_sym.wav";
$dummy = new Sampler($target);
$dummy->save('/tmp/target.mp3');
$fps = round(15 - ($dummy->len()/4));

shell_exec("ffmpeg -y -r $fps -i $dir/{$index}_frames/%03d.jpg -c:v libx264 -pix_fmt yuv420p -crf 23 -r $fps -y /tmp/frames.mp4");
shell_exec("ffmpeg -y -i /tmp/frames.mp4 -i /tmp/target.mp3 -c copy -map 0:v:0 -map 1:a:0 $dir/{$index}_vid.mp4");
