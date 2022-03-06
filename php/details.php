<?php
    require('../config.php');
    $_SESSION['enteredUrl'] = str_replace('createTask=true', '', $_SERVER['REQUEST_URI']);
    if (!$_SESSION['userID']) {
        $taskBoard->locationIndex();
    }

    switch ($_GET['action']) {
        case 'taskDetails':
            $id = $_GET['id'];
            
            $sql = "SELECT * FROM tasks WHERE taskID = ?";
            $task = $taskBoard->mysqliSelectFetchObject($sql, $id);
            if ($task && ($taskBoard->checkTaskPermission($_SESSION['userID'], $task) || $_SESSION['userID'] == 1)) {
                require('../html/top-bar.php');
                echo '<div class="group-box">';
                $taskBoard->printTaskDetails($task, $id);
                $_SESSION['delete'] = $task->taskID;
            } else {
                $taskBoard->locationIndex("?error=invalidurl");
            }
            break;

        case 'groupDetails':
            $sql = "SELECT * FROM groups WHERE groupID = ?";
            $group = $taskBoard->mysqliSelectFetchObject($sql, $_GET['id']);

            if ($group && ($taskBoard->checkGroupPermission($_SESSION['userID'], $group->groupID) || $_SESSION['userID'] == 1) ) {
                require('../html/top-bar.php');
                $taskBoard->printGroupDetails($group);
                $_SESSION['deleteGroup'] = $group->groupID;
            } else {
                $taskBoard->locationIndex("?error=invalidurl");
            }
        
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