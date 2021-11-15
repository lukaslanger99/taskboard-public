<?php
    require('../config.php');
    unset($_SESSION['userID']);
    echo "
    <script>localStorage.removeItem(\"Groups\");</script>
    <META HTTP-EQUIV=\"refresh\" content=\"0;URL= " . DIR_SYSTEM . "\">
    ";
    exit;
