<!DOCTYPE HTML>
<html lang="en">

<head>
    <title>TaskBoard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/stylesheet-properties.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/appointment.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/calendar.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/fa.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/login.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/timetable.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/weather.css">';
    if ($_SESSION['userID'] && !$taskBoard->getNightmodeEnabled($_SESSION['userID'])) {
        echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/stylesheet-normalcolors.css">';
    } else {
        echo '<link rel="stylesheet" type="text/css" href="' . DIR_SYSTEM . 'css/stylesheet-nightmodecolors.css">';
    }
    ?>
    <link rel="icon" type="image/png" href="<?php echo DIR_SYSTEM ?>img/favicon.png">
    <script src="https://kit.fontawesome.com/41b6d947e6.js" crossorigin="anonymous"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/node_modules/tata-js/dist/tata.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/forms.js" defer></script>
    <script src="<?php echo DIR_SYSTEM ?>js/draggableHandler.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/indexHandler.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/javascript.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/labelHandler.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/maps.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/panels.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/taskHandler.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/timetable.js"></script>
    <script src="<?php echo DIR_SYSTEM ?>js/weather.js"></script>
</head>