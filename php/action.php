<?php
require('../config.php');
($_SESSION['userID']) ? $userID = $_SESSION['userID'] : $taskBoard->locationIndex();
$id = $_GET['id'];
$action = $_GET['action'];
$currentDate = date('Y-m-d H:i');

switch ($action) {

    case 'updateComment':
        if (isset($_POST['updatecomment-submit'])) {
            $taskBoard->mysqliQueryPrepared("UPDATE comments SET commentDescription = ? WHERE commentID = ?", $_POST['text'], $_GET['commentID']);
            $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], 'success=commentupdated');
        }
        break;

    default:
        break;
}

header("Location: " . DIR_SYSTEM);
exit;
