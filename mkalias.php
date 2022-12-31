<?php

foreach(glob("*.{php,js,sh}",GLOB_BRACE) as $f){
    $base = basename($f);
    if(in_array($base,['config.php'])) continue;
    $parts = explode(".",$base);
    if($parts[1]=='sh'){
        echo "alias $parts[0]='./$base'\n";
    } else {
        $cmd = $parts[1] == 'php' ? 'php' : 'node';
        echo "alias $parts[0]='$cmd $base'\n";
    }
}
