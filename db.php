<?php namespace download;

    function connect_to_db()
    {
        $host = $GLOBALS['cfg']['db']['host'];
        $user = $GLOBALS['cfg']['db']['username'];
        $pass = $GLOBALS['cfg']['db']['password'];
        $dbname = $GLOBALS['cfg']['db']['schema'];

        if ($host && $user && $pass && $dbname)
        {
            $mysqli = new \mysqli($host, $user, $pass, $dbname);
        }
        else // try connecting with defaults
            $mysqli = new \mysqli();

        if ($mysqli->connect_errno)
            die("Failed to connect to db: " . $mysqli->connect_errno . " - " . $mysqli->connect_error);

        // Set connection character set
        if(false == $mysqli->set_charset("utf8"))
        {
            die("Failed to set charset: " . $mysqli->errno . " - " . $mysqli->error);
        }

        return $mysqli;
    }

?>