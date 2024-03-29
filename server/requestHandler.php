<?php
require('../config.php');
require_once('../php/Parsedown.php');

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

    private function mysqliQueryPrepared($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
            mysqli_stmt_execute($stmt);
        }
    }

    private function mysqliSelectFetchArray($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
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

    private function mysqliSelectFetchObject($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            return "?error=sqlerror";
        } else {
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_object($result);
            }
        }
    }

    public function insertEntry($userID, $timetableID, $text, $start, $end, $weekday)
    {
        $this->mysqliQueryPrepared(
            "INSERT INTO timetableentrys (timetableID, timetableText, timetableTimeStart, timetableTimeEnd, timetableOwnerID, timetableWeekday) 
            VALUES ( ?, ?, ?, ?, ?, '$weekday')",
            $timetableID,
            $text,
            $start,
            $end,
            $userID
        );
    }

    public function getActiveGroups($userID)
    {
        $sql = "SELECT g.* 
            FROM groups g
            LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
            WHERE  ga.userID = ? AND g.groupStatus = 'active'
            ORDER BY g.groupPriority DESC";
        return $this->mysqliSelectFetchArray($sql, $userID);
    }

    private function archiveCheck($groupID, $dateDiff)
    {
        $groupData = $this->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupID = ?", $groupID);
        return $dateDiff >= $groupData->groupArchiveTime;
    }

    private function moveToArchive($taskID)
    {
        $this->mysqliQueryPrepared("UPDATE tasks SET taskStatus = 'archived' WHERE taskID = ?", $taskID);
    }

    private function getUsernameShortByUserID($userID)
    {
        $userData = $this->mysqliSelectFetchObject("SELECT userNameShort FROM users WHERE userID = ?", $userID);
        return $userData->userNameShort;
    }

    public function getActiveGroupsWithTasks($userID)
    {
        if (!$groups = $this->getActiveGroups($userID)) return ["ResponseCode" => "NO_GROUPS"];
        foreach ($groups as $group) {
            $groupID = $group->groupID;
            $group->labels = $this->mysqliSelectFetchArray("SELECT * FROM labels WHERE labelGroupID = ? ORDER BY labelOrder", $groupID);
            $unfoldedStatus = $this->mysqliSelectFetchObject("SELECT groupUnfolded FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID);
            $group->unfolded = $unfoldedStatus->groupUnfolded;
            $tasks = $this->mysqliSelectFetchArray(
                "SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND NOT taskStatus = 'archived' ORDER BY taskPriority DESC, taskID ",
                $groupID
            );
            if ($tasks) {
                foreach ($tasks as $task) {
                    if ($task->taskStatus == 'open') $dateDiff = $this->getDateDifference($task->taskDateCreated);
                    else if ($task->taskStatus == 'resolved') $dateDiff = $this->getDateDifference($task->taskDateResolved);

                    if ($task->taskStatus == 'resolved' && $this->archiveCheck($task->taskParentID, $dateDiff)) {
                        $this->moveToArchive($task->taskID);
                        unset($task);
                    }
                    $task->taskAssignee = $this->getUsernameShortByUserID($task->taskAssignee);
                    $task->dateDiff = $dateDiff;
                    $task->numberOfSubtasks = $this->getNumberOfSubtasks($task->taskID);
                    if ($labelIDs = $this->mysqliSelectFetchArray("SELECT labelID FROM tasklabels WHERE taskID = ?", $task->taskID)) {
                        $labels = [];
                        foreach ($labelIDs as $labelID) {
                            $labels[] = $this->mysqliSelectFetchObject("SELECT * FROM labels WHERE labelID = ?", $labelID->labelID);
                        }
                        $task->activeLabels = $labels;
                    }
                }
                $group->unarchivedTasks = $tasks;
            }
        }
        return ["ResponseCode" => "OK", "data" => $groups];
    }

    public function getTaskData($userID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        return ["ResponseCode" => "OK", "data" => $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID)];
    }

    private function formatDate($date)
    {
        $dateDiffHours = round((strtotime($this->getCurrentTimestamp()) - strtotime($date)) / 3600);
        $dateDiffDays = round($dateDiffHours / 24);
        if ($dateDiffHours < 1) return "less than 1 hour ago";
        else if ($dateDiffHours == 1) return "1 hour ago";
        else if ($dateDiffHours > 1 && $dateDiffDays < 1) return $dateDiffHours . " hours ago";
        else if ($dateDiffDays == 1) return "1 day ago";
        else if (7 > $dateDiffDays  && $dateDiffDays > 1) return $dateDiffDays . " days ago";
        return date("j/M/Y G:i", strtotime($date));
    }

    public function getTaskDataTaskdetails($userID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        if (!$taskData = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID)) return ["ResponseCode" => "NO_TASK_FOUND"];
        $parsedown = new Parsedown();
        ### Parents ###
        $parents = [];
        $parentTask = $taskData;
        $parents[] = ["id" => $parentTask->taskID, "name" => "", "type" => "task"];
        if ($parentTask->taskType == 'subtask') {
            do {
                $parentTask = $this->mysqliSelectFetchObject("SELECT taskID, taskType, taskParentID FROM tasks WHERE taskID = ?", $parentTask->taskParentID);
                $parents[] = ["id" => $parentTask->taskID, "name" => "", "type" => "task"];
            } while ($parentTask->taskType == "subtask");
        }
        $group = $this->mysqliSelectFetchObject("SELECT groupID, groupName FROM groups WHERE groupID = ?", $parentTask->taskParentID);
        $parents[] = ["id" => $group->groupID, "name" => $group->groupName, "type" => "group"];
        $taskData->parents = array_reverse($parents);
        ###
        if (($subtasks = $this->getSubtasks($userID, $taskID)) != "NO_SUBTASKS") $taskData->subtasks = $subtasks;
        if ($comments = $this->mysqliSelectFetchArray("SELECT * FROM comments WHERE commentTaskID = ? ORDER BY commentDate DESC", $taskID)) {
            foreach ($comments as $comment) {
                $comment->commentOwner = ($comment->commentAuthor == $userID);
                $comment->commentAuthor = $this->getUsernameByID($comment->commentAuthor);
                $comment->commentDateFormatted = $this->formatDate($comment->commentDate);
                $comment->descriptionWithMakros = $parsedown->text($comment->commentDescription);
            }
            $taskData->activity = $comments;
        }
        $taskData->assignee = $this->getUsernameByID($taskData->taskAssignee);
        $taskData->reporter = $this->getUsernameByID($taskData->taskReporter);
        $taskData->datesFormatted = [
            "dateCreatedFormatted" => $this->formatDate($taskData->taskDateCreated),
            "dateUpdatedFormatted" => $this->formatDate($taskData->taskDateUpdated),
            "dateResolvedFormatted" => $this->formatDate($taskData->taskDateResolved)
        ];
        $taskData->descriptionWithMakros = $parsedown->text($taskData->taskDescription);
        return ["ResponseCode" => "OK", "data" => $taskData];
    }

    public function createTimetable($userID, $type, $copyLast)
    {
        if ($copyLast == 'true') {
            $lastTimetableID  = $this->mysqliSelectFetchObject(
                "SELECT MAX(timetableID) as max_number FROM timetables WHERE timetableUserID = ?",
                $userID
            );
        }
        $year = date("Y");
        $week = date("W");
        if ($type == 'next') $week = ($week + 1) % 52;
        $this->mysqliQueryPrepared(
            "INSERT INTO timetables (timetableUserID, timetableWeek, timetableYear) VALUES (?, ?, ?)",
            $userID,
            $week,
            $year
        );
        $timetable = $this->mysqliSelectFetchObject(
            "SELECT * FROM timetables WHERE timetableUserID = ? AND timetableWeek = ? AND timetableYear = ?",
            $userID,
            $week,
            $year
        );

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
                        $entry->timetableWeekday
                    );
                }
            }
        }
        return ["ResponseCode" => "OK"];
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
            return ["ResponseCode" => "OK", "data" => $json];
        }
        return ["ResponseCode" => "NO_TIMETABLE"];
    }

    public function getQueueTasks($userID)
    {
        $sql = "SELECT * FROM messages WHERE messageOwner = ? AND messageType = 'queue' ORDER BY messagePrio DESC, messageID";
        return $this->mysqliSelectFetchArray($sql, $userID);
    }

    public function deleteQueueTask($userID, $id)
    {
        $messageData = $this->mysqliSelectFetchObject("SELECT * FROM messages WHERE messageID = ?", $id);
        if ($messageData->messageOwner == $userID) {
            $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageID = ?", $id);
        }
        return $this->getQueueTasks($userID);
    }

    public function addQueueTask($userID, $text, $check)
    {
        if ($text) {
            ($check == 'true') ? $prio = 2 : $prio = 1;
            $queueItems = explode(",", $text);
            foreach ($queueItems as $item) {
                if (!empty(trim($item))) {
                    $sql = "INSERT INTO messages (messageOwner, messageType, messageTitle, messagePrio) VALUES (?, 'queue', ?, ?)";
                    $this->mysqliQueryPrepared($sql, $userID, $item, $prio);
                }
            }
        }
        return $this->getQueueTasks($userID);
    }

    public function getUnfinishedMorningroutineTasks($userID)
    {
        $sql = "SELECT * FROM morningroutine WHERE entryUserID = ? AND entryDate < ? ORDER BY entryOrder";
        return $this->mysqliSelectFetchArray($sql, $userID, date("Y-m-d"));
    }

    public function getAllMorningroutineTasks($userID)
    {
        $sql = "SELECT * FROM morningroutine WHERE entryUserID = ? ORDER BY entryOrder";
        return $this->mysqliSelectFetchArray($sql, $userID);
    }

    public function completeMorningroutineTask($userID, $id)
    {
        $entryData = $this->mysqliSelectFetchObject("SELECT * FROM morningroutine WHERE entryID = ?", $id);
        if ($entryData->entryUserID == $userID) {
            $this->mysqliQueryPrepared("UPDATE morningroutine SET entryDate = NOW() WHERE entryID = ?", $id);
            return ["ResponseCode" => "OK"];
        }
        return ["ResponseCode" => "NO_MORNINGROUTINE"];
    }

    public function addMorningroutineTask($userID, $text)
    {
        if ($text) {
            $taskCount = $this->mysqliSelectFetchObject(
                "SELECT COUNT(*) as number FROM morningroutine WHERE entryUserID = ?",
                $userID
            );
            $taskCount = $taskCount->number;
            $tasks = explode(",", $text);
            foreach ($tasks as $taskTitle) {
                if (!empty(trim($taskTitle))) {
                    $sql = "INSERT INTO morningroutine (entryUserID, entryTitle, entryOrder) VALUES (?, ?, ?)";
                    $this->mysqliQueryPrepared($sql, $userID, $taskTitle, ++$taskCount);
                }
            }
        }
        return ["ResponseCode" => "OK"];
    }

    public function resetMorningroutine($userID)
    {
        $this->mysqliQueryPrepared("UPDATE morningroutine SET entryDate = '0000-00-00' WHERE entryUserID = ?", $userID);
        return ["ResponseCode" => "OK"];
    }

    public function updateMorningroutineOrder($entryIDs)
    {
        for ($i = 0; $i < count($entryIDs); $i++) {
            $this->mysqliQueryPrepared("UPDATE morningroutine SET entryOrder = ? WHERE entryID = ?", ($i + 1), (int) $entryIDs[$i]);
        }
        return ["ResponseCode" => "OK"];
    }

    public function deleteMorningroutineTask($entryID)
    {
        $this->mysqliQueryPrepared("DELETE FROM morningroutine WHERE entryID = ?", $entryID);
        return ["ResponseCode" => "OK"];
    }

    public function getAppointments($userID)
    {
        $sql = "SELECT m.messageID, m.messageOwner, m.messageGroup, m.messageTitle, m.messageDate, m.messageStart, m.messageEnd
        FROM messages m
            LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
        WHERE  ga.userID = ? AND m.messageType = 'appointment' AND messageDate >= CURRENT_DATE
        ORDER BY m.messageDate, messageStart";
        $data = $this->mysqliSelectFetchArray($sql, $userID);
        if ($data) {
            for ($i = 0; $i < 10 && $i < sizeof($data); $i++) {
                $appointment = $data[$i];
                $appointment->currentMonth = (date("m", strtotime($appointment->messageDate)) == date("m"));
                $appointment->messageRedRounded = new DateTime($appointment->messageDate) > new DateTime($this->getUserLastMotd($userID));
                $appointment->messageDateFormFormat = date("Y-m-d", strtotime($appointment->messageDate));
                $appointment->messageDate = date("d.m.y", strtotime($appointment->messageDate));
                $appointment->messageOwnerName = $this->getUsernameByID($appointment->messageOwner);
                $appointment->messageGroupName = $this->getGroupNameByID($appointment->messageGroup);
                $appointment->messageTitleFormated = $this->addTagsToUrlsInString($appointment->messageTitle);
                $appointment->messagePermission = ($userID == $appointment->messageOwner || $this->groupOwnerCheck($appointment->messageGroup, $userID) || $userID == 1);
                $appointment->timeStart = $appointment->messageStart;
                $appointment->timeEnd = $appointment->messageEnd;
                $appointmentlist[] = $appointment;
            }
            return ["ResponseCode" => "OK", "data" => $appointmentlist];
        }
        return ["ResponseCode" => "NO_APPOINTMENTS"];
    }

    public function getAppointmentsFromMonth($userID, $month, $year)
    {
        $monthKey = $year . '-' . (((int) $month < 10) ? '0' : '') . $month;
        $sql = "SELECT m.messageID, m.messageOwner, m.messageGroup, m.messageTitle, m.messageDate, m.messageStart, m.messageEnd
        FROM messages m
            LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
        WHERE  ga.userID = ? AND m.messageType = 'appointment'
        ORDER BY m.messageDate";
        $data = $this->mysqliSelectFetchArray($sql, $userID);
        if ($data) {
            foreach ($data as $appointment) {
                $dateCheck = date("Y-m", strtotime($appointment->messageDate));
                $appointment->currentMonth = (date("m", strtotime($appointment->messageDate)) == date("m"));
                $appointment->messageRedRounded = new DateTime($appointment->messageDate) > new DateTime($this->getUserLastMotd($userID));
                $appointment->messageDateFormFormat = date("Y-m-d", strtotime($appointment->messageDate));
                $appointment->messageDate = date("d.m.y", strtotime($appointment->messageDate));
                $appointment->messageOwnerName = $this->getUsernameByID($appointment->messageOwner);
                $appointment->messageGroupName = $this->getGroupNameByID($appointment->messageGroup);
                $appointment->messageTitleFormated = $this->addTagsToUrlsInString($appointment->messageTitle);
                $appointment->messagePermission = ($userID == $appointment->messageOwner || $this->groupOwnerCheck($appointment->messageGroup, $userID) || $userID == 1);
                $appointment->timeStart = $appointment->messageStart;
                $appointment->timeEnd = $appointment->messageEnd;
                if ($dateCheck == $monthKey) $appointmentlist[] = $appointment;
            }
            return ["ResponseCode" => "OK", "data" => $appointmentlist];
        }
        return ["ResponseCode" => "NO_APPOINTMENTS"];
    }

    public function editAppointment($userID, $messageID, $title, $date)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $messageID);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "UPDATE messages SET messageTitle = ?, messageDate = ? WHERE messageID = ?;";
            $this->mysqliQueryPrepared($sql, $title, $date, $messageID);
        }
        return $this->getAppointments($userID);
    }

    public function deleteAppointment($userID, $id)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $id);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "DELETE FROM messages WHERE messageID = ?";
            $this->mysqliQueryPrepared($sql, $id);
        }
        return $this->getAppointments($userID);
    }

    public function addAppointment($userID, $groupID, $date, $title, $start, $end)
    {
        if (!$end) $end = '-';
        $sql = "INSERT INTO messages 
            (messageOwner, messageGroup, messageType, messageTitle, messageDate, messageStart, messageEnd) 
            VALUES (?, ?, ?, ?, ?, ?, ?);";
        $this->mysqliQueryPrepared($sql, $userID, (int) $groupID, 'appointment', str_replace(array("\r", "\n"), " ", $title), $date, $start, $end);
        return ["ResponseCode" => "OK"];
    }

    public function getMotd($userID)
    {
        $sql = "SELECT m.* 
        FROM messages m
            LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
        WHERE  ga.userID = ? AND m.messageType = 'motd'
        ORDER BY m.messageDate DESC, messageID DESC";
        $data = $this->mysqliSelectFetchArray($sql, $userID);
        if ($data) {
            foreach ($data as $motd) {
                $motd->messageRedRounded = new DateTime($motd->messageDate) > new DateTime($this->getUserLastMotd($userID));
                $motd->messageDate = date("d.m.y", strtotime($motd->messageDate));
                $motd->messageOwnerName = $this->getUsernameByID($motd->messageOwner);
                $motd->messageGroupName = $this->getGroupNameByID($motd->messageGroup);
                $motd->messageTitleFormated = $this->addTagsToUrlsInString($motd->messageTitle);
                $motd->messagePermission = ($userID == $motd->messageOwner || $this->groupOwnerCheck($motd->messageGroup, $userID) || $userID == 1);
            }
            return ["ResponseCode" => "OK", "data" => $data];
        }
        return ["ResponseCode" => "NO_MOTD"];
    }

    public function editMotd($userID, $messageID, $title)
    {
        $message = $this->mysqliSelectFetchObject(
            "SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",
            $messageID
        );
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "UPDATE messages SET messageTitle = ? WHERE messageID = ?;";
            $this->mysqliQueryPrepared($sql, $title, $messageID);
        }
        return $this->getMotd($userID);
    }

    public function deleteMotd($userID, $messageID)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $messageID);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "DELETE FROM messages WHERE messageID = ?";
            $this->mysqliQueryPrepared($sql, $messageID);
        }
        return $this->getMotd($userID);
    }

    public function addMotd($userID, $groupID, $title)
    {
        $sql = "INSERT INTO messages (messageOwner, messageGroup, messageType, messageTitle) VALUES (?, ?, 'motd', ?);";
        $this->mysqliQueryPrepared($sql, $userID, (int) $groupID, str_replace(array("\r", "\n"), " ", $title));
        return $this->getMotd($userID);
    }

    public function toggleUnfoldPanel($userID, $type, $checked)
    {
        if ($type == 'motd') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelMOTDUnfolded = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'appointment') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelAppointmentUnfolded = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'queue') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelQueueUnfolded = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'weather') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelWeatherUnfolded = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'timetable') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelTimetableUnfolded = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'morningroutine') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelMorningroutineUnfolded = ? WHERE userID = ?", $checked, $userID);
        }
        return ["ResponseCode" => "OK"];
    }

    public function toggleActivePanel($userID, $type, $checked)
    {
        if ($type == 'motd') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelMOTD = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'appointment') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelAppointment = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'queue') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelQueue = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'weather') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelWeather = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'timetable') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelTimetable = ? WHERE userID = ?", $checked, $userID);
        } else if ($type == 'morningroutine') {
            $this->mysqliQueryPrepared("UPDATE panels SET panelMorningroutine = ? WHERE userID = ?", $checked, $userID);
        }
        return ["ResponseCode" => "OK"];
    }

    public function updatePanelOrder($userID, $names)
    {
        for ($i = 0; $i < count($names); $i++) {
            if ($names[$i] == 'motd') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelMOTDOrder = ? WHERE userID = ?", ($i + 1), $userID);
            } else if ($names[$i] == 'appointment') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelAppointmentOrder = ? WHERE userID = ?", ($i + 1), $userID);
            } else if ($names[$i] == 'queue') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelQueueOrder = ? WHERE userID = ?", ($i + 1), $userID);
            } else if ($names[$i] == 'weather') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelWeatherOrder = ? WHERE userID = ?", ($i + 1), $userID);
            } else if ($names[$i] == 'timetable') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelTimetableOrder = ? WHERE userID = ?", ($i + 1), $userID);
            } else if ($names[$i] == 'morningroutine') {
                $this->mysqliQueryPrepared("UPDATE panels SET panelMorningroutineOrder = ? WHERE userID = ?", ($i + 1), $userID);
            }
        }
        return ["ResponseCode" => "OK"];
    }

    public function updateLabelOrder($labelIDs)
    {
        for ($i = 0; $i < count($labelIDs); $i++) {
            $this->mysqliQueryPrepared("UPDATE labels SET labelOrder = ? WHERE labelID = ?", ($i + 1), (int) $labelIDs[$i]);
        }
        return ["ResponseCode" => "OK"];
    }

    private function getUsernameByID($userID)
    {
        if (!$userID) return 'unknown';
        $data = $this->mysqliSelectFetchObject("SELECT * FROM users WHERE userID = ?", $userID);
        return $data->userName;
    }

    private function getGroupNameByID($groupID)
    {
        if (!$groupID) return 'unknown';
        $data = $this->mysqliSelectFetchObject("SELECT groupName FROM groups WHERE groupID = ?", $groupID);
        return $data->groupName;
    }

    private function addTagsToUrlsInString($string)
    {
        $words = explode(' ', $string);
        foreach ($words as &$word) {
            if (filter_var($word, FILTER_VALIDATE_URL)) {
                $word = '<a style="text-decoration:underline;" href="' . $word . '" target="_blank">' . $word . '</a>';
            }
        }
        return implode(' ', $words);
    }

    private function groupOwnerCheck($groupID, $userID)
    {
        $groupOwnerID = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $groupOwnerID->groupOwner == $userID;
    }

    private function checkGroupPermission($userID, $groupID)
    {
        $groupAccess =  $this->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID);
        return ($userID == 1 || $groupAccess) ? true : false;
    }

    private function getUserLastMotd($userID)
    {
        $data = $this->mysqliSelectFetchObject("SELECT userLastMotd FROM users WHERE userID = ?", $userID);
        return $data->userLastMotd;
    }

    public function createLabel($userID, $groupID, $title, $description, $color)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return "NO_ACCESS";
        $count = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM labels WHERE labelGroupID = ?", $groupID);
        $sql = "INSERT INTO labels (labelName, labelDescription, labelColor, labelGroupID, labelOrder) VALUES (?, ?, ?, ?, ?)";
        $this->mysqliQueryPrepared($sql, $title, $description, $color, $groupID, ($count->number + 1));
        return ["ResponseCode" => "OK"];
    }

    public function getLabels($userID, $groupID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $data = $this->mysqliSelectFetchArray("SELECT labelID, labelName, labelDescription, labelColor FROM labels WHERE labelGroupID = ? ORDER BY labelOrder", $groupID);
        if ($data) return ["ResponseCode" => "OK", "data" => $data];
        return ["ResponseCode" => "NO_LABELS"];
    }

    public function getLabelsForTask($userID, $groupID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $labels = $this->mysqliSelectFetchArray("SELECT labelID, labelName, labelDescription, labelColor FROM labels WHERE labelGroupID = ? ORDER BY labelOrder", $groupID);
        if ($labels) {
            foreach ($labels as $label) {
                $checkIfLabelIsActiveForTask = $this->mysqliSelectFetchObject("SELECT entryID FROM tasklabels WHERE taskID = ? AND labelID = ?", $taskID, $label->labelID);
                if ($checkIfLabelIsActiveForTask) $label->isUsed = true;
            }
            return ["ResponseCode" => "OK", "data" => $labels];
        }
        return ["ResponseCode" => "NO_LABELS"];
    }

    public function deleteLabel($userID, $groupID, $labelID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("DELETE FROM labels WHERE labelID = ?", $labelID);
        return ["ResponseCode" => "OK"];
    }

    public function updateLabel($userID, $groupID, $labelID, $title, $description, $color)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $sql = "UPDATE labels SET
            labelName = ?,
            labeLDescription = ?,
            labelColor = ?
            WHERE labelID = ?";
        $this->mysqliQueryPrepared($sql, $title, $description, $color, $labelID);
        return ["ResponseCode" => "OK"];
    }

    public function updateTaskLabel($userID, $groupID, $taskID, $labelID, $checkboxChecked)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        if ($checkboxChecked == 'true') {
            $this->mysqliQueryPrepared("INSERT INTO tasklabels (labelID, taskID) VALUES (?, ?)", $labelID, $taskID);
        } else {
            $this->mysqliQueryPrepared("DELETE FROM tasklabels WHERE labelID = ? AND taskID = ?", $labelID, $taskID);
        }
        $this->mysqliQueryPrepared("UPDATE tasks SET taskDateUpdated = ? WHERE tasKID = ?", $this->getCurrentTimestamp(), $taskID);
        return ["ResponseCode" => "OK"];
    }

    public function createTask($userID, $type, $parentID, $title, $description, $prio, $reporter = 0)
    {
        if ($type == 'task' && !$this->checkGroupPermission($userID, $parentID)) return ["ResponseCode" => "NO_ACCESS"];
        if ($type == 'subtask' && !$this->checkGroupPermission($userID, $this->getGroupIDOfTask($parentID))) return ["ResponseCode" => "NO_ACCESS"];
        $sql = "INSERT INTO tasks 
            (taskType, taskParentID, taskPriority, taskPriorityColor, taskTitle, taskDescription, taskStatus, taskReporter, taskDateCreated) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->mysqliQueryPrepared(
            $sql,
            $type,
            $parentID,
            $prio,
            $this->getPriorityColor($prio),
            $title,
            $description,
            'open',
            (!$reporter) ? $userID : $reporter,
            date('Y-m-d H:i')
        );
        return ["ResponseCode" => "OK"];
    }

    public function updateTask($userID, $taskID, $parentID, $title, $description, $prio)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        $oldTaskData = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);
        $priority = (int) $prio;

        if ($oldTaskData->taskPriority != $priority) $this->createComment(
            $userID,
            $taskID,
            "PRIORITY[" . $oldTaskData->taskPriority . " -> " . $priority . "]",
            "history"
        );
        if ($oldTaskData->taskType == 'task' && $oldTaskData->taskParentID != $parentID) {
            $this->createComment(
                $userID,
                $taskID,
                'GROUP[' . $this->getGroupNameByID($oldTaskData->taskParentID) . ' -> ' . $this->getGroupNameByID($parentID) . ']',
                "history"
            );
        }
        if ($oldTaskData->taskTitle != $title) $this->createComment(
            $userID,
            $taskID,
            'TITLE[' . $oldTaskData->taskTitle . ' -> ' . $title . ']',
            "history"
        );
        if ($oldTaskData->taskDescription != $description) $this->createComment(
            $userID,
            $taskID,
            'DESCRIPTION[' . $oldTaskData->taskDescription . ' -> ' . $description . ']',
            "history"
        );
        $sql = "UPDATE tasks SET 
            taskParentID = ?,
            taskPriority = ?,
            taskPriorityColor = ?,
            taskTitle = ?,
            taskDescription = ?,
            taskDateUpdated = ?
            WHERE taskID = ?";
        $this->mysqliQueryPrepared($sql, $parentID, $priority, $this->getPriorityColor($priority), $title, $description, $this->getCurrentTimestamp(), $taskID);
        return ["ResponseCode" => "OK"];
    }

    public function createFeedback($userID, $description)
    {
        $title = $this->getUsernameByID($userID) . " - " . $this->getCurrentTimestamp();
        return $this->createTask(1, 'task', 67, $title, $description, 1, $userID);
    }

    private function getPriorityColor($priority)
    {
        $colors = ['green', '#ffcc00', 'red'];
        return $colors[$priority - 1];
    }

    private function getSubtasks($userID, $parentID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($parentID))) return ["ResponseCode" => "NO_ACCESS"];
        if ($subtasks = $this->mysqliSelectFetchArray(
            "SELECT * FROM tasks WHERE taskType = ? AND taskParentID = ? ORDER BY taskStatus, taskPriority DESC, taskID",
            'subtask',
            $parentID
        )) {
            foreach ($subtasks as $task) {
                if ($subtaskCount = $this->getNumberOfSubtasks($task->taskID)) $task->subtaskCount = $subtaskCount;
                if ($task->taskAssignee) $task->assigneeNameShort = $this->getUserNameShort($task->taskAssignee);
                if ($task->taskStatus == 'open') $task->daysActive = $this->getDateDifference($task->taskDateCreated);
            }
            return $subtasks;
        }
        return ["ResponseCode" => "NO_SUBTASKS"];
    }

    private function getGroupIDOfTask($taskID)
    {
        $taskData = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);
        if ($taskData->taskType == 'task') return $taskData->taskParentID;
        do {
            $taskData = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskData->taskParentID);
        } while ($taskData->taskType == 'subtask');
        return $taskData->taskParentID;
    }

    private function getNumberOfSubtasks($taskID)
    {
        $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ? AND taskStatus = 'open'";
        if ($data = $this->mysqliSelectFetchObject($sql, $taskID)) return $data->number;
        return 0;
    }

    private function getUserNameShort($userID)
    {
        if (!$userData = $this->mysqliSelectFetchObject("SELECT userNameShort FROM users WHERE userID = ?", $userID)) return 'unknown';
        return $userData->userNameShort;
    }

    private function getDateDifference($date)
    {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d H:i')))->format('%r%a');
    }

    public function setTaskToOpen($userID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("UPDATE tasks SET taskStatus = 'open', taskAssignee = '' WHERE taskID = ?", $taskID);
        return ["ResponseCode" => "OK"];
    }

    public function assignTask($userID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("UPDATE tasks SET taskAssignee = ?, taskDateUpdated = ? WHERE taskID = ?", $userID, $this->getCurrentTimestamp(), $taskID);
        return ["ResponseCode" => "OK"];
    }

    public function resolveTask($userID, $taskID)
    {
        if (!$this->deleteTaskPermission($taskID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $sql = "SELECT COUNT(*) as number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ? AND taskStatus = 'open'";
        $subtasks = $this->mysqliSelectFetchObject($sql, $taskID);
        if ($subtasks->number > 0) return "UNRESOLVED_SUBTASKS";
        $sql = "UPDATE tasks SET taskStatus = 'resolved', taskDateUpdated = ?, taskDateResolved = ? WHERE taskID = ?";
        $this->mysqliQueryPrepared($sql, $this->getCurrentTimestamp(), $this->getCurrentTimestamp(), $taskID);
        return ["ResponseCode" => "OK"];
    }

    private function deleteTaskPermission($taskID, $userID)
    {
        return $this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID));
    }

    public function deleteTask($userID, $taskID)
    {
        if (!$this->deleteTaskPermission($taskID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $taskData = $this->mysqliSelectFetchObject("SELECT taskType, taskParentID FROM tasks WHERE taskID = ?", $taskID);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskID = ?", $taskID);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?", $taskID);
        $this->mysqliQueryPrepared("DELETE FROM comments WHERE commentTaskID = ?", $taskID);
        $this->mysqliQueryPrepared("DELETE FROM tasklabels WHERE taskID = ?", $taskID);
        if ($taskData->taskType == 'task') $action = "groupDetails";
        else if ($taskData->taskType == 'subtask') $action = "taskDetails";
        $location = DIR_SYSTEM . "php/details.php?action=" . $action . "&id=" . $taskData->taskParentID . "&success=deletesubtask";
        return ["ResponseCode" => "OK", "data" => $location];
    }

    public function createComment($userID, $taskID, $description, $type)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($taskID))) return ["ResponseCode" => "NO_ACCESS"];
        $timestamp = $this->getCurrentTimestamp();
        $sql = "INSERT INTO comments (commentTaskID, commentAuthor, commentDescription, commentDate, commentType) VALUES (?, ?, ?, ?, ?)";
        $this->mysqliQueryPrepared($sql, $taskID, $userID, $description, $timestamp, $type);
        return ["ResponseCode" => "OK"];
    }

    public function deleteComment($userID, $commentID)
    {
        $commentData = $this->mysqliSelectFetchObject("SELECT commentTaskID FROM comments WHERE commentID = ?", $commentID);
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($commentData->commentTaskID))) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("DELETE FROM comments WHERE commentID = ?", $commentID);
        return ["ResponseCode" => "OK"];
    }

    public function updateComment($userID, $commentID, $text)
    {
        $commentData = $this->mysqliSelectFetchObject("SELECT commentTaskID FROM comments WHERE commentID = ?", $commentID);
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfTask($commentData->commentTaskID))) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("UPDATE comments SET commentDescription = ? WHERE commentID = ?", $text, $commentID);
        return ["ResponseCode" => "OK"];
    }

    private function getMailStatus($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userMailStatus FROM users WHERE userID = ?", $userID);
        return $return->userMailStatus;
    }

    private function getNumberOfOwnedGroups($userID)
    {
        $sql = "SELECT COUNT(*) as number FROM groups WHERE groupOwner = ?";
        $return = $this->mysqliSelectFetchObject($sql, $userID);
        return (int) $return->number;
    }

    private function getUserType($userID)
    {
        $user = $this->mysqliSelectFetchObject("SELECT userType FROM users WHERE userID = ?", $userID);
        return $user->userType;
    }

    public function createGroup($userID, $groupName)
    {
        if ($this->getMailStatus($userID) == 'unverified') return ["ResponseCode" => "UNVERIFIED_MAIL"];
        if ($this->getNumberOfOwnedGroups($userID) > 9 && $this->getUserType($userID) == 'normal') return ["ResponseCode" => "MAX_GROUPS"];
        if ($this->mysqliSelectFetchObject(
            "SELECT * FROM groups WHERE groupName = ? AND groupOwner = ?",
            $groupName,
            $userID
        )) return ["ResponseCode" => "GROUPNAME_TAKEN"];
        $sql = "INSERT INTO groups (groupName, groupOwner) VALUES (?, ?);";
        $this->mysqliQueryPrepared($sql, $groupName, $userID);
        $group = $this->mysqliSelectFetchObject(
            "SELECT * FROM groups WHERE groupName = ? AND groupOwner = ?",
            $groupName,
            $userID
        );
        $this->mysqliQueryPrepared(
            "INSERT INTO groupaccess (groupID, userID) VALUES ( ?, ?)",
            $group->groupID,
            $group->groupOwner
        );
        return ["ResponseCode" => "OK"];
    }

    private function deleteGroupDependencies($groupID)
    {
        $this->mysqliQueryPrepared("DELETE FROM groups WHERE groupID = ?", $groupID);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $groupID);
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ?", $groupID);
        $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ?", $groupID);
        $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageGroup = ?", $groupID);
    }

    public function deleteGroup($userID, $groupID)
    {
        if (!$this->groupOwnerCheck($groupID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $this->deleteGroupDependencies($groupID);
        return ["ResponseCode" => "OK"];
    }

    public function leaveGroup($userID, $groupID)
    {
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ? AND userID = ?", $groupID, $userID);
        return ["ResponseCode" => "OK"];
    }

    private function generateRandomString($length = 21)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function createGroupInvite($userID, $groupID, $username)
    {
        if (!$this->groupOwnerCheck($groupID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        if ($user = $this->mysqliSelectFetchObject("SELECT userID FROM users WHERE userName = ?", $username)) {
            $sql = "INSERT INTO tokens (tokenType, tokenGroupID, tokenUserID, tokenToken) VALUES (?, ?, ?, ?)";
            $this->mysqliQueryPrepared($sql, "joingroup", $groupID, $user->userID, $this->generateRandomString());
            return ["ResponseCode" => "OK"];
        }
        return ["ResponseCode" => "NO_USER_FOUND"];
    }

    public function toggleGroupInvites($userID, $groupID, $status)
    {
        if (!$this->groupOwnerCheck($groupID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("UPDATE groups SET groupInvites = ? WHERE groupID = ?;", $status, $groupID);
        if ($status == 'enabled') {
            $this->mysqliQueryPrepared(
                "INSERT INTO tokens (tokenType, tokenGroupID, tokenToken) VALUES ('groupinvite', ?, ?)",
                $groupID,
                $this->generateRandomString()
            );
        } else if ($status == 'disabled') {
            $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ? AND tokenType = ?", $groupID, "groupinvite");
        }
        return ["ResponseCode" => "OK"];
    }

    private function getCurrentTimestamp()
    {
        return date('Y-m-d H:i');
    }

    public function updateWeatherCity($userID, $city)
    {
        $this->mysqliQueryPrepared("UPDATE panels SET panelWeatherCity = ? WHERE userID = ?", $city, $userID);
        return ["ResponseCode" => "OK"];
    }

    public function getGroupAccess($userID, $groupID)
    {
        if (!$groupAccess = $this->mysqliSelectFetchArray("SELECT * FROM groupaccess WHERE groupID = ?", $groupID)) return ["ResponseCode" => "NO_ENTRIES"];
        foreach ($groupAccess as $entry) {
            $entry->userName = $this->getUsernameByID($entry->userID);
        }
        $data = (object) [];
        $data->groupAccess = $groupAccess;
        if ($this->groupOwnerCheck($groupID, $userID)) $data->groupOwner = true;
        return ["ResponseCode" => "OK", "data" => $data];
    }

    public function removeUser($userID, $groupID, $removedUserID)
    {
        if (!$this->groupOwnerCheck($groupID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE userID = ? AND groupID = ?", $removedUserID, $groupID);
        return ["ResponseCode" => "OK"];
    }

    public function refreshInvites($userID, $groupID)
    {
        if (!$this->groupOwnerCheck($groupID, $userID)) return ["ResponseCode" => "NO_ACCESS"];
        $newToken = $this->generateRandomString();
        $this->mysqliQueryPrepared(
            "UPDATE tokens SET tokenToken = ? WHERE tokenType = ? AND tokenGroupID = ?;",
            $newToken,
            'groupinvite',
            $groupID
        );
        return ["ResponseCode" => "OK", "data" => $newToken];
    }

    public function joinGroup($userID, $token)
    {
        if (!$tokenData = $this->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $token)) return ["ResponseCode" => "INVALID_TOKEN"];
        $groupCheck = $this->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE groupID = ? AND userID = ?", $tokenData->tokenGroupID, $userID);
        if ($groupCheck) return ["ResponseCode" => "ALREADY_JOINED"];
        $this->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES (?, ?)", $tokenData->tokenGroupID, $userID);
        header("Location: " . DIR_SYSTEM . "php/details.php?action=groupDetails&id=" . $tokenData->tokenGroupID);
        exit;
    }

    public function getGroupIniviteData($userID, $groupID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $data = $this->mysqliSelectFetchObject("SELECT groupInvites FROM groups WHERE groupID = ?", $groupID);
        if ($data->groupInvites == 'enabled') {
            $tokenData = $this->mysqliSelectFetchObject("SELECT tokenToken FROM tokens WHERE tokenType = ? AND tokenGroupID = ?", 'groupinvite', $groupID);
            $data->token = $tokenData->tokenToken;
        }
        if ($this->groupOwnerCheck($groupID, $userID)) $data->groupOwner = true;
        return ["ResponseCode" => "OK", "data" => $data];
    }

    public function getGroupSettingsData($userID, $groupID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $data = $this->mysqliSelectFetchObject("SELECT groupName, groupPriority, groupArchiveTime, groupStatus FROM groups WHERE groupID = ?", $groupID);
        $unfolded = $this->mysqliSelectFetchObject("SELECT groupUnfolded FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID);
        $data->groupUnfolded = $unfolded->groupUnfolded;
        if ($this->groupOwnerCheck($groupID, $userID)) $data->groupOwner = true;
        return ["ResponseCode" => "OK", "data" => $data];
    }

    public function updateGroup($userID, $groupID, $groupName, $groupPrio, $groupArchiveTime, $groupUnfolded, $groupStatus)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $this->mysqliQueryPrepared("UPDATE groupaccess SET groupUnfolded = ? WHERE userID = ? AND groupID = ?", $groupUnfolded, $userID, $groupID);
        if ($this->groupOwnerCheck($groupID, $userID)) {
            $this->mysqliQueryPrepared(
                "UPDATE groups SET groupName = ?, groupPriority = ?, groupArchiveTime = ?, groupStatus = ? WHERE groupID = ?",
                $groupName,
                $groupPrio,
                $groupArchiveTime,
                $groupStatus,
                $groupID
            );
        }
        return ["ResponseCode" => "OK"];
    }

    public function getGroupData($userID, $groupID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return ["ResponseCode" => "NO_ACCESS"];
        $groupData = $this->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupID = ?", $groupID);
        ($this->groupOwnerCheck($groupID, $userID)) ? $groupData->groupOwner = true : $groupData->groupOwner = false;
        $groupData->tasks = $this->mysqliSelectFetchArray(
            "SELECT * FROM tasks WHERE taskParentID = ? AND taskType = ? ORDER BY taskID DESC",
            $groupID,
            'task'
        );
        return ["ResponseCode" => "OK", "data" => $groupData];
    }
}
