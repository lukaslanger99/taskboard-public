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
        echo json_encode($rh->getActiveGroups($userID));
        break;

    case 'getTaskData':
        header('Content-Type: application/json');
        echo json_encode($rh->getTaskData($_POST['id']));
        break;

    case 'addEntrys':
        $id = intval($_GET['id']);
        $text = $_POST['text'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $monfri = $_POST['monfri'];
        $monsun = $_POST['monsun'];

        $timetable = $rh->getTimetableByID($userID, $id);
        $week = $timetable->timetableWeek;
        $year = $timetable->timetableYear;

        // Monday
        if ($_POST['mon'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'mon');
        }
        // Tuesday
        if ($_POST['tue'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'tue');
        }
        // Wednesday
        if ($_POST['wed'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'wed');
        }
        // Thursday
        if ($_POST['thu'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'thu');
        }
        // Friday
        if ($_POST['fri'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'fri');
        }
        // Saturday
        if ($_POST['sat'] == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'sat');
        }
        // Sunday
        if ($_POST['sun'] == 'true' || $monsun == 'true') {
            $rh->insertEntry($userID, $id, $text, $start, $end, 'sun');
        }
        header('Content-Type: application/json');
        echo json_encode($rh->timetableToJSON($timetable));
        break;

    case 'createTimetable':
        header('Content-Type: application/json');
        $rh->createTimetable($userID, $_GET['type'], $_GET['copycheck']);
        echo json_encode($rh->timetableToJSON($rh->getTimetable($userID, $_GET['type'])));
        break;

    case 'deleteTimetable':
        $rh->deleteTimetable($userID, $_GET['id']);
        header('Content-Type: application/json');
        echo json_encode(0);
        break;

    case 'deleteEntry':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteEntry($userID, $_GET['id']));
        break;

    case 'getTimetable':
        header('Content-Type: application/json');
        echo json_encode($rh->timetableToJSON($rh->getTimetable($userID, $_GET['type'])));
        break;

    case 'getQueueTasks':
        header('Content-Type: application/json');
        echo json_encode($rh->getQueueTasks($userID));
        break;

    case 'deleteQueueTask':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteQueueTask($userID, $_GET['id']));
        break;

    case 'addQueueTask':
        header('Content-Type: application/json');
        echo json_encode($rh->addQueueTask($userID, $_POST['text'], $_POST['check']));
        break;

    case 'getAppointments':
        header('Content-Type: application/json');
        echo json_encode($rh->getAppointments($userID));
        break;

    case 'editAppointment':
        header('Content-Type: application/json');
        echo json_encode($rh->editAppointment($userID, $_GET['id'], $_POST['title'], $_POST['date']));
        break;

    case 'deleteAppointment':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteAppointment($userID, $_GET['id']));
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
        echo json_encode($rh->editMotd($userID, $_GET['id'], $_POST['title']));
        break;

    case 'deleteMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->deleteMotd($userID, $_GET['id']));
        break;

    case 'addMotd':
        header('Content-Type: application/json');
        echo json_encode($rh->addMotd($userID, $_POST['group'], $_POST['title']));
        break;

    case 'toggleUnfoldPanel':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleUnfoldPanel($userID, $_GET['type'], $_GET['checked']));
        break;

    case 'toggleUnfoldGroup':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleUnfoldGroup($userID, $_GET['id'], $_GET['checked']));
        break;

    case 'toggleActivePanel':
        header('Content-Type: application/json');
        echo json_encode($rh->toggleActivePanel($userID, $_GET['type'], $_GET['checked']));
        break;

    case 'updatePanelOrder':
        header('Content-Type: application/json');
        echo json_encode($rh->updatePanelOrder($userID, explode(',', $_POST['order'])));
        break;

    default:
        # code...
        break;
}
exit;
