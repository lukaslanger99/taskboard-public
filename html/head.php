<!DOCTYPE HTML>
        <html lang="en">
            <head>
                <title>TaskBoard</title>
                <?php
                echo '<link rel="stylesheet" type="text/css" href="'.DIR_SYSTEM.'css/stylesheet-properties.css">';
                if ($_SESSION['userID'] && $taskBoard->getNightmodeEnabled($_SESSION['userID'])) {
                    echo '<link rel="stylesheet" type="text/css" href="'.DIR_SYSTEM.'css/stylesheet-nightmodecolors.css">
                    ';
                } else {
                    echo '<link rel="stylesheet" type="text/css" href="'.DIR_SYSTEM.'css/stylesheet-normalcolors.css">
                    ';
                }
                ?>
                <link rel="icon" type="image/png" href="<?php echo DIR_SYSTEM ?>img/favicon.png">
                <script src="https://use.fontawesome.com/8c1a9566b2.js"></script>
                <script src="<?php echo DIR_SYSTEM ?>js/node_modules/tata-js/dist/tata.js"></script>
                <script src="<?php echo DIR_SYSTEM ?>js/forms.js" async></script>
                <script src="<?php echo DIR_SYSTEM ?>js/maps.js"></script>
                <script src="<?php echo DIR_SYSTEM ?>js/javascript.js"></script>
            </head>
