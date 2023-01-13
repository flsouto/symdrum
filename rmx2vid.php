<?php


$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

use FlSouto\Sampler;

if(empty($argv[2])){
    die("usage: cmd <dir> <idx>\n");
}

$dir = $argv[1]."/";
$rmx_d = $dir . "rmx/";
$idx = $argv[2];

if($idx == 'all'){
    $indexes = [];
    foreach(glob($rmx_d."*.wav") as $f){
        if(preg_match("/(\d\d)\.wav$/",$f,$m)){
            passthru("php rmx2vid.php $dir $m[1]");
        }
    }
    die();
}

$loop = new Sampler($rmx_d."$idx.wav");
$len = $loop->len();

$src = json_decode(file_get_contents($rmx_d."$idx.json"),true);

function time2src($time){
    global $src,$len;
    $s_len = $len / 4;
    if($time < $s_len){
        // nothing
    } else if( $time < $s_len * 2){
        $time = $time - $s_len;
    } else if( $time < $s_len * 3){
        $time = $time - $s_len * 2;
    } else {
        $time = $time - $s_len * 3;
    }

    $s_len = $s_len / 4;

    if($time < $s_len){
        return 0;
    } else if( $time < $s_len * 2){
        return 1;
    } else if( $time < $s_len * 3){
        return 2;
    } else {
        return 3;
    }

}

if(!is_dir($frm_d = $rmx_d."frames/")){
    mkdir($frm_d, 0777);
}
shell_exec("rm $frm_d*.jpg 2>&1");

$seed = time();

for($i=0,$j=0;$i<$len;$i+=.1,$j++){
    $srcf = $src[time2src($i)];
    preg_match("/^\d\d/",$srcf,$m);
    $srci = $m[0];
    $pool = glob($f = $dir."{$srci}_frames/"."*.jpg");
    $pool = array_chunk($pool, count($pool)/3);

    if(strstr($srcf,'drm_sym')){
        $pool = $pool[1];
    } else if(strstr($srcf,'drm')){
        $pool = $pool[2];
    } else {
        $pool = $pool[0]; // sym
    }
    srand( $srci + $seed );
    $rand = $pool[array_rand($pool)];
    $frm_f = $frm_d . str_pad($j, 4, "0", STR_PAD_LEFT).".jpg";
    echo $frm_f."\n";
    copy($rand, $frm_f);
}


$target = "$rmx_d/{$idx}.wav";
$dummy = new Sampler($target);
$len = $dummy->len();
$fps = 10;
$dummy->save('/tmp/target.mp3');

shell_exec("ffmpeg -y -t $len -r $fps -i $rmx_d/frames/%04d.jpg -c:v libx264 -pix_fmt yuv420p -crf 23 -r $fps -y /tmp/frames.mp4");
shell_exec("ffmpeg -y -i /tmp/frames.mp4 -i /tmp/target.mp3 -c copy -map 0:v:0 -map 1:a:0 $rmx_d/{$idx}.mp4 -shortest");

