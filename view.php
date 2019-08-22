<?php namespace download\view;

    include "releases.php";

    function get_stylesheet()
    {
        $style = "css/style.css";
        return $style;
    }

    function print_stylesheet()
    {
        $style = get_stylesheet();
        echo "<link rel='stylesheet' href='$style'>";
    }

    function print_header()
    {

        //echo "<hr />\n";
    }

    function print_body()
    {

        //echo "<h1>Title</h1>\n";
        //echo "<script src='js/headers.js' type='text/javascript'></script>\n";
        //echo "<hr />\n";
    }

    function print_footer()
    {

    } 

    /* 
        echo $_SERVER['QUERY_STRING'] . PHP_EOL;

        ["QUERY_STRING"]=>
        string(27) "view=downloads&groupBy=date"
        ["REQUEST_URI"]=>
        string(61) "/~vincent/html/download/index.php?view=downloads&groupBy=date"
        ["SCRIPT_NAME"]=>
        string(33) "/~vincent/html/download/index.php"
        ["PHP_SELF"]=>
        string(33) "/~vincent/html/download/index.php"
    */
    function format_release_url()
    {

    }

    // memoization for the indentation
    $ind = array();

    function indent($num = 1)
    {
        if ($ind[$num])
            return $ind[$num];

        $ret = "";

        for ($i = 0; $i < $num; $i++)
            $ret = $ret . "    ";

        $ind[$num] = $ret;

        return $ret;
    }

    function print_releases($groupBy, $relGroup, $constraint)
    {
        $group = $_GET["groupBy"];

        if ($group == null)
            $group = "device";


        echo indent(2) . "<div id = 'build_div' class = 'div'>\n";

        //TODO: Make selector/navbar for choosing group sort selection

        //foreach($relGroup as $releases)
        foreach(array_keys($relGroup) as $key)
        {
            $releases = \download\releases\filter_releases($relGroup[$key], $constraint);

            if (count($releases) == 0)
                continue;

            //echo "<a href=\"?groupBy=${group}&${group}=${key}\"> <b>${key}</b></a>";
            if ($_GET[$group] == null)
                echo indent(3) . "<h2><a href='?". $_SERVER["QUERY_STRING"] . "&amp;${group}=${key}'><b>${key}</b></a></h2>\n";
            else
                echo indent(3) ."<h2><a href='?". $_SERVER["QUERY_STRING"] . "'><b>${key}</b></a></h2>\n";

            echo indent(3) . "<table class = 'build_folder'>\n";

            echo indent(4) . "<tr class = 'header_tr'>\n";

            echo indent(5) . "<th>Distribution</th>\n";

            echo indent(5) . "<th>Version</th>\n";

            echo indent(5) . "<th>Number</th>\n";

            echo indent(5) . "<th>Device</th>\n";

            echo indent(5) . "<th>Date</th>\n";

            echo indent(4) . "</tr>\n";

            foreach($releases as $release)
            {

                $tag = $release->tag;
                
                $a_r_url = "<a class = 'release_url' href='?view=" . $_GET["view"] . "&amp;tag=$tag'>";

                echo indent(4) . "<tr class = 'build_tr'>\n";

                echo indent(5) . "<td class='build_dist'>" . $a_r_url . $release->getLongDist() . "</a>" . "</td>\n";

                echo indent(5) . "<td class='build_version'>" . $a_r_url . $release->getVersion() . "</a>" . "</td>\n";

                echo indent(5) . "<td class='build_number'>" . $a_r_url . $release->getBuildNum() . "</a>" . "</td>\n";

                echo indent(5) . "<td class='build_device'>" . $a_r_url . $release->getDevice() . "</a>" . "</td>\n";

                echo indent(5) . "<td class='build_date'>" . $a_r_url . $release->getDate() . "</a>" . "</td>\n";

                echo indent(4) . "</tr>\n";
            }
            echo indent(3) . "</table>\n";
        }
        echo indent(2) . "</div>\n";
    }

    function list_release_artifacts($tag)
    {
        // get and parse tags
        $tags = \download\releases\read_tags();
        $maps = \download\releases\parse_tags($tags);

        echo "Do something with the tag " . $tag . " here..." . PHP_EOL;

    }

    function list_releases()
    {
        
        // get and parse tags
        $tags = \download\releases\read_tags();
        $maps = \download\releases\parse_tags($tags);

        $constraint = array(
            "date" => $_GET["date"],
            "device" => $_GET["device"],
            "dist" => $_GET["dist"],
            "version" => $_GET["version"]
        );

        if (null !== ($case = $_GET["groupBy"]))
        {
            print_releases($case, $maps[$case], $constraint);
        }
        else
        {
            $case = "device";
            print_releases($case, $maps[$case], $constraint);
        }
        
        //var_dump(filter_releases($maps["device"], $constraint));
        //echo "</pre>\n";

        /*
        */
        //echo "<hr />\n";
    }

    function generate_view()
    {
        print_header();
        print_body();
        print_footer();

        switch($case = $_GET["view"])
        {
            case "downloads":
            {
                if (null != ($tag = $_GET["tag"]))
                    list_release_artifacts($tag);
                else
                    list_releases();

                break;
            }
            case "about":
            {
                //TODO: do something
                break;
            }
            case "build_status":
            {
                //TODO: do something
                break;
            }
            default:
            {
                //TODO: default (home) case
                break;
            }
        }
    }

?>