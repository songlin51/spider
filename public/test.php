<?php
    $str = file_get_contents("1.log");
    preg_match("#<div class=\"content-text\">([\s\S]*)</div>#i",$str,$mathc);
var_dump($match);