<?php namespace download;

    include "config.secure.php";

    $cfg['releases']['request_url'] = "https://api.github.com/repos/Galaxy-MSM8916/releases/releases?page=";
    $cfg['releases']["cache_dir"] = "releases_zlib";
    $cfg['releases']["compress"] = true;

    $cfg['releases']["update_interval"] = 3600; //interval in seconds

    $cfg['github_org_url'] = "https://github.com/Galaxy-MSM8916";

    /* database information */
    $cfg['db']['host'] = 'localhost';
    // $cfg['db']['username']
    // $cfg['db']['password']
    $cfg['db']['schema'] = 'download_msm89xx';

?>

