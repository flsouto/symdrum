<?php
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);

use FlSouto\Sampler;

if(empty($argv[1])){
    die("usage: cmd <dir>\n");
}

$dir = $argv[1]."/";
$rmx_d = $dir . "rmx/";
$cmd = $argv[2] ?? '';

switch($cmd){

    case 'save' :
        passthru("php rmxsave.php $dir");

    break;

    default:

        $loops = glob(__DIR__."/$dir/"."*_{drm,sym}.wav",GLOB_BRACE);

        $size = mt_rand(70,100) / 10;

        function getLoop(){
            global $loops, $size;
            $i = array_rand($loops);
            if(is_string($loops[$i])){
                $src = $loops[$i];
                $loops[$i] = (new Sampler($loops[$i]))->copy(0,'1/4')->resize($size)->split(8);
                foreach($loops[$i] as $s){
                    $s->src = basename($src);
                }
            }
            return $loops[$i];
        }

        $remix = Sampler::silence(0);
        $src = [];
        for($i=0;$i<4;$i++){
            echo "At $i\n";
            $loop = getLoop();
            $loop = $loop[array_rand($loop)];
            $src[] = $loop->src;
            $loop = $loop();
            if(mt_rand(0,1)){
                $loop->mod('reverse');
            }
            if(mt_rand(0,1)){
        //        $loop->chop(mt_rand(1,4));
            }
            $remix->add($loop);
        }
        $remix->maxgain();
        $remix->x(4);

        if(!is_dir($rmx_d)){
            mkdir($rmx_d,0777);
        }

        $remix->save("$rmx_d/tmp.wav");
        file_put_contents("$rmx_d/tmp.json", json_encode($src));
        $remix->play();






    break;
}
