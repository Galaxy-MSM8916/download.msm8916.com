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

    function print_releases()
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
        foreach(array_keys($valid_group) as $key)
        {
        if (isset($_GET[$key]))
            $constraint[$key] = $_GET[$key];
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
            "dist_name_short" => "Distribution",
            "build_version" => "Version",
            "codename" => "Device",
            "build_date" => "Date",
            "downloads" => "Downloads",
            "",
        );

        /* construct query for use in grouping builds */
        $group_query = "SELECT DISTINCT $group FROM dist_device_builds";

        if (isset($_GET["sort"]) && array_key_exists($_GET["sort"], $valid_sort))
            $group_query .= " ORDER BY $group {$_GET['sort']};";

        /* construct sql query for build listings */
        $build_query = "SELECT * FROM dist_device_builds WHERE";

        if (isset($constraint) && !test_array_values($constraint))
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

        $total_rows = 0;

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
                $total_rows = $total_rows + $result->num_rows;

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
                $dl_query = "SELECT MAX(download_count) as count FROM artifact"
                  . " JOIN build ON artifact.build_id=build.build_id"
                  . " WHERE build.build_id={$build_row['build_id']};";

                if (false !== ($dlq_result = $mysqli->query($dl_query)))
                {
                    if (null !== ($dl_count_row = $dlq_result->fetch_assoc()))
                    {
                        $download_count = $dl_count_row['count'];
                    }
                    $dlq_result->free();
                }

                $cells = array(
                    $build_row['build_num'],
                    "dist_name_short" => $build_row['dist_name_short'],
                    "build_version" => $build_row['build_version'],
                    "codename" => $build_row['codename'],
                    "build_date" => $build_row['build_date'],
                    $download_count,
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
            if ($result->num_rows > 0)
                echo indent(3) . "</table>\n";
        }

        if ($total_rows == 0)
        {
            http_response_code(404);
            echo indent(3) . "<h3>No matching builds found</h3>\n";
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
            echo "<h3>Could not find build information for tag <i>$tag</i></h3>\n";
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
        $board_name = $build_info['board_name'];
        $board_arch = $build_info['board_arch'];

        if (null !== ($extra = $build_info['codename_model_extra']))
        {
            $device_model_map = json_decode($extra, true);

            $keys = array_keys($device_model_map);

            $i = 0;

            $codename_extra = "";
            $model_extra = "";

            while ($i < count($keys) - 1)
            {
                $key = $keys[$i];
                $value = $device_model_map[$key];

                $codename_extra .= "$key / ";
                if (isset($value) && $value)
                    $model_extra .= "$value / ";

                $i = $i + 1;
            }
            $key = $keys[$i];
            $value = $device_model_map[$key];

            $codename_extra .= "$key";
            if (isset($value) && $value)
                $model_extra .= "$value";

        }

        echo <<<EOF
        <div id="release">
            <h2>{$dist_name_long} {$version} for the {$device_name} ($model)</h2>
            <hr />
            <h3>Info:</h3>
            <div id="release_info">
EOF;
        if ($build_info['unified'])
        {
            echo <<<UNIFIED
                <p>Unified codename: <span>{$codename}</span></p>
                <p>Other codenames: <span>{$codename_extra}</span></p>
                <p>Supported models: <span>{$model_extra}</span></p>
UNIFIED;
        }
        elseif ($extra)
        {
            $codename_extra = $codename . " / " . $codename_extra;
            echo <<<ALIAS
                <p>Codenames: <span>{$codename_extra}</span></p>
                <p>Device Model: <span>{$model}</span></p>
ALIAS;
        }
        // don't show for dummy devices
        elseif ($build_info['variant_id'] > 2)
        {
        echo <<<ELSE
                <p>Device Codename: <span>{$codename}</span></p>
                <p>Device Model: <span>{$model}</span></p>
ELSE;
        }
        echo <<<DATE
                <p>Build Date: <span>{$build_date}</span></p>
DATE;
        if ($build_num > 0)
            echo <<<BUILD_NUM
                <p>Build Number: <span>{$build_num}</span></p>
BUILD_NUM;
        echo <<<BOARD
                <p>Board name: <span>{$board_name}</span></p>
                <p>Board arch: <span>{$board_arch}</span></p>
BOARD;
        echo <<<EOF
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

        echo <<<BEGIN
                <h3>Other Links: </h3>
                <div class="other_links">
BEGIN;
        // don't show for dummy devices
        if ($build_info['variant_id'] > 2)
            echo <<<DTREE
                    <p><a href='${device_tree_url}'>Device tree</a></p>
DTREE;
        echo <<<EOF
                    <p><a href='${kernel_tree_url}'>Kernel tree</a></p>
                    <p><a href='${release_url}'>View all artifacts/downloads on GitHub</a></p>
                <div>
            </div>
        </div>
EOF;
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
            default:
            {
                if ($case)
                {
                    print_404();
                    break;
                }
            }
            case "home":
            {
                print_home();
                break;
            }
        }
    }

?>