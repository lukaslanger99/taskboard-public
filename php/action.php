<?php
require('../config.php');
($_SESSION['userID']) ? $userID = $_SESSION['userID'] : $taskBoard->locationIndex();
$id = $_GET['id'];
$action = $_GET['action'];
$currentDate = date('Y-m-d H:i');

if ($action == 'deleteTask' || $action == 'closeTask') {
    $sql = "SELECT * FROM tasks WHERE taskID = ?;";
    $task = $taskBoard->mysqliSelectFetchObject($sql, $id);
    $parentID = $task->taskParentID;
    $type = $task->taskType;
}

switch ($action) {
    case 'stateOpen':
        if (isset($_POST['stateopen-submit'])) {
            $taskBoard->mysqliQueryPrepared("UPDATE tasks SET taskState = 'open', taskAssignedBy = '' WHERE taskID = ?", $id);
            if ($taskBoard->getTaskType($id) == 'subtask') $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=" . $taskBoard->getParentIDOfTask($id));
        }
        break;

    case 'updateWeatherCity':
        if (isset($_POST['update-weather-submit'])) {
            if (empty($_POST['city'])) $taskBoard->locationIndex("?error=emptyfields");
            $taskBoard->mysqliQueryPrepared("UPDATE panels SET panelWeatherCity = ? WHERE userID = ?", $_POST['city'], $userID);
            $taskBoard->locationIndex("?success=weathercityupdated");
        }
        break;

    case 'assign':
        if (isset($_POST['assign-submit'])) {
            $taskBoard->mysqliQueryPrepared("UPDATE tasks SET taskAssignedBy = '$userID' WHERE taskID = ?", $id);
            $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$id&success=taskassigned");
        }
        break;

    case 'createComment':
        if (isset($_POST['createcomment-submit'])) {
            if (empty($_POST['description'])) $taskBoard->locationIndex("?error=emptyfields");
            (empty($_POST['description'])) ? $description = '-' : $description = $_POST['description'];
            $taskBoard->createComment($id, $_GET['type'], $userID, $description, $currentDate);
            $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$id&success=commentcreated");
        }
        break;

    case 'createGroup':
        if (isset($_POST['creategroup-submit'])) {
            if ($taskBoard->getMailState($userID) == 'unverified') {
                if (strpos($_SESSION['enteredUrl'], '?')) {
                    header("Location: " . DOMAIN . $_SESSION['enteredUrl'] . "&error=unverifiedmail");
                } else {
                    header("Location: " . DOMAIN . $_SESSION['enteredUrl'] . "?error=unverifiedmail");
                }
                exit;
            }
            if ($taskBoard->getNumberOfOwnedGroups($userID) > 9 && $taskBoard->getUserType($userID) == 'normal') $taskBoard->locationIndex("?error=maxgroups");
            if (empty($_POST['name'])) $taskBoard->locationIndex("?error=emptyfields");
            $groupName = $_POST['name'];
            $sql = "INSERT INTO groups (groupName, groupOwner) VALUES (?, ?);";
            $taskBoard->mysqliQueryPrepared($sql, $groupName, $userID);
            $group = $taskBoard->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupName = ? AND groupOwner = ?", $groupName, $userID);
            $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES ( ?, ?)", $group->groupID, $group->groupOwner);
            $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], "success=groupcreated");
        }
        break;

    case 'deleteTask':
        if ($taskBoard->deleteTaskPermission($id, $userID, $type)) {
            $taskBoard->mysqliQueryPrepared("DELETE FROM tasks WHERE taskID = ?", $id);
            $taskBoard->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?", $id);
            $taskBoard->mysqliQueryPrepared("DELETE FROM comments WHERE commentTaskID = ?", $id);
            $taskBoard->mysqliQueryPrepared("DELETE FROM tasklabels WHERE taskID = ?", $id);
            if ($type == 'task') $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=$parentID&success=deletetask");
            else if ($type == 'subtask') $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$parentID&success=deletesubtask");
            exit;
        }
        break;

    case 'deleteMessage':
        $messageData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM messages WHERE messageID = ?", $id);
        if ($messageData->messageOwner == $_SESSION['userID'] || $taskBoard->groupOwnerCheck($messageData->messageGroup, $_SESSION['userID']) || 1 == $_SESSION['userID']) {
            $taskBoard->mysqliQueryPrepared("DELETE FROM messages WHERE messageID = ?", $id);
            $taskBoard->locationIndex("?success=deletemessage");
        }
        break;

    case 'deleteComment':
        $taskId = $_GET['taskId'];
        if ($_SESSION['delete'] == $taskId) $taskBoard->mysqliQueryPrepared("DELETE FROM comments WHERE commentID = ?", $id);
        $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$taskId&success=deletecomment");
        exit;

    case 'deleteGroup':
        if ($_SESSION['deleteGroup'] == $id) {
            $taskBoard->deleteGroup($id);
            $taskBoard->locationWithDir("?success=deletegroup");
        }
        break;

    case 'closeTask':
        if (isset($_POST['finish-submit'])) {
            $sql = "SELECT COUNT(*) as number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ? AND taskState = 'open'";
            $subtasks = $taskBoard->mysqliSelectFetchObject($sql, $id);
            if ($subtasks->number > 0) {
                header("Location: " . DIR_SYSTEM . "php/details.php?action=taskDetails&id=$id&error=unclosedsubtasks");
                exit;
            }
            $sql = "UPDATE tasks SET taskState = 'closed', taskDateClosed = '$currentDate' WHERE taskID = ?";
            $taskBoard->mysqliQueryPrepared($sql, $id);
            if ($type == 'task') $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=$parentID&success=closedtask");
            else if ($type == 'subtask') $taskBoard->locationWithDir("php/details.php?action=taskDetails&id=$parentID&success=closedsubtask");
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
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id . "&success=invited");
                    exit;
                } else {
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id . "&error=nouserfound");
                    exit;
                }
            }
        }
        break;

    case 'groupinvites':
        if (isset($_POST['groupinvites-submit'])) {
            $enableInvites = $_GET['invites']; // enable, disable
            if ($enableInvites == 'enable') {
                $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupInvites = 'enabled' WHERE groupID = ?;", $id);
                $sql = "INSERT INTO tokens (tokenType, tokenGroupID, tokenToken) VALUES ('groupinvite', ?, ?)";
                $taskBoard->mysqliQueryPrepared($sql, $id, $taskBoard->generateRandomString());
            } else if ($enableInvites == 'disable') {
                $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupInvites = 'disabled' WHERE groupID = ?;", $id);
                $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ? AND tokenType = 'groupinvite'", $id);
            }
            $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id);
            exit;
        }
        break;

    case 'groupstate':
        if (isset($_POST['groupstate-submit'])) {
            $stateAction = $_GET['state']; // activate, hide
            if ($stateAction == 'activate') $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupState = 'active' WHERE groupID = ?;", $id);
            else if ($stateAction == 'hide') $taskBoard->mysqliQueryPrepared("UPDATE groups SET groupState = 'hidden' WHERE groupID = ?;", $id);
            $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id);
            exit;
        }
        break;

    case 'refreshinvite':
        if ($taskBoard->groupOwnerCheck($id, $_SESSION['userID'])) {
            $taskBoard->mysqliQueryPrepared("UPDATE tokens 
                SET tokenToken = ? WHERE tokenType = 'groupinvite' AND tokenGroupID = ?;", $taskBoard->generateRandomString(), $id);
            $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id);
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
                header("Location: " . DIR_SYSTEM . "php/details.php?action=groupDetails&id=" . $tokenData->tokenGroupID . "&warning=alreadyjoined");
                exit;
            }
            if ($user->userType == 'normal' && $taskBoard->getNumberOfGroupUsers($token->tokenGroupID) > 5) {
                header("Location: " . DIR_SYSTEM . "php/profile.php?error=maxgroupusers");
                exit;
            }
            $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES (?, ?)", $tokenData->tokenGroupID, $user->userID);
            $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $tokenData->tokenGroupID . "&success=joinedgroup");
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

    case 'update':
        if (isset($_POST['updatetask-submit'])) {
            $sql = "SELECT * FROM tasks WHERE taskID = ?";
            $task = $taskBoard->mysqliSelectFetchObject($sql, $id);

            $comment = '';
            $priority = (int) $_POST['priority'];
            ($task->taskType == 'task') ? $parentID = $_POST['groupID'] : $parentID = $task->taskParentID;

            if ($task->taskPriority != $priority) $comment .= 'PRIORITY[' . $task->taskPriority . ' -> ' . $priority . ']';
            if ($task->taskType == 'task' && $task->taskParentID != $_POST['groupID']) {
                $comment .= 'GROUP[' . $taskBoard->getGroupNameByID($task->taskParentID) . ' -> ' . $taskBoard->getGroupNameByID($parentID) . ']';
            }
            if ($task->taskTitle != $_POST['title']) $comment .= 'TITLE[' . $task->taskTitle . ' -> ' . $_POST['title'] . ']';
            if ($task->taskDescription != $_POST['description']) $comment .= 'DESCRIPTION[' . $task->taskDescription . ' -> ' . $_POST['description'] . ']';
            if ($comment != '') {
                $comment = '[' . $taskBoard->getUsernameByID($_SESSION['userID']) . '] ' . $comment;
                $taskBoard->createComment($id, $task->taskType, 'Auto-Created', $comment, $currentDate);
            }

            $priorityColor = $taskBoard->getPriorityColor($priority);
            $title = $_POST['title'];
            (empty($_POST['description'])) ? $description = '-' : $description = $_POST['description'];

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

            if ($priority > 1000 || $archiveTime > 365) $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id . "&error=highnumber");
            $sql = "UPDATE groups SET groupName = ?, groupPriority = ?, groupArchiveTime = ? WHERE groupID = ?";
            $taskBoard->mysqliQueryPrepared($sql, $name, $priority, $archiveTime, $id);
            $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=" . $id . "&success=updatedgroup");
        }
        break;

    default:
        break;
}

header("Location: " . DIR_SYSTEM);
exit;
