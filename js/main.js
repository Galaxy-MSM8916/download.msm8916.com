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

function get_link($url, $text, $id=null)
{
    if ($id == null)
        $link = "<a href='" + $url + "'>" + $text + "</a>";
    else
        $link = "<a href='" + $url + "' id='" + $id + "'>" + $text + "</a>";

    return $link;
}

function set_title()
{
    $url = document.URL.split("/");

    $domain = $url[2];

    $view = get_param("view");

    $nav_link = document.getElementById("nav_" + $view).innerHTML;

    $text = document.getElementById("nav_" + $view).innerText

    if ($view == null)
        $title = $domain;
    else
        $title = $domain + " > " + $text;

    document.getElementsByTagName("title")[0].innerHTML = $title.toLowerCase();
    document.getElementById("banner1").innerHTML = get_link($url[1] + "//" + $domain, $domain) + " > " + $nav_link;
}

set_title()