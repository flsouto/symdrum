<?php

class Stop extends Exception{}

class StrIterator{
    var $i = 0;
    var $len = 0;
    var $str;
    function __construct($str){
        $this->str = $str;
        $this->len = strlen($str);
    }
    function next(){
        if($this->i >= $this->len) return false;
        return substr($this->str, $this->i++, 1);
    }
    function map(callable $cb){
        $acc = '';
        while(($c = $this->next()) !== false){
            try{
                $acc .= $cb($c);
            } catch(Stop $e){
                break;
            }
        }
        return $acc;
    }
}

function parse(StrIterator $str){
    $result = $str->map(function($c) use($str){
        if($c == '{'){
            return parse_pick($str);
        } else if($c == '[') {
            return parse_maybe($str);
        } else {
            return $c;
        }
    });
    $result = preg_replace('/\s+/',' ',$result);
    $result = preg_replace('/(\w)\s:/','$1:',$result);
    $result = trim($result);
    $result = preg_replace('/ an ([bcdfgjklmnpqrstvxz])/i',' a \\1', $result);
    return $result;
}

const SEPARATOR = '__SEPARATOR__';

function parse_pick(StrIterator $str){
    $level = 1;
    $result = $str->map(function($c) use(&$level){
        if($level == 1){
            if($c == '}') throw new Stop();
            if($c == '|') return SEPARATOR;
        }
        if($c == '{') $level++;
        else if($c == '}') $level--;
        return $c;
    });
    $parts = explode(SEPARATOR, $result);
    return parse(new StrIterator($parts[array_rand($parts)]));
}

function parse_maybe(StrIterator $str){
    $level = 1;
    $result = $str->map(function($c) use(&$level){
        if($level == 1){
            if($c == ']') throw new Stop();
        }
        if($c == '[') $level++;
        else if($c == ']') $level--;
        return $c;
    });
    return mt_rand(0,1) ? parse(new StrIterator($result)) : '';
}


function pick(...$args){
    $tpl = $args[array_rand($args)];
    return parse(new StrIterator($tpl));
}
