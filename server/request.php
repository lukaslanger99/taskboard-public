<?php
require('requestHandler.php');
$rh = new RequestHandler();

if (!$_SESSION['userID']) {
    $taskBoard->locationIndex();
} else {
    $userID = $_SESSION['userID'];
}

switch ($_GET['action']) {
    case 'getActiveGroups':
        header('Content-Type: application/json');
        if ($groups = $rh->getActiveGroups($userID)) $result = ["ResponseCode" => "OK", "data" => $groups];
        else $result = ["ResponseCode" => "NO_GROUPS"];
        echo json_encode($result);
        break;

    case 'getActiveGroupsWithTasks':
        header('Content-Type: application/json');
        echo json_encode($rh->getActiveGroupsWithTasks($userID));
        break;

    case 'getTaskData':
        header('Content-Type: application/json');
        echo json_encode($rh->getTaskData($userID, $_POST['id']));
        break;

    case 'getTaskDataTaskdetails':
        header('Content-Type: application/json');
        echo json_encode($rh->getTaskDataTaskdetails($userID, $_POST['taskID']));
        break;

    case 'addEntries':
        $id = $_POST['id'];
        $text = $_POST['text'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $monfri = $_POST['monfri'];
        $monsun = $_POST['monsun'];
        if ($_POST['mon'] == 'true' || $monfri == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'mon'); // Monday
        if ($_POST['tue'] == 'true' || $monfri == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'tue'); // Tuesday
        if ($_POST['wed'] == 'true' || $monfri == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'wed'); // Wednesday
        if ($_POST['thu'] == 'true' || $monfri == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'thu'); // Thursday
        if ($_POST['fri'] == 'true' || $monfri == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'fri'); // Friday
        if ($_POST['sat'] == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'sat'); // Saturday
        if ($_POST['sun'] == 'true' || $monsun == 'true') $rh->insertEntry($userID, $id, $text, $start, $end, 'sun'); // Sunday
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK"]);
        break;

    case 'createTimetable':
        header('Content-Type: application/json');
        echo json_encode($rh->createTimetable($userID, $_POST['type'], $_POST['copycheck']));
        break;

    case 'deleteTimetable':
        $rh->deleteTimetable($userID, $_POST['id']);
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK"]);
        break;

    case 'deleteEntry':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->deleteEntry($userID, $_POST['id'])]);
        break;

    case 'getTimetable':
        header('Content-Type: application/json');
        echo json_encode($rh->timetableToJSON($rh->getTimetable($userID, $_POST['type'])));
        break;

    case 'getQueueTasks':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->getQueueTasks($userID)]);
        break;

    case 'deleteQueueTask':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->deleteQueueTask($userID, $_POST['id'])]);
        break;

    case 'addQueueTask':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->addQueueTask($userID, $_POST['text'], $_POST['check'])]);
        break;

    case 'getUnfinishedMorningroutineTasks':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->getUnfinishedMorningroutineTasks($userID)]);
        break;

    case 'getAllMorningroutineTasks':
        header('Content-Type: application/json');
        echo json_encode(["ResponseCode" => "OK", "data" => $rh->getAllMorningroutineTasks($userID)]);
        break;

    case 'completeMorningroutineTask':
        header('Content-Type: application/json');
        echo json_encode($rh->completeMorningroutineTask($userID, $_POST['id']));
        break;

    case 'addMorningroutineTask':
        header('Content-Type: application/json');
        echo json_encode($rh->addMorningroutineTask($userID, $_POST['text']));
        break;

    case 'resetMorningroutine':
        header('Content-Type: application/json');
        echo json_encode($rh->resetMorningroutine($userID));
        break;

    case 'updateMorningroutineOrder':
        header('Content-Type: application/json');
        echo json_encode($rh->updateMorningroutineOrder(explode(',', $_POST['order'])));
        break;

    case 'deleteMorningroutineTask':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteMorningroutineTask($_POST['entryID']));
        break;

    case 'getAppointments':
        header('Content-Type: application/json');
        echo json_encode($rh->getAppointments($userID));
        break;

    case 'getAppointmentsFromMonth':
        header('Content-Type: application/json');
        echo json_encode($rh->getAppointmentsFromMonth($userID, $_POST['month'], $_POST['year']));
        break;

    case 'editAppointment':
        header('Content-Type: application/json');
        echo json_encode($rh->editAppointment($userID, $_POST['id'], $_POST['title'], $_POST['date']));
        break;

    case 'deleteAppointment':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteAppointment($userID, $_POST['id']));
        break;

    case 'addAppointment':
        header('Content-Type: application/json');
        echo json_encode($rh->addAppointment($userID, $_POST['group'], $_POST['date'], $_POST['title'], $_POST['start'], $_POST['end']));
        break;

    case 'getMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->getMotd($userID));
        break;

    case 'editMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->editMotd($userID, $_POST['id'], $_POST['title']));
        break;

    case 'deleteMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteMotd($userID, $_POST['id']));
        break;

    case 'addMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->addMotd($userID, $_POST['group'], $_POST['title']));
        break;

    case 'toggleUnfoldPanel':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleUnfoldPanel($userID, $_POST['type'], $_POST['checked']));
        break;

    case 'toggleActivePanel':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleActivePanel($userID, $_POST['type'], $_POST['checked']));
        break;

    case 'updatePanelOrder':
        header('Content-Type: application/json');
        echo json_encode($rh->updatePanelOrder($userID, explode(',', $_POST['order'])));
        break;

    case 'updateLabelOrder':
        header('Content-Type: application/json');
        echo json_encode($rh->updateLabelOrder(explode(',', $_POST['order'])));
        break;

    case 'createLabel':
        header('Content-Type: application/json');
        echo json_encode($rh->createLabel($userID, $_POST['groupID'], $_POST['title'], $_POST['description'], $_POST['color']));
        break;

    case 'getLabels':
        header('Content-Type: application/json');
        echo json_encode($rh->getLabels($userID, $_POST['groupID']));
        break;

    case 'getLabelsForTask':
        header('Content-Type: application/json');
        echo json_encode($rh->getLabelsForTask($userID, $_POST['groupID'], $_POST['taskID']));
        break;

    case 'deleteLabel':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteLabel($userID, $_POST['groupID'], $_POST['labelID']));
        break;

    case 'updateLabel':
        header('Content-Type: application/json');
        echo json_encode($rh->updateLabel($userID, $_POST['groupID'], $_POST['labelID'], $_POST['title'], $_POST['description'], $_POST['color']));
        break;

    case 'updateTaskLabel':
        header('Content-Type: application/json');
        echo json_encode($rh->updateTaskLabel($userID, $_POST['groupID'], $_POST['taskID'], $_POST['labelID'], $_POST['checkboxChecked']));
        break;

    case 'createTask':
        header('Content-Type: application/json');
        echo json_encode($rh->createTask($userID, $_POST['type'], $_POST['parentID'], $_POST['tasktitle'], $_POST['taskdescription'], $_POST['taskprio']));
        break;

    case 'updateTask':
        header('Content-Type: application/json');
        echo json_encode($rh->updateTask($userID, $_POST['taskID'], $_POST['parentID'], $_POST['tasktitle'], $_POST['taskdescription'], $_POST['taskprio']));
        break;

    case 'createFeedback':
        header('Content-Type: application/json');
        echo json_encode($rh->createFeedback($userID, $_POST['description']));
        break;

    case 'setTaskToOpen':
        header('Content-Type: application/json');
        echo json_encode($rh->setTaskToOpen($userID, $_POST['taskID']));
        break;

    case 'assignTask':
        header('Content-Type: application/json');
        echo json_encode($rh->assignTask($userID, $_POST['taskID']));
        break;

    case 'resolveTask':
        header('Content-Type: application/json');
        echo json_encode($rh->resolveTask($userID, $_POST['taskID']));
        break;

    case 'deleteTask':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteTask($userID, $_POST['taskID']));
        break;

    case 'createComment':
        header('Content-Type: application/json');
        echo json_encode($rh->createComment($userID, $_POST['taskID'], $_POST['description'], $_POST['type']));
        break;

    case 'deleteComment':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteComment($userID, $_POST['commentID']));
        break;

    case 'updateComment':
        header('Content-Type: application/json');
        echo json_encode($rh->updateComment($userID, $_POST['commentID'], $_POST['text']));
        break;

    case 'createGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->createGroup($userID, $_POST['groupName']));
        break;

    case 'deleteGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteGroup($userID, $_POST['groupID']));
        break;

    case 'leaveGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->leaveGroup($userID, $_POST['groupID']));
        break;

    case 'createGroupInvite':
        header('Content-Type: application/json');
        echo json_encode($rh->createGroupInvite($userID, $_POST['groupID'], $_POST['username']));
        break;

    case 'toggleGroupInvites':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleGroupInvites($userID, $_POST['groupID'], $_POST['status']));
        break;

    case 'updateWeatherCity':
        header('Content-Type: application/json');
        echo json_encode($rh->updateWeatherCity($userID, $_POST['city']));
        break;

    case 'getGroupAccess':
        header('Content-Type: application/json');
        echo json_encode($rh->getGroupAccess($userID, $_POST['groupID']));
        break;

    case 'removeUser':
        header('Content-Type: application/json');
        echo json_encode($rh->removeUser($userID, $_POST['groupID'], $_POST['userID']));
        break;

    case 'refreshInvites':
        header('Content-Type: application/json');
        echo json_encode($rh->refreshInvites($userID, $_POST['groupID']));
        break;

    case 'joinGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->joinGroup($userID, $_GET['t']));
        break;

    case 'getGroupIniviteData':
        header('Content-Type: application/json');
        echo json_encode($rh->getGroupIniviteData($userID, $_POST['groupID']));
        break;

    case 'getGroupSettingsData':
        header('Content-Type: application/json');
        echo json_encode($rh->getGroupSettingsData($userID, $_POST['groupID']));
        break;

    case 'updateGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->updateGroup(
            $userID,
            $_POST['groupID'],
            $_POST['groupName'],
            $_POST['groupPriority'],
            $_POST['groupArchiveTime'],
            $_POST['groupUnfolded'],
            $_POST['groupStatus']
        ));
        break;

    case 'getGroupData':
        header('Content-Type: application/json');
        echo json_encode($rh->getGroupData($userID, $_POST['groupID']));
        break;

    default:
        # code...
        break;
}
exit;
