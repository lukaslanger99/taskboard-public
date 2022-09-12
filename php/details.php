<?php
require('../config.php');
$_SESSION['enteredUrl'] = str_replace('createTask=true', '', $_SERVER['REQUEST_URI']);
if (!$_SESSION['userID']) {
    $taskBoard->locationIndex();
}

switch ($_GET['action']) {
    case 'taskDetails':
        require('../html/top-bar.php');
        echo '<div id="taskdetails"></div>
            <script>taskdetailsHandler.printTaskdetails()</script>';
        break;

    case 'groupDetails':
        require('../html/top-bar.php');
        echo '<div id="groupdetails"></div>
            <script>groupdetailsHandler.printGroupdetails()</script>';
        break;

    case 'userDetails':
        if ($_SESSION['userID'] == 1) {
            require('../html/top-bar.php');
            echo $taskBoard->printUserDetails($_GET['userID']);
        } else {
            $taskBoard->locationIndex();
            exit;
        }
        break;

    default:
        $taskBoard->locationIndex("?error=invalidurl");
        break;
}
require('../html/bottom.php');
