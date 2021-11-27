<?php
    require('../config.php');
    if (!$_SESSION['userID']) {
        $taskBoard->locationIndex();
    }
    else {
        $userID = $_SESSION['userID'];
    }

    $id = $_GET['id'];
    $action = $_GET['action'];
    $currentDate = date('Y-m-d H:i');

    if ($action == 'deleteTask' || $action == 'finishTask') {
        $sql = "SELECT * FROM tasks WHERE taskID = ?;";
        $task = $taskBoard->mysqliSelectFetchObject($sql, $id);
        $parentID = $task->taskParentID;
        $type = $task->taskType;
    }

    switch ($action) {
        case 'stateOpen':
            if (isset($_POST['stateopen-submit'])) {
                $sql = "UPDATE tasks SET taskState = 'open', taskAssignedBy = '' WHERE taskID = ?;";
                $taskBoard->mysqliQueryPrepared($sql, $id);

                if ($taskBoard->getTaskType($id) == 'subtask') {
                    $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=".$taskBoard->getParentIDOfTask($id));
                }
            }
            break;

        case 'addQueue':
            if (isset($_POST['add-queue-submit'])) {
                if (empty($_POST['item'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                if ($_POST['highprio'] == 'on') {
                    $prio = 2;
                } else {
                    $prio = 1;
                }

                $queueItems = explode(",", $_POST['item']);
                foreach ($queueItems as $item) {
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messagePrio) VALUES (?, 'queue', ?, ?)";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $item, $prio);
                }
                $taskBoard->locationIndex("?success=queueadded");
            }
            break;

        case 'assign':
            if (isset($_POST['assign-submit'])) {
                $state = 'assigned';
                $sql = "UPDATE tasks SET taskState = '$state', taskDateAssigned = '$currentDate', taskAssignedBy = '$userID' WHERE taskID = ?;";
                $taskBoard->mysqliQueryPrepared($sql , $id);
                $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$id&success=taskassigned");
            }
            break;

        case 'createAppointment':
            if (isset($_POST['createappointment-submit'])) {
                if (empty($_POST['date']) || empty($_POST['title'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $title = str_replace(array("\r","\n")," ", $_POST['title']);
                $date = $_POST['date'];
                $groupID = (int) $_POST['groupID'];
                $sql = "INSERT INTO messages 
                (messageOwner, messageGroup, messageType, messageTitle, messageDate) 
                VALUES (?, ?, 'appointment', ?, ?);";
                $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $groupID, $title, $date);
                $taskBoard->locationIndex("?success=appointmentcreated");
            }
            break;

        case 'createComment':
            if (isset($_POST['createcomment-submit'])) {
                if (empty($_POST['description'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $type = $_GET['type'];
                if (empty($_POST['description'])) {
                    $description = '-';
                } else {
                    $description = $_POST['description'];
                }
                $taskBoard->createComment($id, $type, $userID, $description, $currentDate);
                $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$id&success=commentcreated");
            }
            break;
        
        case 'createGroup':
            if (isset($_POST['creategroup-submit'])) {
                $userID = $_SESSION['userID'];
                if ($taskBoard->getMailState($userID) == 'unverified') {
                    if (strpos($_SESSION['enteredUrl'], '?')) {
                        header("Location: " . DOMAIN.$_SESSION['enteredUrl']."&error=unverifiedmail");
                    } else {
                        header("Location: " . DOMAIN.$_SESSION['enteredUrl']."?error=unverifiedmail");
                    }
                    exit;
                }
                if ($taskBoard->getNumberOfOwnedGroups($userID) > 9 && $taskBoard->getUserType($userID) == 'normal') {
                    $taskBoard->locationIndex("?error=maxgroups");
                }
                if (empty($_POST['name'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $groupName = $_POST['name'];
                $sql = "INSERT INTO groups (groupName, groupOwner) VALUES (?, ?);";
                $taskBoard->mysqliQueryPrepared($sql, $groupName, $userID);
                $group = $taskBoard->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupName = ? AND groupOwner = ?", $groupName, $userID);
                $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES ( ?, ?)", $group->groupID, $group->groupOwner);

                if (strpos($_SESSION['enteredUrl'], '?')) {
                    $url = DOMAIN . $_SESSION['enteredUrl']."&success=groupcreated";
                } else {
                    $url = DOMAIN . $_SESSION['enteredUrl']."?success=groupcreated";
                }
                $taskBoard->localstorageGroupUpdate($url);
            }
            break;

        case 'createMotd':
            if (isset($_POST['createmotd-submit'])) {
                if (empty($_POST['title'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $title = str_replace(array("\r","\n")," ", $_POST['title']);
                $groupID = (int) $_POST['groupID'];
                $sql = "INSERT INTO messages (messageOwner, messageGroup, messageType, messageTitle) VALUES (?, ?, 'motd', ?);";
                $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $groupID, $title);
                $taskBoard->locationIndex("?success=motdcreated");
            }
            break;

        case 'createRepeatingtask':
            if (isset($_POST['creatert-submit'])) {
                $title = str_replace(array("\r","\n")," ", $_POST['title']);
                if ($_POST['taskEverySecondDay']) {
                    $currentDay = $taskBoard->getWeekday();
                    if ($currentDay == 'MON' || $currentDay == 'WED' || $currentDay == 'FRI' || $currentDay == 'SUN') {
                        if ($taskBoard->getWeek() == 'odd') {
                            if ($_POST['taskStartToday']) {
                                $state1 = 'odd';
                                $state2 = 'even';
                            } else {
                                $state1 = 'even';
                                $state2 = 'odd';
                            }
                        } else {
                            if ($_POST['taskStartToday']) {
                                $state1 = 'even';
                                $state2 = 'odd';
                            } else {
                                $state1 = 'odd';
                                $state2 = 'even';
                            }
                        }
                    } else {
                        if ($taskBoard->getWeek() == 'odd') {
                            if ($_POST['taskStartToday']) {
                                $state1 = 'even';
                                $state2 = 'odd';
                            } else {
                                $state1 = 'odd';
                                $state2 = 'even';
                            }
                        } else {
                            if ($_POST['taskStartToday']) {
                                $state1 = 'odd';
                                $state2 = 'even';
                            } else {
                                $state1 = 'even';
                                $state2 = 'odd';
                            }
                        }
                    }
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Mon', $state1);
                            $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Wed', $state1);
                            $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Fri', $state1);
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Sun', $state1);

                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Tue', $state2);
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Thu', $state2);
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) 
                            VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, 'Sat', $state2);
                } else {
                    if (empty($_POST['title'])) {
                        $taskBoard->locationIndex("?error=emptyfields");
                    }
                    $weekday = $_POST['weekday'];
                    $quantity = $_POST['quantity'];
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messageWeekday, messageQuantity) VALUES (?, 'repeatingtask', ?, ?, ?);";
                    $taskBoard->mysqliQueryPrepared($sql, $_SESSION['userID'], $title, $weekday, $quantity);
                }
                $taskBoard->locationIndex("?success=rtcreated");
            }
            break;
        
        case 'createSubtask':
            if (isset($_POST['createtask-submit'])) {
                if (empty($_POST['title'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $priority = (int) $_POST['priority'];
                $priorityColor = $taskBoard->getPriorityColor($priority);
                $state = 'open';
                $title = $_POST['title'];
                if (empty($_POST['description'])) {
                    $description = '-';
                } else {
                    $description = $_POST['description'];
                }
                $taskId = $_GET['taskId'];
                $sql = "INSERT INTO tasks 
                (taskType, taskParentID, taskPriority, taskPriorityColor, taskTitle, taskDescription, taskState, taskDateCreated) 
                VALUES ('subtask', ?, ?, '$priorityColor', ?, ?, '$state', '$currentDate');";
                $taskBoard->mysqliQueryPrepared($sql, $taskId, $priority, $title, $description);
                if ($_POST['createAnother']) {
                    $createAnother = '&createSubtask=true';
                } else {
                    $createAnother = '';
                }
                $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=".$taskId.$createAnother."&success=subtaskcreated");
            }
            break;

        case 'createTask':
            if (isset($_POST['createtask-submit'])) {
                if (empty($_POST['title'])) {
                    $taskBoard->locationIndex("?error=emptyfields");
                }
                $priority = (int) $_POST['priority'];
                $priorityColor = $taskBoard->getPriorityColor($priority);
                $state = 'open';
                $title = $_POST['title'];
                if (empty($_POST['description'])) {
                    $description = '-';
                } else {
                    $description = $_POST['description'];
                }
                $groupID = (int) $_POST['groupID'];
            
                $sql = "INSERT INTO tasks 
                (taskType, taskParentID, taskPriority, taskPriorityColor, taskTitle, taskDescription, taskState, taskDateCreated) 
                VALUES ('task', ?, ?, '$priorityColor', ?, ?, '$state', '$currentDate');";
                $taskBoard->mysqliQueryPrepared($sql, $groupID, $priority, $title, $description);
                if ($_POST['createAnother']) {
                    $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], "createTask=true&groupID=$groupID&success=taskcreated");
                } else {
                    $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], "success=taskcreated");
                }
            }
            break;
        
        case 'deleteTask':
            if ($taskBoard->deleteTaskPermission($id, $_SESSION['userID'], $type)) {
                $taskBoard->mysqliQueryPrepared("DELETE FROM tasks WHERE taskID = ?", $id);
                $taskBoard->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?", $id);
                $taskBoard->mysqliQueryPrepared("DELETE FROM comments WHERE commentTaskID = ?", $id);

                if ($type == 'task') {
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=$parentID&success=deletetask");
                } else if ($type == 'subtask') {
                    $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$parentID&success=deletesubtask");
                }
                exit;
            }
            break;

        case 'deleteMessage':
            $messageOwner = $taskBoard->mysqliSelectFetchObject("SELECT messageOwner FROM messages WHERE messageID = ?", $id);
            if ($messageOwner->messageOwner == $_SESSION['userID'] || 1 == $_SESSION['userID']) {
                $taskBoard->mysqliQueryPrepared("DELETE FROM messages WHERE messageID = ?", $id);
                $taskBoard->locationIndex("?success=deletemessage");
            }
            break;

        case 'deleteComment':
            $taskId = $_GET['taskId'];
            if ($_SESSION['delete'] == $taskId) {
                $taskBoard->mysqliQueryPrepared("DELETE FROM comments WHERE commentID = ?", $id);
            }
            $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$taskId&success=deletecomment");
            exit;

        case 'deleteGroup':
            if ($_SESSION['deleteGroup'] == $id) {
                $taskBoard->deleteGroup($id);
                $taskBoard->localstorageGroupUpdate(DIR_SYSTEM . "?success=deletegroup");
            }
            break;
        
        case 'finishTask':
            if (isset($_POST['finish-submit'])) {
                $sql = "SELECT COUNT(*) as number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ? AND (taskState = 'open' OR taskState = 'assigned')";
                $subtasks = $taskBoard->mysqliSelectFetchObject($sql, $id);
                if ($subtasks->number > 0) {
                    header("Location: " . DIR_SYSTEM . "php/details.php?action=taskDetails&id=$id&error=unfinishedsubtasks");
                    exit;
                }
                $state = 'finished';
                $sql = "UPDATE tasks SET taskState = '$state', taskDateFinished = '$currentDate' WHERE taskID = ?";
                $taskBoard->mysqliQueryPrepared($sql, $id);

                if ($type == 'task') {
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=$parentID&success=finishedtask");
                } else if ($type == 'subtask') {
                    $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$parentID&success=finishedsubtask");
                }
                exit;
            }
            break;

        case 'generateToken':
            if (isset($_POST['groupinvite-submit'])) {
                if (!empty($_POST['name'])) {
                    if ($taskBoard->checkUsername($_POST['name'])) {
                        $user = $taskBoard->mysqliSelectFetchObject("SELECT userID FROM users WHERE userName = ?", $_POST['name']);
                        $sql = "INSERT INTO tokens (tokenType, tokenGroupID, tokenUserID, tokenToken) VALUES ('joingroup', ?, ?, ?)";
                        $taskBoard->mysqliQueryPrepared($sql, $id, $user->userID, $taskBoard->generateRandomString());
                        $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id."&success=invited");
                        exit;
                    } else {
                        $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id."&error=nouserfound");
                        exit;
                    }
                }
            }
            break;

        case 'groupinvites':
            if (isset($_POST['groupinvites-submit'])) {
                $enableInvites = $_GET['invites']; // enable, disable
                
                if ($enableInvites == 'enable') {
                    $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupInvites = 'enabled' WHERE groupID = ?;" , $id);
                    //token anlegen
                    $sql = "INSERT INTO tokens (tokenType, tokenGroupID, tokenToken) VALUES ('groupinvite', ?, ?)";
                    $taskBoard->mysqliQueryPrepared($sql, $id, $taskBoard->generateRandomString());
                } else if ($enableInvites == 'disable') {
                    $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupInvites = 'disabled' WHERE groupID = ?;" , $id);
                    //token löschen
                    $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ? AND tokenType = 'groupinvite'", $id);
                }
                $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id);
                exit;
            }
            break;

        case 'groupstate':
            if(isset($_POST['groupstate-submit'])) {
                $stateAction = $_GET['state']; // activate, hide

                if ($stateAction == 'activate') {
                    $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupState = 'active' WHERE groupID = ?;" , $id);
                } else if ($stateAction == 'hide') {
                    $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupState = 'hidden' WHERE groupID = ?;" , $id);
                }
                $taskBoard->localstorageGroupUpdate(DIR_SYSTEM."php/details.php?action=groupDetails&id=".$id);
                exit;
            }
            break;

        case 'refreshinvite':
            if ($taskBoard->groupOwnerCheck($id, $_SESSION['userID'])) {
                $taskBoard->mysqliQueryPrepared("UPDATE tokens 
                SET tokenToken = ? WHERE tokenType = 'groupinvite' AND tokenGroupID = ?;" , $taskBoard->generateRandomString(), $id);
                $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id);
                exit;
            }
            break;

        case 'joingroup':
            $token = $_GET['t'];
            $tokenData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $token);
            if ($tokenData) {
                $user = $taskBoard->getUserData($_SESSION['userID']);
                $groupCheck = $taskBoard->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE groupID = ? AND userID = ?", $tokenData->tokenGroupID, $user->userID);
                if ($groupCheck) {
                    header("Location: " . DIR_SYSTEM . "php/details.php?action=groupDetails&id=".$tokenData->tokenGroupID."&warning=alreadyjoined");
                    exit;
                }
                if ($user->userType == 'normal' && $taskBoard->getNumberOfGroupUsers($token->tokenGroupID) > 5) {
                    header("Location: " . DIR_SYSTEM . "php/profile.php?error=maxgroupusers");
                    exit;
                }
                $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES (?, ?)", $tokenData->tokenGroupID, $user->userID);
                $taskBoard->localstorageGroupUpdate(DIR_SYSTEM . "php/details.php?action=groupDetails&id=".$tokenData->tokenGroupID."&success=joinedgroup");
            }
            break;

        case 'leaveGroup':
            $taskBoard->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ? AND userID = ?", $_GET['groupID'], $_SESSION['userID']);
            $taskBoard->locationWithDir("php/groups.php?success=leavegroup");
            exit;

        case 'removeUser':
            $groupID = $_GET['groupID'];
            if ($taskBoard->groupOwnerCheck($_GET['groupID'], $_SESSION['userID'])) {
                $taskBoard->mysqliQueryPrepared("DELETE FROM groupaccess WHERE userID = ? AND groupID = ?", $_GET['userID'], $groupID);
                $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=$groupID&success=removeduser");
            }
            break;

        case 'repeatingTaskDone':
            // if (isset($_POST['rtdone-submit'])) {
                $date = date('Y-m-d');
                $sql = "UPDATE messages SET messageState = '$date' WHERE messageID = ?";
                $taskBoard->mysqliQueryPrepared($sql, $id);
            // }
            break;
        
        case 'update':
            if (isset($_POST['updatetask-submit'])) {
                $sql = "SELECT * FROM tasks WHERE taskID = ?";
                $task = $taskBoard->mysqliSelectFetchObject($sql, $id);

                $comment = '';
                $priority = (int) $_POST['priority'];

                if ($task->taskType == 'task') {
                    $parentID = $_POST['groupID'];
                } else {
                    $parentID = $task->taskParentID;
                }

                if ($task->taskPriority != $priority) {
                    $comment .= 'PRIORITY['.$task->taskPriority.' -> '.$priority.']';
                }
                if ($task->taskType == 'task' && $task->taskParentID != $_POST['groupID']) {
                    $comment .= 'GROUP['.$taskBoard->getGroupNameByID($task->taskParentID).' -> '.$taskBoard->getGroupNameByID($parentID).']';
                }
                if ($task->taskTitle != $_POST['title']) {
                    $comment .= 'TITLE['.$task->taskTitle.' -> '.$_POST['title'].']';
                }
                if ($task->taskDescription != $_POST['description']) {
                    $comment .= 'DESCRIPTION['.$task->taskDescription.' -> '.$_POST['description'].']';
                }
                if ($comment != '') {
                    $comment = '['.$taskBoard->getUsernameByID($_SESSION['userID']).'] '.$comment;
                    $taskBoard->createComment($id, $task->taskType, 'Auto-Created', $comment, $currentDate);
                }

                $priorityColor = $taskBoard->getPriorityColor($priority);
                $title = $_POST['title'];
                if (empty($_POST['description'])) {
                    $description = '-';
                } else {
                    $description = $_POST['description'];
                }
            
                $sql = "UPDATE tasks SET 
                taskParentID = ?,
                taskPriority = ?,
                taskPriorityColor = '$priorityColor',
                taskTitle = ?,
                taskDescription = ? 
                WHERE taskID = ?";
                $taskBoard->mysqliQueryPrepared($sql, $parentID, $priority, $title, $description, $id);
                $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$id&success=updatedtask");
                exit;
            }
            break;

        case 'updateAppointment':
            if (isset($_POST['updatemessage-submit'])) {
                $title = $_POST['title'];
                $date = $_POST['date'];
                $sql = "UPDATE messages SET messageTitle = ?, messageDate = ? WHERE messageID = ?;";
                $taskBoard->mysqliQueryPrepared($sql , $title, $date, $id);
                $taskBoard->locationIndex("?success=updatedmessage");
            }
            break;

        case 'updateComment':
            if (isset($_POST['updatecomment-submit'])) {
                $taskBoard->mysqliQueryPrepared("UPDATE comments SET commentDescription = ? WHERE commentID = ?", $_POST['text'], $_GET['commentID']);
                $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], 'success=commentupdated');
            }
            break;

        case 'updateGroup':
            if (isset($_POST['updategroup-submit'])) {
                $name = $_POST['name'];
                $priority = $_POST['priority'];
                $archiveTime = $_POST['archivetime'];

                if ($priority > 1000 || $archiveTime > 365) {
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id."&error=highnumber");
                }
                $sql = "UPDATE groups SET groupName = ?, groupPriority = ?, groupArchiveTime = ? WHERE groupID = ?";
                $taskBoard->mysqliQueryPrepared($sql, $name, $priority, $archiveTime, $id);
                $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$id."&success=updatedgroup");
            }
            break;

        case 'updateMessage':
            if (isset($_POST['updatemessage-submit'])) {
                $title = $_POST['title'];
                $sql = "UPDATE messages SET messageTitle = ? WHERE messageID = ?;";
                $taskBoard->mysqliQueryPrepared($sql , $title, $id);
                $taskBoard->locationIndex("?success=updatedmessage");
            }
            break;

        case 'updateRT':
            if (isset($_POST['updatemessage-submit'])) {
                $title = $_POST['title'];
                $weekday = $_POST['weekday'];
                $quantity = $_POST['quantity'];
                $sql = "UPDATE messages SET messageTitle = ?, messageWeekday = ?, messageQuantity = ? WHERE messageID = ?;";
                $taskBoard->mysqliQueryPrepared($sql , $title, $weekday, $quantity, $id);
                $taskBoard->locationIndex("?success=updatedmessage");
            }
            break;
        
        default:
            break;
    }
    
    header("Location: " . DIR_SYSTEM);
    exit;