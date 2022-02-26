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

        $dateTime = new DateTime();

        // Monday
        if ($_POST['mon'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 1); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'mon');
        }
        // Tuesday
        if ($_POST['tue'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 2); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'tue');
        }
        // Wednesday
        if ($_POST['wed'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 3); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'wed');
        }
        // Thursday
        if ($_POST['thu'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 4); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'thu');
        }
        // Friday
        if ($_POST['fri'] == 'true' || $monfri == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 5); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'fri');
        }
        // Saturday
        if ($_POST['sat'] == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 6); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'sat');
        }
        // Sunday
        if ($_POST['sun'] == 'true' || $monsun == 'true') {
            $date = $dateTime->setISODate($year, $week, 7); //year , week num , day
            $date = $date->format('Y-m-d'); // 2022-02-22
            $rh->insertEntry($userID, $id, $text, $start, $end, $date, 'sun');
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

    default:
        # code...
        break;
}
exit;
