<?php
    require('../config.php');
    
    if ($_SESSION['userID'] != 1) {
        $taskBoard->locationIndex();
    }

    switch ($_GET['action']) {
        case 'deleteUser':
            if (isset($_POST['deleteuser-form'])) {
                $userID = $taskBoard->getUserIDByUsername($_POST['username']);
                if ($userID) {
                    $taskBoard->deleteUser($userID);
                } else {
                    header("Location: " . DIR_SYSTEM . "php/admin.php?error=nouserfound");
                    exit;
                }
            } else {
                $taskBoard->deleteUser($_GET['userID']);
            }

            header("Location: " . DIR_SYSTEM . "php/admin.php");
            exit;
        
        default:
            $taskBoard->locationIndex();
    }