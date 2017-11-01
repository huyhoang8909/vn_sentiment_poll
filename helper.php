<?php
/**
* Get all values from a multidimensional array
*
* @param $arr array
* @return null|string|array
*/
function array_value_recursive(array $arr){
    $val = array();
    array_walk_recursive($arr, function($v) use(&$val){
      array_push($val, $v);
    });
    return $val;
}
