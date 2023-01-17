<?php

use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

if(!isset($argv[2])){
    die("Usage: cmd <dir> <index>\n");
}

$dir = $argv[1];
$index = $argv[2];

$target = "$dir/{$index}_track.wav";
$dummy = new Sampler($target);
$len = $dummy->len();
//$fps = round(15 - ($len/3/4));
$fps = 10;
$dummy->save('/tmp/target.mp3');

shell_exec("ffmpeg -y -t $len -r $fps -i $dir/{$index}_short_frames/%04d.jpg -c:v libx264 -pix_fmt yuv420p -crf 23 -r $fps -y /tmp/frames.mp4");
shell_exec("ffmpeg -y -i /tmp/frames.mp4 -i /tmp/target.mp3 -c copy -map 0:v:0 -map 1:a:0 $dir/{$index}_short_vid.mp4 -shortest");
