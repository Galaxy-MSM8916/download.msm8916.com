<?php

function get_link($url, $text)
{
    $s = "<a href=\"$url\" >$text</a>";

    return $s;
}

/* Return true iff all array values are equal to $testValue */
function test_array_values($array, $testValue = null)
{
    if (isset($array))
    {
        foreach($array as $value)
        {
            if ($value !== $testValue)
                return false;
        }
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

function indent($num = 1)
{
    // memoization for the indentation
    static $ind = array();

    if (isset($ind[$num]))
        return $ind[$num];

    $ret = "";

    for ($i = 0; $i < $num; $i++)
        $ret = $ret . "    ";

    $ind[$num] = $ret;

    return $ret;
}

?>