<?php
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

use FlSouto\Sampler;

$loops = glob(__DIR__."/symdrumiii/"."*.wav");

$size = mt_rand(70,100) / 10;

function getLoop(){
    global $loops, $size;
    $i = array_rand($loops);
    if(is_string($loops[$i])){
        $loops[$i] = (new Sampler($loops[$i]))->copy(0,'1/4')->resize($size)->split(8);
    }
    return $loops[$i];
}

$remix = Sampler::silence(0);
for($i=0;$i<4;$i++){
    echo "At $i\n";
    $loop = getLoop();
    $loop = $loop[array_rand($loop)]();
    if(mt_rand(0,1)){
        $loop->mod('reverse');
    }
    if(mt_rand(0,1)){
        $loop->chop(mt_rand(1,4));
    }
    $remix->add($loop);
}

$remix->x(4)->play();




