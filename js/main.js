function get_param($key)
{
    $value = null;

    $arr = document.URL.split("?");

    if ($arr.length < 2)
        return $value;

    $query = $arr[1];

    $params = new URLSearchParams($query);

    $value = $params.get($key);

    return $value;
}

function set_title()
{
    var $title = document.getElementsByTagName("title")[0].innerHTML;
    document.getElementsByTagName("h1")[0].innerHTML = $title;
}

set_title()