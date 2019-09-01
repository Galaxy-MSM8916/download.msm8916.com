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

/* Return true iff all array values are equal to $testValue */
function test_array_values($array, $testValue = null)
{
    foreach($array as $value)
    {
        if ($value !== $testValue)
            return false;
    }
    return true;
}

function build_query_from_get($queries = null)
{
    if ($queries !== null)
        $query_data = array_merge($_GET, $queries);
    else
        $query_data = $_GET;

    return '?' . htmlspecialchars(http_build_query($query_data));
}

?>