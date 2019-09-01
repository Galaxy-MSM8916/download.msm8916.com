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
        if ($constraint == null)
        {
            $constraint = array(
                "date" => $_GET["date"],
                "device" => $_GET["device"],
                "dist" => $_GET["dist"],
                "downloads" => $_GET["downloads"],
                "version" => $_GET["version"]
            );
        }

        $group = $_GET["groupBy"];

        if ($group == null)
            $group = "device";

        $maps = parse_github_releases();

        $relGroup = $maps[$group];

        $keys = array_keys($relGroup);

        if ($_GET["sort"] == "asc")
            asort($keys);
        elseif ($_GET["sort"] == "desc")
            arsort($keys);

        echo indent(2) . "<script type='text/javascript'>document.getElementById('sort_group_div').hidden = false</script>\n";

        echo indent(2) . "<div id = 'build_div' class = 'div'>\n";

        $headings = array(
            "Build",
            "dist" => "Distribution",
            "version" => "Version",
            "device" => "Device",
            "date" => "Date",
            "downloads" => "Downloads",
            "",
        );

        foreach($keys as $key)
        {
            $releases = filter_releases($relGroup[$key], $constraint);

            if (count($releases) == 0)
                continue;

            $q = build_query_from_get(array($group => $key));

            echo indent(3) . "<h2>" . get_link($q, $key) . "</h2>" . PHP_EOL;
            echo indent(3) . "<table class = 'build_folder'>\n";
            echo indent(4) . "<tr class = 'header_tr'>\n";

            foreach(array_keys($headings) as $hkey)
            {
                $value = $headings[$hkey];

                if (is_int($hkey) || $group != $hkey)
                    echo indent(5) . "<th>${value}</th>\n";
            }

            echo indent(4) . "</tr>\n";

            foreach($releases as $release)
            {

                $cells = array(
                    $release->getBuildNum(),
                    "dist" => $release->getLongDist(),
                    "version" => $release->getVersion(),
                    "device" => $release->getDevice(),
                    "date" => $release->getDate(),
                    $release->getDownloads(),
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

                $tag_link = get_link(htmlspecialchars("?view=downloads&tag={$release->tag}"), "View");
                echo indent(5) . "<td class='build_dl_link'>" . $tag_link . "</td>\n";

                echo indent(4) . "</tr>\n";
            }
            echo indent(3) . "</table>\n";
        }
        echo indent(2) . "</div>\n";
    }

    function list_release_artifacts($tag)
    {
        // get and parse tags
        $maps = parse_github_releases();

        $release = $maps["tag"][$tag][0];

        $distLong = $release->getLongDist();
        $version = $release->getVersion();
        $device = $release->getDevice();
        $date = $release->getDate();
        $build = $release->getBuildNum();
        $deviceLong = $release->getLongDeviceName();
        $model = $release->getDeviceModel();

        $github_org_url = $GLOBALS['cfg']['github_org_url'];

        $device_tree_url = "${github_org_url}/android_device_samsung_${device}";
        $kernel_tree_url = "${github_org_url}/android_kernel_samsung_msm8916";
        $release_url = "${github_org_url}/releases/releases/tag/$tag";

        echo <<<EOF
        <div id="release">
            <h2>${distLong} ${version} for the ${deviceLong}</h2>
            <hr />
            <h3>Info:</h3>
            <div id="release_info">
                <p>Device Codename: <span>${device}</span></p>
                <p>Device Model: <span>${model}</span></p>
                <p>Build Date: <span>$date</span></p>
                <p>Build Number: <span>$build</span></p>
            </div>
            <hr />
            <h3>Artifacts:</h3>
            <div id="release_links">
EOF;

        $MiB = 1024 * 1024;
        $KiB = 1024;

        foreach($release->getArtifacts() as $artifact)
        {
            $name = $artifact->getName();
            $size = $artifact->getSize();
            $download_count = $artifact->getDownloadCount();
            $download_url = $artifact->getDownloadUrl();
            $description = $artifact->getDescription();

            if ($size > $MiB)
                $sizeTxt = round($size / $MiB, 2) . " MiB";
            elseif ($size > $KiB)
                $sizeTxt = round($size / $KiB, 2) . " KiB";
            else
                $sizeTxt = $size . " bytes";

            echo <<<ARTIFACT
                <div class="artifact_info">
                    <a href='${download_url}' title="Download ${description}">
                        <p>Name: <span>${name}</span></p>
                        <p>File Type: <span>${description}</span></p>
                        <p>File Size: <span>${sizeTxt}</span></p>
                        <p>Download Count: <span>${download_count}</span></p>
                    </a>
                    <hr />
                </div>
ARTIFACT;
        }

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
        // get and parse tags
        $maps = parse_github_releases();

        $prefix_len = strlen($_SERVER["CONTEXT_PREFIX"]);

        $old_url = substr($_SERVER["REDIRECT_URL"], $prefix_len);

        $split_url = explode("/", $old_url);

        $constraint = array();

        foreach ($split_url as $substr)
        {
            if (array_key_exists($new = str_replace("_", " ", $substr), $maps["dist"]))
                {
                    $constraint["dist"] = $new;
                    continue;
                }
            elseif (array_key_exists($substr, $maps["version"]))
                {
                    if($constraint["dist"])
                    {
                        $constraint["version"] = $substr;
                        continue;
                    }
                    else
                    {
                        $constraint = null;
                        break;
                    }
                }
            elseif (array_key_exists($substr, $maps["device"]))
                {
                    if($constraint["version"])
                    {
                        $constraint["device"] = $substr;
                        continue;
                    }
                    else
                    {
                        $constraint = null;
                        break;
                    }
                }
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
        switch($case = $_GET["view"])
        {
            case "downloads":
            {
                if (null != ($tag = $_GET["tag"]))
                    list_release_artifacts($tag);
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
                if (strlen($_SERVER["REDIRECT_URL"]) > 0)
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