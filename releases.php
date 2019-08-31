<?php namespace download\releases;

    include "helpers.php";

    $format_map = array();
    $release_map = array();
    $tags = null;

    //$DELIM = "\\-"; //this is not getting interpreted proper for some reason?

    class rel_format
    {
        public $replace_uscore;
        public $dist_name;
        public $date_offset;
        public $dist_offset;
        public $device_offset;
        public $build_offset;
        public $channel_offset;
        public $version_offset;
        public $extra_offset;

        function __construct($dist_name, $date_offset, $dist_offset, $device_offset, $build_offset,
            $version_offset, $replace_uscore=true, $channel_offset=null, $extra_offset=null)
        {
            $this->dist_name = $dist_name;
            $this->date_offset = $date_offset;
            $this->dist_offset = $dist_offset;
            $this->device_offset = $device_offset;
            $this->build_offset = $build_offset;
            $this->version_offset = $version_offset;

            $this->replace_uscore = $replace_uscore;
            $this->channel_offset = $channel_offset;
            $this->extra_offset = $extra_offset;
        }

    }

    function get_format_map()
    {
        /*
        #TWRP-3.2.3-lineage-15.1-j10-20180921-gprimelte
        #dotOS-o-j17-20190101-NIGHTLY-gprimelte
        #oc_hotplug-bootimage-lineage-15.1-j3-20190101-gprimelte
        #rr-oreo-j30-20180804-NIGHTLY-gprimelte
        #lineage-16.0-j5-20181014-NIGHTLY-fortuna3g
        #lineage-go-16.0-j8-20181117-NIGHTLY-fortuna3g
        */

        global $format_map;

        if (count($format_map) == 0)
        {
            $format_map["TWRP"] = new rel_format("TWRP", 5, 0, 6, 4, 1, true);
            $format_map["dot"] = new rel_format("DotOS", 3, 0, 5, 2, 1, true, 4);
            $format_map["rr"] = new rel_format("ResurrectionRemix", 3, 0, 5, 2, 1, true, 4);
            $format_map["lineage-go"] = new rel_format("LineageOS Go", 4, 0, 6, 3, 2, true, 5);
            $format_map["lineage"] = new rel_format("LineageOS", 3, 0, 5, 2, 1, true, 4);
            $format_map["oc_hotplug"] = new rel_format("Kernel", 5, 0, 6, 4, 3, false);
        }
        return $format_map;
    }

    class github_release_artifact
    {
        var $name; //a filename
        var $size;
        var $download_count;
        var $download_url;

        public function __construct($name, $size, $download_count, $download_url)
        {
            $this->name = $name;
            $this->size = $size;
            $this->download_count = $download_count;
            $this->download_url = $download_url;
        }

        function getName()
        {
            return $this->name;
        }

        function getSize()
        {
            return $this->size;
        }

        function getDownloadCount()
        {
            return $this->download_count;
        }

        function getDownloadUrl()
        {
            return $this->download_url;
        }
    }

    class github_release
    {
        var $tag;
        var $artifacts;

        var $format;
        var $tokens;

        public function __construct(&$tag, &$format)
        {
            $this->tag = $tag;
            $this->format = $format;
            $this->artifacts = array();
        }

        function add_artifact($name, $size, $download_count, $download_url)
        {
            $relAsset = new github_release_artifact($name, $size, $download_count, $download_url);
            $this->artifacts[] = $relAsset;
        }

        function getArtifacts()
        {
            return $this->artifacts;
        }

        function getTokens()
        {
            if ($this->format->replace_uscore == true)
                //$tag = str_replace("_", $DELIM, $this->tag);
                $tag = str_replace("_", "-", $this->tag);
            else
                $tag = $this->tag;

            if ($this->tokens == null)
                //$this->tokens = explode($DELIM, $tag);
                $this->tokens = explode("-", $tag);
            
            return $this->tokens;
        }

        function getLongDist()
        {
            return $this->format->dist_name;
        }

        function getLongDeviceName() {
            $deviceLong = "Samsung Galaxy ";

            $device = $this->getDevice();

            if (0 == strncmp("j5", $device, 2))
                $deviceLong .= "J5";
            elseif (0 == strncmp("gprime", $device, 6))
                $deviceLong .= "GRAND Prime";
            elseif (0 == strncmp("fortuna", $device, 7))
                $deviceLong .= "GRAND Prime";
            elseif (0 == strncmp("gte", $device, 3))
                $deviceLong .= "Tab E";
            elseif (0 == strncmp("gt5", $device, 3))
                $deviceLong .= "Tab A";
            elseif (0 == strncmp("a3", $device, 2))
                $deviceLong .= "A3";
            elseif (0 == strncmp("a5", $device, 2))
                $deviceLong .= "A5";
            elseif (0 == strncmp("j7", $device, 2))
                $deviceLong .= "J7";
            elseif (0 == strncmp("o7", $device, 2))
                $deviceLong .= "On7";
            elseif (0 == strncmp("serrano", $device, 7))
                $deviceLong .= "S4 Mini VE";
            else
                $deviceLong .= "device";

            return $deviceLong;
        }

        function getDeviceModel()
        {
            //TODO: Implement this function
            return "N/A";
        }

        function getShortDist()
        {
            return $this->getTokens()[$this->format->dist_offset];
        }

        function getVersion()
        {
            return $this->getTokens()[$this->format->version_offset];
        }

        function getDate()
        {
            $dateStr = $this->getTokens()[$this->format->date_offset];

            $day = substr($dateStr, 6);
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);

            //$date = date_create($year . '-' . $month . '-' . $day);
            $date = $year . '-' . $month . '-' . $day;

            return $date;
        }

        function getBuildNum()
        {
            $build = $this->getTokens()[$this->format->build_offset];

            return substr($build, 1);
        }

        function getDevice()
        {
            return $this->getTokens()[$this->format->device_offset];
        }

        function getChannel()
        {
            if ($this->format->channel_offset !== null)
                $channel = $this->getTokens()[$this->format->channel_offset];
            else
                $channel = "NIGHTLY";

            return $channel;
        }

        function getExtra()
        {
            if ($this->format->extra_offset !== null)
                $extra = $this->getTokens()[$this->format->extra_offset];
            else
                $extra = "";

            return $extra;
        }

    }

    function read_tags($tagpath = "releases.txt")
    {
        //$cwd = getcwd();
        //$relpath = "$cwd/releases.txt";
        global $tags;

        if ($tags !== null)
            return $tags;

        $relfile = fopen($tagpath, "r");

        $tags = array();

        if ($relfile == false)
        {
            echo "Error: Could not open releases file $relpath\n";
            return $tags;
        }

        $s = fgets($relfile);

        while ($s != false)
        {
            //Strip newlines from tags
            $tags[] = rtrim($s);
            $s = fgets($relfile);
        }

        fclose($relfile);

        return $tags; 
    }

    function parse_tags()
    {
        global $release_map;

        if (count($release_map) > 0)
            return $release_map;

        $tags = read_tags();

        $release_map = array(
            "date" => array(),
            "dist" => array(),
            "version" => array(),
            "device" => array(),
            "tag" => array()
        );

        for ($i = 0; $i < count($tags); $i++)
        {
            $tag = $tags[$i];
            $rel = get_release($tag); 
            
            \download\helpers\add_value_to_2d_arr($release_map["date"], $rel->getDate(), $rel);
            \download\helpers\add_value_to_2d_arr($release_map["dist"], $rel->getLongDist(), $rel);
            \download\helpers\add_value_to_2d_arr($release_map["version"], $rel->getVersion(), $rel);
            \download\helpers\add_value_to_2d_arr($release_map["device"], $rel->getDevice(), $rel);
            \download\helpers\add_value_to_2d_arr($release_map["tag"], $tag, $rel);
        }
        return $release_map;
    }

    function get_release(&$tag)
    {
        $format = null;
        $release = null;

        $map = get_format_map();

        foreach (array_keys($map) as $key)
        {
            $n = strlen($key);

            if (strncasecmp($key, $tag, $n) == 0)
            {
                $format = $map[$key];
                $release = new github_release($tag, $format);
                break;
            }
        }
        return $release;
    }

    /* Return true iff all array values are equal to $testValue */
    function test_array_values($array, $testValue = null)
    {
        foreach($array as $value)
        {
            if ($value !== $testValue)
                return false;
        }
        return true;
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

            if ($constraint["date"] && $date != $constraint["date"])
                continue;
            if ($constraint["version"] && $version != $constraint["version"])
                continue;
            if ($constraint["device"] && $device != $constraint["device"])
                continue;
            if ($constraint["dist"] && $dist != $constraint["dist"])
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
    function fetch_releases_from_remote($out_dir = null, $compress = true, $url = null)
    {
        $count = 0;

        if ($url == null)
            $request_url = "https://api.github.com/repos/Galaxy-MSM8916/releases/releases?page=";
        else
            $request_url = $url;

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

    function parse_github_releases($decoded_json = null)
    {
        global $release_map;

        if (count($release_map) > 0)
            return $release_map;

        $release_map = array(
            "date" => array(),
            "dist" => array(),
            "version" => array(),
            "device" => array(),
            "tag" => array()
        );

        if ($decoded_json == null)
            $decoded_json = fetch_releases_from_remote();

        foreach ($decoded_json as $page)
        {
            foreach ($page as $raw_release)
            {
                $tag = $raw_release["tag_name"];

                // skip unmatched releases
                //TODO: Add remaining release types (gapps, etc)
                if(null == ($rel = get_release($tag)))
                    continue;

                foreach($raw_release["assets"] as $raw_asset)
                {
                    $name = $raw_asset["name"];
                    $size = $raw_asset["size"];
                    $download_count = $raw_asset["download_count"];
                    $download_url = $raw_asset["browser_download_url"];

                    $rel->add_artifact($name, $size, $download_count, $download_url);
                }

                \download\helpers\add_value_to_2d_arr($release_map["date"], $rel->getDate(), $rel);
                \download\helpers\add_value_to_2d_arr($release_map["dist"], $rel->getLongDist(), $rel);
                \download\helpers\add_value_to_2d_arr($release_map["version"], $rel->getVersion(), $rel);
                \download\helpers\add_value_to_2d_arr($release_map["device"], $rel->getDevice(), $rel);
                \download\helpers\add_value_to_2d_arr($release_map["tag"], $tag, $rel);
            }
        }
        return $release_map;
    }

?>
