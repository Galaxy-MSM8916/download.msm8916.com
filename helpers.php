<?php

function get_link_elem($url, $text)
{
    $s = "<a href=\"$url\" >$text</a>\n";

    return $s;
}

function add_value_to_2d_arr(&$arr, $key, &$value)
{
    // add value to 2d array. does not check for
    // dups in 2nd level array.
    if ($arr[$key] == null)
    {
        $arr[$key] = array();
    }

    $arr[$key][] = $value;
}

?>