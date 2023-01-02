<?php
use FlSouto\Sampler;
$config = require(__DIR__."/config.php");
require_once($config['smp_path']);
require_once(__DIR__."/Text.php");

if(empty($argv[1])){
    die("Usage: cmd <dir>\n");
}

$dir = $argv[1]."/";

$grouped = [];
foreach(glob($dir."*.wav") as $f){
    $base = basename($f);
    $index = explode('_', $base)[0];
    if(ctype_digit($index)){
        $grouped[$index] = 1;
    }
}

$tracks = [];
foreach(array_keys($grouped) as $idx){
    $tracks[] = $dir."{$idx}_sym.wav";
    $tracks[] = $dir."{$idx}_drm_sym.wav";
    $tracks[] = $dir."{$idx}_drm.wav";
}

$tracklist = [];

$album = null;
foreach($tracks as $f){
    $track = new Sampler($f);
    $name = explode('-',basename($f));
    if($album){
        $offset = trim(ltrim(shell_exec("soxi -d '$album->file'"),'00:'));
        $album->add($track);
    } else {
        $offset = "00:00";
        $album = $track();
    }
    $offset = preg_replace("/\.\d+$/","",$offset);
    if(ctype_digit($offset)){
        $offset = "00:$offset";
    }
    if(substr($offset,1,1)==':'){
        $offset = "0$offset";
    }
    $line = "$offset - ".basename($f);
    $tracklist[] = $line;
    echo $line."\n";
}

$tags = [
    "industrial","rhythmic noise", "ambient","experimental","soundtrack","power noise", "beats". "drums","symphonic",
    "samples", "loops", "wav", "pack", "download"
];
shuffle($tags);

$hashtags = ["#looppack","#samplepack","#audioproduction"];
shuffle($hashtags);

$dlhere = pick("{Download|Get} {everything|{the full|this} [{loop|audio|sample}] pack|each [individual] {file|sample}|all [individual] {files|samples|loops}} {here|from}:");

$desc = implode(" ",$hashtags)."\n";
$desc.= "$dlhere {bandcamp_ul}\n";
$desc.= "Tracklist:\n";
$desc.= implode("\n", $tracklist)."\n\n";
$desc.= "Tags: ".implode(", ", $tags);

file_put_contents($dir."desc.txt", $desc);

