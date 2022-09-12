<?php
require('../config.php');
($_SESSION['userID']) ? $userID = $_SESSION['userID'] : $taskBoard->locationIndex();
$id = $_GET['id'];
$action = $_GET['action'];
$currentDate = date('Y-m-d H:i');

switch ($action) {

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
                $sql = "INSERT INTO comments (commentTaskID, commentAuthor, commentDescription, commentDate) VALUES (?, 'Auto-Created', ?, '$date')";
                $taskBoard->mysqliQueryPrepared($sql, $taskId, $description);
            }

            $priorityColor = $taskBoard->getPriorityColor($priority);
            $title = $_POST['title'];
            (empty($_POST['description'])) ? $description = '-' : $description = $_POST['description'];

            $sql = "UPDATE tasks SET 
                taskParentID = ?,
                taskPriority = ?,
                taskPriorityColor = '$priorityColor',
                taskTitle = ?,
                taskDescription = ?,
                taskDateUpdated = ?
                WHERE taskID = ?";
            $taskBoard->mysqliQueryPrepared($sql, $parentID, $priority, $title, $description, date('Y-m-d H:i'), $id); // switch date to function call when moved to request.php
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
