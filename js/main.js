function set_title()
{
    var $title = document.getElementsByTagName("title")[0].innerHTML;
    document.getElementsByTagName("h1")[0].innerHTML = $title;
}

set_title()