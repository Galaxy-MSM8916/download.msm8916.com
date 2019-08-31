<?php namespace download;

    $cfg['releases']['request_url'] = "https://api.github.com/repos/Galaxy-MSM8916/releases/releases?page=";
    $cfg['releases']["cache_dir"] = "releases_zlib";
    $cfg['releases']["compress"] = true;

    $cfg['releases']["update_interval"] = 3600; //interval in seconds

    $cfg['github_org_url'] = "https://github.com/Galaxy-MSM8916";

?>

