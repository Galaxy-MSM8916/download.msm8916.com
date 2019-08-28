<?php namespace download ?>
<?php include "view.php"; ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <link rel="stylesheet" href='<?php echo view\get_stylesheet() ?>'>
</head>

<body>
    <header>
        <div id="top_nav" class="top_nav">
            <!-- create the navbar-->
                <h1 id = "hostname"><a href="<?php echo view\get_script_base_url() ?>"><?php echo $_SERVER["HTTP_HOST"] ?></a></h1>
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

<?php
if ($_GET["view"] == downloads  && ($_GET["tag"] == null))
echo <<<EOF
    <hr id="header_hr">
    <table id="navbar_22" class="unorderedList">
        <tr>
            <td> <span>Group by:</span> </td>
            <td id="nav_groupByDate"> <a href="?view=downloads&groupBy=date">Date</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByDevice"> <a href="?view=downloads&groupBy=device">Device</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByDistribution"> <a href="?view=downloads&groupBy=dist">Distribution</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByVersion"> <a href="?view=downloads&groupBy=version">Version</a></td>
        </tr>
    </table>
EOF;
?>

    <div id="body_div" class = "div">
        <hr id="body_hr">
        <script src='<?php echo view\get_script_base_url() . "/js/main.js"?>' type='text/javascript'></script>
        <?php
            view\generate_view();
        ?>
    </div>

    <footer>
        <hr id="footer_hr">
        <div id="footer_div" class="div">
            <p id="para_time"> <i> This page was generated on <?php echo date("d") . "<sup>" . date("S") . "</sup>" . date(" M Y H:i:s T"); ?> </i></p>
        </div>
    </footer>

</body>

</html>