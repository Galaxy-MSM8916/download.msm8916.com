<?php namespace download ?>
<?php include "view.php"; ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <link rel="stylesheet" href='<?php echo get_stylesheet() ?>'>
</head>

<body>
    <header>
        <div id="top_nav" class="top_nav">
            <!-- create the navbar-->
                <h1 id = "hostname"><a href="<?php echo get_script_base_url() ?>"><?php echo $_SERVER["HTTP_HOST"] ?></a></h1>
                <a id="nav_downloads" href="?view=downloads">Downloads</a>
                <a id="nav_home" href="?view=home">Home</a>
                <!--
                <a id="nav_build_status" href="?view=build_status">Build Status</a>
                <a id="nav_about" href="?view=about">About</a>
                <a id="nav_contact" href="?view=contact">Contact</a>
                -->
        </div>
        <!--
        <hr />
        <div id="table_header">
                <h1 id = "banner1" title = "title1" class = "heading"><?php echo $_SERVER["HTTP_HOST"] ?></h1>
        </div>
        -->
    </header>

    <div id="sort_group_div" hidden="true">
        <hr id="header_hr">
        <table id="nav_group" class="unorderedList">
            <tr>
                <td> <span>Group by: </span> </td>
                <?php
                    $groupArray = array(
                        "build_date" => "Build Date",
                        "codename" => "Device Codename",
                        "dist_name_short" => "Distribution",
                        "build_version" => "Version",
                    );
                ?> 
                <?php $keys = array_keys($groupArray);?> 
                <?php for ($i = 0; $i < count($groupArray) - 1; $i++) { ?> 
                <td id="nav_groupBy<?=$keys[$i]?>">
                    <a href='<?= build_query_from_get(array("groupBy" => $keys[$i])) ?>'><?=$groupArray[$keys[$i]]?></a>
                </td>
                <td id="nav_separator"> | </td>
                <?php } ?> 
                <td id="nav_groupBy<?=$keys[$i]?>">
                    <a href='<?= build_query_from_get(array("groupBy" => $keys[$i])) ?>'><?=$groupArray[$keys[$i]]?></a>
                </td>
            </tr>
        </table>
        <table id="nav_sort" class="unorderedList">
            <tr>
                <td> <span>Sort:</span> </td>
                <td id="nav_SortAsc">
                    <a href='<?= build_query_from_get(array("sort" => "asc")) ?>'>Ascending order</a>
                </td>
                <td id="nav_separator"> | </td>
                <td id="nav_SortDesc">
                    <a href='<?= build_query_from_get(array("sort" => "desc")) ?>'>Descending order</a>
                </td>
            </tr>
        </table>
    </div>

    <div id="body_div" class = "div">
        <hr id="body_hr">
        <script src='<?php echo get_script_base_url() . "/js/main.js"?>' type='text/javascript'></script>
        <?php
            generate_view();
        ?>
    </div>

    <footer>
        <hr id="footer_hr">
        <div id="footer_div" class="div">
            <?php $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"] ?> 
            <?php $dateStr = date("d") . "<sup>" . date("S") . "</sup>" . date(" M Y H:i:s T"); ?> 
            <p id="para_time">
                <i>This page was generated on <?= $dateStr ?></i>
                in <i><?= round($time, 5) ?> seconds.</i>
            </p>
        </div>
    </footer>

</body>

</html>