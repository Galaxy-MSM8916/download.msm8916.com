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

function get_script_base_url()
{
    return $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["SCRIPT_NAME"]);
}

// memoization for the indentation
$ind = array();

function indent($num = 1)
{
    global $ind;

    if ($ind[$num])
        return $ind[$num];

    $ret = "";

    for ($i = 0; $i < $num; $i++)
        $ret = $ret . "    ";

    $ind[$num] = $ret;

    return $ret;
}

?>