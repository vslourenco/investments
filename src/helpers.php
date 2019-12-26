<?php

if (!function_exists('dd')) {
    function dd()
    {	
    	echo "<pre>";
        array_map(function($x) { 
            dump($x); 
        }, func_get_args());
        die;
    }
 }

 if (!function_exists('dump')) {
    /**
     *  Dump data
     */
    function dump()
    {
        array_map(function ($params) {
            var_dump($params);
        }, func_get_args());
        exit;
    }
}

?>