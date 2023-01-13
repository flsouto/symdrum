<?php

if(empty($argv[1])){
    die("usage: cmd <dir>\n");
}

$dir = $argv[1]."/";

$grouped = [];
foreach(glob($dir."*.wav") as $f){
    $base = basename($f);
    $index = explode('_', $base)[0];
    if(ctype_digit($index)){
        $grouped[$index][] = $base;
    }
}
$updir = __DIR__."/$dir/upload/";
if(!is_dir($updir)){
    mkdir($updir,0777);
}

$current = glob($updir."*.wav");
$next = '';
if(empty($current)){
    $next = "01_sym.wav";
} else if(strstr($current[0],'rmx')) {
    preg_match("/rmx(\d\d)/",$current[0], $m);
    $i = str_pad( ((int)$m[1]) + 1, 2, '0', STR_PAD_LEFT);
    $next = "rmx/{$i}.wav";
    $save_as = "rmx_{$i}.wav";
} else {
    $base = basename($current[0]);
    $index = explode("_",$base)[0];
    switch($base){
        case "{$index}_sym.wav" :
            $next = "{$index}_drm_sym.wav";
        break;
        case "{$index}_drm_sym.wav" :
            $next = "{$index}_drm.wav";
        break;
        case "{$index}_drm.wav" :
            $next = str_pad($index+1, 2, '0', STR_PAD_LEFT)."_sym.wav";
            if(!file_exists($dir.$next) && file_exists($dir."rmx/01.wav")){
                $next = 'rmx/01.wav';
                $save_as = 'rmx_01.wav';
            }
        break;
    }
}

if(!file_exists($dir.$next)){
    die("No more files to upload\n");
}

$save_as = $save_as ?? $next;

copy($dir.$next, $updir.$save_as);

if($current[0]??null){
    unlink($current[0]);
}

shell_exec("echo '$save_as' | xclip -sel clip > /dev/null");

echo "Copied '$save_as'\n";
