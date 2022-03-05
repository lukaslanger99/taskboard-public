<?php
    require('../config.php');
    unset($_SESSION['userID']);
    echo "
    <META HTTP-EQUIV=\"refresh\" content=\"0;URL= " . DIR_SYSTEM . "\">
    ";
    exit;
