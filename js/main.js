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

function change_nav_class()
{
    $value = get_param("view");

    if ($value == null)
        $value = "home";

    $elem = document.getElementById("nav_" + $value);
    $elem.className = "active";
}

function set_title()
{
    $url = document.URL.split("/");

    //$url2 = document.URL.split("?");

    $domain = $url[2];
    //$base_url = $url2[0];

    $view = get_param("view");

    /*
    document.getElementById("banner1").innerHTML = get_link($base_url, $domain);
    */

    if ($view == null)
        $title = $domain;
    else
    {
        $text = document.getElementById("nav_" + $view).innerText
        $title = $domain + " > " + $text;
        /*
        $nav_link = document.getElementById("nav_" + $view).innerHTML;
        document.getElementById("banner1").innerHTML = get_link($base_url, $domain) + " > " + $nav_link;
        */
    }
    document.getElementsByTagName("title")[0].innerHTML = $title.toLowerCase();
}

change_nav_class();
set_title()