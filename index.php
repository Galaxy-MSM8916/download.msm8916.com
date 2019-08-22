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
    <div id="header_div" class = "div">
        <h1 id = "banner1" title = "title1" class = "heading"></h1>

        <!-- create the navbar-->
        <ul id="navbar_1" class="unorderedList">
            <li> <a href="index.php?view=build_status"> Build Status </a> </li>
            <li> <a href="index.php?view=downloads"> Downloads </a> </li>
        </ul>
    </div>
    <hr id="header_hr">

    <div id="body_div" class = "div">
        <script src='js/header.js' type='text/javascript'></script>
        <?php
            //TODO: model-view controller


            //&nbsp - non breaking space entity

            view\generate_view();



        ?>
        <p id="para_1" class="bodyText">
        </p>
    </div>
    <hr id="body_hr">

    <div id="footer_div" class="div">
        <?php
        ?>
        <ul id="navbar_2" class="unorderedList">
            <li> <a href="index.php?view=about"> About </a> </li>
            <li> <a href="index.php?view=contact"> Contact </a> </li>
        </ul>
        <!-- <p id="para_copy"> &copy; Vincent <?php echo date("Y"); ?> </p> -->
        <p id="para_time"> <i> This page was generated on <?php echo date("d") . "<sup>" . date("S") . "</sup>" . date(" M Y H:i:s T"); ?> </i></p>
    </div>

</body>

</html>