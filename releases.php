<?php namespace download;

    include "config.php";
    include "helpers.php";

    include "db.php";

    function get_release_info($tag)
    {
        $info = null;

        $mysqli = connect_to_db();

        $query = "SELECT * FROM dists;";

        if (null == ($result = $mysqli->query($query)))
        {
            $mysqli->close();
            printf("Failed to get row: %s\n", $mysqli->error);
            return $info;
        }

        while (null !== ($row = $result->fetch_assoc()))
        {
            $n = strlen($row['tag_prefix']);

            if (strncasecmp($row['tag_prefix'], $tag, $n) == 0)
            {
                if ($row['replace_uscore'] == true)
                    $tokens = explode("-", str_replace("_", "-", $tag));
                else
                    $tokens = explode("-", $tag);

                $info['dist_id'] = $row['dist_id'];
            
                if ($row['date_offset'] >= 0)
                    $info['date'] = $tokens[$row['date_offset']];

                if ($row['device_offset'] >= 0)
                    $info['device'] = $tokens[$row['device_offset']];

                if ($row['build_offset'] >= 0)
                {
                    $info['build'] = $tokens[$row['build_offset']];

                    if ($info['build'][0] == 'j')
                        $info['build'] = substr($info['build'], 1);
                }

                if ($row['version_offset'] >= 0)
                    $info['version'] = $tokens[$row['version_offset']];

                if ($row['channel_offset'] >= 0)
                    $info['channel'] = $tokens[$row['channel_offset']];

                //if ($row['extra_offset'] >= 0)
                //    $info['extra'] = $tokens[$row['extra_offset']];

                break;
            }
        }

        $result->free();

        if ($info == null)
        {
            printf("Failed to get dist info for tag %s\n", $tag);
            $mysqli->close();
            return $info;
        }

        if ($row['device_offset'] >= 0)
        {
            $query = "SELECT variant_id FROM variant WHERE codename='"
                        . $info['device'] . "';";

            if (false == ($result = $mysqli->query($query)))
            {
                printf("Failed to get variant_id: %s\n", $mysqli->error);
                $mysqli->close();
                return null;
            }

            if (null !== ($row = $result->fetch_assoc()))
                $info['variant_id'] = $row['variant_id'];

            $result->free();
        }
        /*
        else // set database default value
            $info['variant_id'] = 1;
        */

        $mysqli->close();
        return $info;
    }

   function filter_releases($releases, $constraint)
    {
        if (test_array_values($constraint))
            return $releases;

        $ret = array();

        foreach($releases as $release)
        {
            $date = $release->getDate();
            $version = $release->getVersion();
            $device = $release->getDevice();
            $dist = $release->getLongDist();
            $downloads = $release->getDownloads();

            if ($constraint["date"] && $date != $constraint["date"])
                continue;
            if ($constraint["version"] && $version != $constraint["version"])
                continue;
            if ($constraint["device"] && $device != $constraint["device"])
                continue;
            if ($constraint["dist"] && $dist != $constraint["dist"])
                continue;
            if ($constraint["downloads"] && $downloads != $constraint["downloads"])
                continue;

            $ret[] = $release;
        }
        return $ret;
    }

    function getSslPage($url) {

        $userAgent = "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0";
        $timeout = 2;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /* fetch json-formatted releases from a url, by default github api */
    function fetch_releases_from_remote($request_url, $out_dir, $compress)
    {
        $count = 0;

        $pages = array();

        if ($out_dir && (is_dir($out_dir) == false))
            mkdir($out_dir, 0755, true);

        do
        {
            $count = $count + 1;
            // read file into string
            $request_data = getSslPage($request_url . $count);

            $handle = false;

            if ($out_dir && is_dir($out_dir))
            {
                if ($compress)
                    $fpath = "compress.zlib://" . $out_dir . "/" . $count . ".gz";
                else
                    $fpath = $out_dir . "/" . $count;

                $handle = fopen($fpath, "w");
            }

            // TODO: possibly log write info to a log file
            if ($handle !== false)
            {
                fwrite($handle, $request_data);
                fclose($handle);
            }

            // decode the json string
            $pages[$count] = json_decode($request_data, true);

        }
        while (count($pages[$count]) > 0);

        return $pages;

    }

    function fetch_releases_from_dir($path)
    {
        $pages = array();

        if (is_dir($path))
            $dh = opendir($path);
        else
            return $pages;

        while ($filename = readdir($dh))
        {
            $fpath = $path . "/" . $filename;

            if (is_dir($fpath))
                continue;

            $request_data = file_get_contents("compress.zlib://" . $fpath);
            $pages[$filename] = json_decode($request_data, true);
        }
        return $pages;
    }

    function insert_release($release_info, $tag)
    {

        $ver = $release_info['version'];
        $build_num = $release_info['build'];
        $dist_id = $release_info['dist_id'];
        $codename = $release_info['device'];
        $variant_id = $release_info['variant_id'];
        //$extra = $release_info['extra']; - unused

        if (isset($release_info['date']))
            $date = $release_info['date'];
        else
            $date = $release_info['upload_date'];

        if (isset($release_info['channel']))
            $channel = $release_info['channel'];

        $mysqli = connect_to_db();

        // check if build exists in database already
        $query = "SELECT build_id FROM build WHERE build_tag='$tag';";

        $result = $mysqli->query($query);

        // if we found a match, update the record
        if ($result && $result->num_rows > 0)
        {
            $query = "UPDATE build SET last_update=CURRENT_TIMESTAMP"
                    . " WHERE build_tag='$tag';";

            if (false == ($result = $mysqli->query($query)))
                printf("Failed to update timestamp: %s\n", $mysqli->error);

            $mysqli->close();
            return $result;
        }

        $result->free();

        if (isset($channel))
        { // full roms
            $query = "INSERT INTO build"
                . "(build_tag, build_date, build_version,"
                . " build_channel, build_num, variant_id, dist_id)"
                . " values ('$tag', '$date', '$ver', '$channel', $build_num,"
                ." $variant_id, $dist_id);";
        }
        elseif (isset($variant_id))
        { //recovery/boot image
            $query = "INSERT INTO build"
                . "(build_tag, build_date, build_version,"
                . " build_num, variant_id, dist_id)"
                . " values ('$tag', '$date', '$ver', $build_num,"
                ." $variant_id, $dist_id);";
        }
        else
        { // everything else
            $query = "INSERT INTO build"
                . "(build_tag, build_date, dist_id)"
                . " values ('$tag', '$date', $dist_id);";
        }

        if (false == ($result = $mysqli->query($query)))
            printf("Failed to insert build: %s\n", $mysqli->error);

        $mysqli->close();
        return $result;
    }

    function insert_artifact($raw_artifact, $tag)
    {
        $name = $raw_artifact["name"];
        $size = $raw_artifact["size"];
        $download_count = $raw_artifact["download_count"];
        $download_url = $raw_artifact["browser_download_url"];

        $mysqli = connect_to_db();

        // check if artifact is already there, and update if so
        $query = "SELECT * FROM artifact WHERE file_name='$name'"
            . " AND file_size='$size';";

        if (false !== ($result = $mysqli->query($query)))
        {
            if (null !== ($row = $result->fetch_assoc()))
            {
                $result->free();
                if ($download_count > $row['download_count'])
                {
                    $query = "UPDATE artifact SET "
                        . "download_count=$download_count WHERE "
                        . "file_name='$name' AND file_size='$size';";

                    if (false == ($result = $mysqli->query($query)))
                        printf("Failed to update artifact: %s\n", $mysqli->error);
                }
                $mysqli->close();
                return $result;
            }
            $result->free();
        }
        else
        {
            printf("Failed to query database: %s\n", $mysqli->error);
            $mysqli->close();
            return $result;
        }

        // get file extension and type id
        $tokens = explode(".", $name);
        $extension = $tokens[count($tokens) - 1];

        $query = "SELECT type_id FROM artifact_type WHERE extension='$extension';";

        if (false !== ($result = $mysqli->query($query))
            && (null !== ($row = $result->fetch_assoc())))
        {
            $type_id = $row['type_id'];
            $result->free();
        }

        // get the build_id for the tag
        $query = "SELECT build_id FROM build WHERE build_tag='$tag';";

        if (false !== ($result = $mysqli->query($query))
            && (null !== ($row = $result->fetch_assoc())))
        {
            $build_id = $row['build_id'];
            $result->free();
        }
        else
        {
            printf("Failed to find build_id for $tag: %s\n", $mysqli->error);
            $mysqli->close();
            return false;
        }

        // insert the artifact info into table
        if (isset($type_id))
        {
            $query = "INSERT INTO artifact (file_name, file_size, build_id,"
                . " type_id, download_count, download_url) values "
                . "('$name', $size, $build_id, $type_id, $download_count, '$download_url');";
        }
        else
        {
            $query = "INSERT INTO artifact (file_name, file_size, build_id,"
                . " download_count, download_url) values "
                . "('$name', $size, $build_id, $download_count, '$download_url');";
        }

        if (false == ($result = $mysqli->query($query)))
            printf("Failed to insert artifact: %s\n", $mysqli->error);

        $mysqli->close();
        return $result;
    }

    function parse_decoded_releases(&$decoded_json)
    {
        foreach ($decoded_json as $page)
        {
            foreach ($page as $raw_release)
            {
                $tag = $raw_release["tag_name"];

                // skip unmatched releases
                if(null == ($rel = get_release_info($tag)))
                    continue;

                // extract date
                $rel['upload_date'] = substr($raw_release['published_at'], 0, 10);

                // don't add artifacts if build couldn't be added
                if (false == insert_release($rel, $tag))
                    continue;

                foreach($raw_release["assets"] as $raw_asset)
                    insert_artifact($raw_asset, $tag);
            }
        }
    }


    function parse_github_releases()
    {
        $query = "SELECT MAX(last_update) as last_update FROM build;";

        $mysqli = connect_to_db();
        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $result->free();

            if ($row['last_update'] !== null)
            {
                $current_time = new \DateTime();
                $update_time = new \DateTime($row['last_update']);
                $diff = $current_time->getTimestamp() - $update_time->getTimestamp();

                // don't update if the update interval hasn't passed yet
                if ($diff < $GLOBALS['cfg']['releases']['update_interval'])
                    return;
            }
        }

        $mysqli->close();

        $cache_dir = $GLOBALS['cfg']['releases']['cache_dir'];

        $request_url = $GLOBALS['cfg']['releases']['request_url'];

        if (null == ($compress = $GLOBALS['cfg']['releases']['compress']))
            $compress = false;

        //if ($cache_dir == null)
        //    $decoded_json = fetch_releases_from_remote($request_url, $cache_dir, $compress);
        //else
        //    $decoded_json = fetch_releases_from_dir($cache_dir);

        $decoded_json = fetch_releases_from_remote($request_url, null, $compress);

        parse_decoded_releases($decoded_json);

    }

?>
