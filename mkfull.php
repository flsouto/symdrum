<?php

if(!isset($argv[2])){
    die("Usage: cmd <dir> <index>\n");
}

$dir = $argv[1];
$index = $argv[2];

passthru("php mkosc.php $dir $index drm");
passthru("php mkosc.php $dir $index sym");
passthru("node mkframes.js $dir $index");
passthru("php mkvid.php $dir $index");

