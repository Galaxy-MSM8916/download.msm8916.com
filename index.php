<?php namespace download ?>
<?php error_reporting(E_ALL); ?>
<?php include "view.php"; ?>
<?php $cwd = getcwd() ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Index of <?php echo basename($cwd) . "/" ?></title>
    <link rel="stylesheet" href='<?php echo view\get_stylesheet() ?>'>
</head>

<body>
    <header>
        <table id="table_header">
            <tr>
                <td id="td_banner">
                    <h1 id = "banner1" title = "title1" class = "heading"><?php echo $_SERVER["HTTP_HOST"] ?></h1>
                </td>

                <td id="td_navbar">
                    <!-- create the navbar-->
                    <ul id="navbar_1" class="unorderedList">
                        <li id="nav_build_status"><a href="index.php?view=build_status">Build Status</a></li>
                        <li id="nav_separator"> | </li>
                        <li id="nav_downloads" class = "current_tab"> <a href="index.php?view=downloads">Downloads</a></li>
                        <li id="nav_separator"> | </li>
                        <li id="nav_about"><a href="index.php?view=about">About</a></li>
                        <li id="nav_separator"> | </li>
                        <li id="nav_contact"><a href="index.php?view=contact">Contact</a></li>
                    </ul>
                </td>
            </tr>
        </table>
    </header>
    <hr id="header_hr">

<?php
if ($_GET["view"] == downloads  && ($_GET["tag"] == null))
echo <<<EOF
    <table id="navbar_22" class="unorderedList">
        <tr>
            <td> <span>Group by:</span> </td>
            <td id="nav_groupByDate"> <a href="index.php?view=downloads&groupBy=date">Date</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByDevice"> <a href="index.php?view=downloads&groupBy=device">Device</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByDistribution"> <a href="index.php?view=downloads&groupBy=dist">Distribution</a></td>
            <td id="nav_separator"> | </td>
            <td id="nav_groupByVersion"> <a href="index.php?view=downloads&groupBy=version">Version</a></td>
        </tr>
    </table>
    <hr id="header_hr">
EOF;
?>

    <div id="body_div" class = "div">
        <script src='js/main.js' type='text/javascript'></script>
        <?php
            //TODO: model-view controller


            //&nbsp - non breaking space entity

            view\generate_view();



        ?>
        <p id="para_1" class="bodyText">
        </p>
    </div>
    <hr id="body_hr">

    <footer>
        <div id="footer_div" class="div">
            <!-- <p id="para_copy"> &copy; Vincent <?php echo date("Y"); ?> </p> -->
            <p id="para_time"> <i> This page was generated on <?php echo date("d") . "<sup>" . date("S") . "</sup>" . date(" M Y H:i:s T"); ?> </i></p>
        </div>
    </footer>

</body>

</html>