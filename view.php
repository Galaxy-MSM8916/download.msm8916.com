<?php namespace download;

    include "releases.php";

    function get_stylesheet()
    {
        $style = get_script_base_url() . "/css/style.css";
        return $style;
    }

    function print_stylesheet()
    {
        $style = get_stylesheet();
        echo "<link rel='stylesheet' href='$style'>";
    }

    function print_releases($constraint = null)
    {
        /* arrays for validation of query input */
        $valid_group = array(
            "build_date" => 0,
            "codename" => 0,
            "dist_name_short" => 0,
            "build_version" => 0,
        );

        $valid_sort = array(
            "asc" => 0,
            "desc" => 0
        );

        /* construct constraints */
        if ($constraint == null)
        {
            if (isset($_GET["date"]))
                $constraint["build_date"] = $_GET["date"];

            if (isset($_GET["device"]))
                $constraint["codename"] = $_GET["device"];

            if (isset($_GET["dist"]))
                $constraint["dist_name_short"] = $_GET["dist"];

            //if (isset($_GET["downloads"]))
            //    $constraint["dist_name_short"] = $_GET["downloads"];

            if (isset($_GET["version"]))
                $constraint["build_version"] = $_GET["version"];
        }

        $mysqli = connect_to_db();

        $group = "codename";

        if (isset($_GET["groupBy"]))
        {
            if (array_key_exists($_GET["groupBy"], $valid_group))
                $group = $_GET["groupBy"];
        }

        echo indent(2) . "<script type='text/javascript'>document.getElementById('sort_group_div').hidden = false</script>\n";

        echo indent(2) . "<div id = 'build_div' class = 'div'>\n";

        $headings = array(
            "Build",
            "dist" => "Distribution",
            "version" => "Version",
            "device" => "Device",
            "date" => "Date",
            //"downloads" => "Downloads",
            "",
        );

        /* construct query for use in grouping builds */
        $group_query = "SELECT DISTINCT $group FROM dist_device_builds";

        if (isset($_GET["sort"]) && array_key_exists($_GET["sort"], $valid_sort))
            $group_query .= " ORDER BY $group {$_GET['sort']};";

        /* construct sql query for build listings */
        $build_query = "SELECT * FROM dist_device_builds WHERE";

        if (!test_array_values($constraint))
        {
            $i = 0;
            $keys = array_keys($constraint);

            while($i < count($keys))
            {
                $key = $keys[$i];
                $value = $mysqli->escape_string($constraint[$key]);

                $build_query .= " $key='$value' AND";

                $i = $i + 1;
            }
        }

        // run group_query and get result
        if (false == ($gq_result = $mysqli->query($group_query)))
        {
            printf("Failed to query database: %s\n", $mysqli->error);

            $mysqli->close();
            return $gq_result;
        }

        while(null !== ($gq_row = $gq_result->fetch_assoc()))
        {
            $row_result = $gq_row[$group];

            // finish contructing build_query
            $final_query = $build_query .
                " $group='$row_result';";

            // get device info
            if (false == ($result = $mysqli->query($final_query)))
            {
                printf("Failed to query database: %s\n", $mysqli->error);

                $mysqli->close();
                return $result;
            }

            /* print heading and table header row*/
            if ($result->num_rows > 0)
            {
                $q = build_query_from_get(array($group => $row_result));

                echo indent(3) . "<h2>" . get_link($q, $row_result) . "</h2>" . PHP_EOL;
                echo indent(3) . "<table class = 'build_folder'>\n";
                echo indent(4) . "<tr class = 'header_tr'>\n";

                foreach(array_keys($headings) as $hkey)
                {
                    $value = $headings[$hkey];

                    if (is_int($hkey) || $group != $hkey)
                        echo indent(5) . "<th>${value}</th>\n";
                }

                echo indent(4) . "</tr>\n";
            }

            while (null !== ($build_row = $result->fetch_assoc()))
            {
                $cells = array(
                    $build_row['build_num'],
                    "dist" => $build_row['dist_name_short'],
                    "version" => $build_row['build_version'],
                    "device" => $build_row['codename'],
                    "date" => $build_row['build_date'],
                    //$release->getDownloads(),
                );

                echo indent(4) . "<tr class = 'build_tr'>\n";

                foreach(array_keys($cells) as $ckey)
                {
                    if (is_int($ckey))
                        echo indent(5) . "<td>" . $cells[$ckey] . "</td>\n";
                    elseif ($group != $ckey)
                    {
                        $q = build_query_from_get(array($ckey => $cells[$ckey]));
                        echo indent(5) . "<td>" . get_link($q, $cells[$ckey]) . "</td>\n";
                    }
                }

                $tag_link = get_link(htmlspecialchars("?view="
                    . "downloads&tag={$build_row['build_tag']}"), "View");
                echo indent(5) . "<td class='build_dl_link'>" . $tag_link . "</td>\n";

                echo indent(4) . "</tr>\n";
            }
            echo indent(3) . "</table>\n";
        }
        echo indent(2) . "</div>\n";

        $result->free();
        $mysqli->close();
    }

    function list_release_artifacts($tag)
    {
        $mysqli = connect_to_db();

        // get device info
        $query = "SELECT * FROM dist_device_builds"
            . " WHERE build_tag='" . $mysqli->escape_string($tag) . "';";

        if (false == ($result = $mysqli->query($query)))
        {
            printf("Failed to query database: %s\n", $mysqli->error);

            $mysqli->close();
            return $result;
        }

        if (null == ($build_info = $result->fetch_assoc()))
        {
            printf("Failed to query database: %s\n", $mysqli->error);
            $mysqli->close();
            return false;
        }
        $result->free();

        $github_org_url = $GLOBALS['cfg']['github_org_url'];

        $device_tree_url = "${github_org_url}/android_device_samsung_"
            . $build_info['codename'];

        $kernel_tree_url = "${github_org_url}/android_kernel_samsung_msm8916";
        $release_url = "${github_org_url}/releases/releases/tag/$tag";

        $dist_name_long = $build_info['dist_name_long'];
        $codename = $build_info['codename'];
        $model = $build_info['model'];
        $version = $build_info['build_version'];
        $build_date = $build_info['build_date'];
        $build_num = $build_info['build_num'];
        $build_id = $build_info['build_id'];
        $device_name = $build_info['device_name'];

        echo <<<EOF
        <div id="release">
            <h2>{$dist_name_long} {$version} for the {$device_name}</h2>
            <hr />
            <h3>Info:</h3>
            <div id="release_info">
                <p>Device Codename: <span>{$codename}</span></p>
                <p>Device Model: <span>{$model}</span></p>
                <p>Build Date: <span>{$build_date}</span></p>
                <p>Build Number: <span>{$build_num}</span></p>
            </div>
            <hr />
            <h3>Artifacts:</h3>
            <div id="release_links">
EOF;

        $MiB = 1024 * 1024;
        $KiB = 1024;

        $query = "SELECT * FROM artifacts"
            . " WHERE build_id='{$build_id}';";

        if (false == ($result = $mysqli->query($query)))
        {
            printf("Failed to query database: %s\n", $mysqli->error);
            $mysqli->close();
            return $result;
        }

        while (null !== ($artifact_info = $result->fetch_assoc()))
        {
            $name = $artifact_info['file_name'];
            $size = $artifact_info['file_size'];
            $download_count = $artifact_info['download_count'];
            $download_url = $artifact_info['download_url'];
            $description = $artifact_info['description'];

            if ($size > $MiB)
                $sizeTxt = round($size / $MiB, 2) . " MiB";
            elseif ($size > $KiB)
                $sizeTxt = round($size / $KiB, 2) . " KiB";
            else
                $sizeTxt = $size . " bytes";

            echo <<<ARTIFACT
                <div class="artifact_info">
                    <a href='{$download_url}' title="Download {$description}">
                        <p>Name: <span>{$name}</span></p>
                        <p>File Type: <span>{$description}</span></p>
                        <p>File Size: <span>${sizeTxt}</span></p>
                        <p>Download Count: <span>{$download_count}</span></p>
                    </a>
                    <hr />
                </div>
ARTIFACT;
        }

        $result->free();
        $mysqli->close();

        echo <<<EOF
                <h3>Other Links: </h3>
                <div class="other_links">
                    <p><a href='${device_tree_url}'>Device tree</a></p>
                    <p><a href='${kernel_tree_url}'>Kernel tree</a></p>
                    <p><a href='${release_url}'>View all artifacts/downloads on GitHub</a></p>
                <div>
            </div>
        </div>
EOF;
    }

    function parse_old_download_url()
    {
        $columns = array(
            "dist_name_long",
            "build_version",
            "codename",
            "build_date",
        );

        $prefix_len = strlen(dirname($_SERVER["SCRIPT_NAME"])) + 1;
        $old_url = substr($_SERVER["REDIRECT_URL"], $prefix_len);
        $split_url = explode("/", $old_url);

        $i = 0;

        while ($i < count($split_url))
        {
            if (isset($split_url[$i]) && $split_url[$i])
                $constraint[$columns[$i]] = $split_url[$i];

            $i = $i + 1;
        }

        return $constraint;
    }

    function print_home()
    {
        echo <<<EOF
        <div id="home">
            <h1>Under construction</h1>
            <hr />
            <h2>Links:</h2>
            <div id="home_links">
                <a href="?view=downloads">View build downloads</a>
                <a href="https://jenkins.msm8916.com">Jenkins CI</a>
                <a href="https://review.msm8916.com">Gerrit Code Review</a>
                <a href="{$GLOBALS['cfg']['github_org_url']}">GitHub Page</a>
            </div>
        </div>
EOF;
    }

    function print_404()
    {
        echo <<<EOF
        <h2>Error 404 - Not found</h2>
        <br />
        <h3>The page requested could not be found.</h2>
        <br />
EOF;
    }

    function generate_view()
    {
        parse_github_releases();

        $case = isset($_GET["view"]) ? $_GET["view"] : null;

        switch($case)
        {
            case "downloads":
            {
                if (isset($_GET["tag"]))
                    list_release_artifacts($_GET["tag"]);
                else
                    print_releases();

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
            case "home":
            {
                print_home();
                break;
            }
            default:
            {
                if (isset($_SERVER["REDIRECT_URL"]) &&
                    strlen($_SERVER["REDIRECT_URL"]) > 0)
                {
                    $constraint = parse_old_download_url();

                    if (count($constraint) > 0)
                        print_releases($constraint);
                    else
                        print_404();
                }
                else //default (home) case
                {
                    print_home();
                }

                break;
            }
        }
    }

?>