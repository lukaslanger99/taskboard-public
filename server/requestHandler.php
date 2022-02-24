<?php
require('../config.php');

class RequestHandler
{
    private function mysqliConnect()
    {
        $mysqli = new mysqli(SERVER_NAME, USER, PASS, DB);

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        return $mysqli;
    }

    private function mysqliQueryPrepared($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '', $value6 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '' && $value6 == '') {
            } else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '' && $value6 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            } else if ($value3 == '' && $value4 == '' && $value5 == '' && $value6 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            } else  if ($value4 == '' && $value5 == '' && $value6 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            } else  if ($value5 == '' && $value6 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            } else if ($value6 == '') {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            } else {
                mysqli_stmt_bind_param($stmt, "ssssss", $value, $value2, $value3, $value4, $value5, $value6);
            }
            mysqli_stmt_execute($stmt);
        }
    }

    private function mysqliSelectFetchArray($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
            } else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            } else if ($value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            } else  if ($value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            } else  if ($value5 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            } else {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                while ($obj = mysqli_fetch_object($result)) {
                    $data[] = $obj;
                }
            }
            return $data;
        }
    }

    private function mysqliSelectFetchObject($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
            } else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            } else if ($value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            } else  if ($value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            } else  if ($value5 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            } else {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_object($result);
            }
        }
    }

    public function insertEntry($userID, $timetableID, $text, $start, $end, $date, $weekday)
    {
        $this->mysqliQueryPrepared(
            "INSERT INTO timetableentrys (timetableID, timetableText, timetableTimeStart, timetableTimeEnd, timetableDate, timetableOwnerID, timetableWeekday) 
            VALUES ( ?, ?, ?, ?, ?, ?, '$weekday')",
            $timetableID,
            $text,
            $start,
            $end,
            $date,
            $userID
        );
    }

    public function getActiveGroups($userID)
    {
        $sql = "SELECT g.* 
            FROM groups g
            LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
            WHERE  ga.userID = ? AND g.groupState = 'active'
            ORDER BY g.groupPriority DESC";
        $groups = $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        if ($groups) {
            $tasks = $this->mysqliSelectFetchArray(
                "SELECT t.* 
                    FROM tasks t
                    LEFT JOIN groupaccess ga ON t.taskParentID = ga.groupID
                    LEFT JOIN groups g ON g.groupID = ga.groupID
                    WHERE  ga.userID = ? AND g.groupState = 'active' AND t.taskType = 'task' AND NOT t.taskState = 'archived'
                    ORDER BY t.taskParentID DESC",
                $userID
            );
            $json = [];
            $json['groups'] = $groups;
            $json['tasks'] = $tasks;
            return $json;
        }
        return '';
    }

    public function createTimetable($userID, $type, $copyLast)
    {
        if ($copyLast == 'true') {
            $lastTimetableID  = $this->mysqliSelectFetchObject("SELECT MAX(timetableID) as max_number FROM timetables WHERE timetableUserID = ?", $userID);
        }
        $year = date("Y");
        $week = date("W");
        if ($type == 'next') $week = ($week + 1) % 52;
        $this->mysqliQueryPrepared("INSERT INTO timetables (timetableUserID, timetableWeek, timetableYear) VALUES (?, ?, ?)", $userID, $week, $year);
        $timetable = $this->mysqliSelectFetchObject("SELECT * FROM timetables WHERE timetableUserID = ? AND timetableWeek = ? AND timetableYear = ?", $userID, $week, $year);

        if ($lastTimetableID) {
            $entrys = $this->mysqliSelectFetchArray("SELECT * FROM timetableentrys WHERE timetableID = ?", $lastTimetableID->max_number);
            if ($entrys) {
                foreach ($entrys as $entry) {
                    $this->insertEntry(
                        $userID,
                        $timetable->timetableID,
                        $entry->timetableText,
                        $entry->timetableTimeStart,
                        $entry->timetableTimeEnd,
                        date("Y-m-d", strtotime("$entry->timetableDate +7 day")),
                        $entry->timetableWeekday
                    );
                }
            }
        }
    }

    public function deleteTimetable($userID, $timetableID)
    {
        $timetable = $this->mysqliSelectFetchObject("SELECT * FROM timetables WHERE timetableID = ?", $timetableID);
        if ($timetable->timetableUserID == $userID) {
            $this->mysqliQueryPrepared("DELETE FROM timetables WHERE timetableID = ?", $timetableID);
            $this->mysqliQueryPrepared("DELETE FROM timetableentrys WHERE timetableID = ?", $timetableID);
        }
    }

    public function deleteEntry($userID, $entryID)
    {
        $entry = $this->mysqliSelectFetchObject("SELECT * FROM timetableentrys WHERE timetableEntryID = ?", $entryID);
        if ($entry->timetableOwnerID == $userID) {
            $this->mysqliQueryPrepared("DELETE FROM timetableentrys WHERE timetableEntryID = ?", $entryID);
            return $this->timetableToJSON($this->getTimetableByID($userID, $entry->timetableID));
        }
    }

    public function getTimetable($userID, $type)
    {
        $year = date("Y");
        $week = date("W");
        if ($type == 'next') $week = ($week + 1) % 52;

        return $this->mysqliSelectFetchObject(
            "SELECT * FROM timetables WHERE timetableUserID = ? AND timetableWeek = ? AND timetableYear = ?",
            $userID,
            $week,
            $year
        );
    }

    public function getTimetableByID($userID, $timetableID)
    {
        return $this->mysqliSelectFetchObject(
            "SELECT * FROM timetables WHERE timetableUserID = ? AND timetableID = ?",
            $userID,
            $timetableID
        );
    }

    public function timetableToJSON($timetable)
    {
        if ($timetable) {
            $timetableID = $timetable->timetableID;
            $json['id'] = $timetableID;
            $json['week'] = $timetable->timetableWeek;

            $tasks = $this->mysqliSelectFetchArray("SELECT * FROM timetableentrys WHERE timetableID = ? ORDER BY timetableTimeStart", $timetableID);
            if ($tasks) {
                foreach ($tasks as $task) {
                    $json['tasks'][] = $task;
                }
            }
            return $json;
        }
        return 0;
    }
}
