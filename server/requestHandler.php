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
            WHERE  ga.userID = ? AND g.groupState = 'active'
            ORDER BY g.groupPriority DESC";
        return $this->mysqliSelectFetchArray($sql, $userID);
    }

    public function getTaskData($taskID)
    {
        return $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);
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

    public function getAppointments($userID)
    {
        $sql = "SELECT m.messageID, m.messageOwner, m.messageGroup, m.messageTitle, m.messageDate, m.messageStart, m.messageEnd
        FROM messages m
            LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
        WHERE  ga.userID = ? AND m.messageType = 'appointment' AND messageDate > CURRENT_DATE
        ORDER BY m.messageDate";
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
            return $appointmentlist;
        }
        return 0;
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
            return $appointmentlist;
        }
        return 0;
    }

    public function editAppointment($userID, $id, $title, $date)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $id);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "UPDATE messages SET messageTitle = ?, messageDate = ? WHERE messageID = ?;";
            $this->mysqliQueryPrepared($sql, $title, $date, $id);
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
            VALUES (?, ?, 'appointment', ?, ?, ?, ?);";
        $this->mysqliQueryPrepared($sql, $userID, (int) $groupID, str_replace(array("\r", "\n"), " ", $title), $date, $start, $end);
        return $this->getAppointments($userID);
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
                $motd->messagePermission = ($userID == $motd->messageGroup || $this->groupOwnerCheck($motd->messageGroup, $userID) || $userID == 1);
            }
            return $data;
        }
        return 0;
    }

    public function editMotd($userID, $id, $title)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $id);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "UPDATE messages SET messageTitle = ? WHERE messageID = ?;";
            $this->mysqliQueryPrepared($sql, $title, $id);
        }
        return $this->getMotd($userID);
    }

    public function deleteMotd($userID, $id)
    {
        $message = $this->mysqliSelectFetchObject("SELECT messageOwner, messageGroup FROM messages WHERE messageID = ?",  $id);
        if ($message->messageOwner == $userID || $this->groupOwnerCheck($message->messageGroup, $userID)) {
            $sql = "DELETE FROM messages WHERE messageID = ?";
            $this->mysqliQueryPrepared($sql, $id);
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
        }
        return 1;
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
        }
        return 1;
    }

    public function toggleUnfoldGroup($userID, $groupID, $checked)
    {
        $this->mysqliQueryPrepared("UPDATE groupaccess SET groupUnfolded = ? WHERE userID = ? AND groupID = ?", $checked, $userID, $groupID);
        return 1;
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
            }
        }
        return 1;
    }

    public function updateLabelOrder($labelIDs)
    {
        for ($i = 0; $i < count($labelIDs); $i++) {
            $this->mysqliQueryPrepared("UPDATE labels SET labelOrder = ? WHERE labelID = ?", ($i + 1), (int) $labelIDs[$i]);
        }
        return 1;
    }

    private function getUsernameByID($userID)
    {
        if ($userID == null || $userID == 'unknown' || $userID == 'Auto-Created') {
            return $userID;
        }
        $sql = "SELECT * FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userName;
    }

    private function getGroupNameByID($groupID)
    {
        if ($groupID) {
            $return = $this->mysqliSelectFetchObject("SELECT groupName FROM groups WHERE groupID = ?", $groupID);
            return $return->groupName;
        } else {
            return '';
        }
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
        if ($this->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID)) {
            return 1;
        } else {
            return 0;
        }
    }

    private function getUserLastMotd($userID)
    {
        $sql = "SELECT userLastMotd FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userLastMotd;
    }

    public function createLabel($userID, $groupID, $title, $description, $color)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        $count = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM labels WHERE labelGroupID = ?", $groupID);
        $sql = "INSERT INTO labels (labelName, labelDescription, labelColor, labelGroupID, labelOrder) VALUES (?, ?, ?, ?, ?)";
        $this->mysqliQueryPrepared($sql, $title, $description, $color, $groupID, ($count->number + 1));
        return 1;
    }

    public function getLabels($userID, $groupID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        $data = $this->mysqliSelectFetchArray("SELECT labelID, labelName, labelDescription, labelColor FROM labels WHERE labelGroupID = ? ORDER BY labelOrder", $groupID);
        if ($data) return $data;
        return 0;
    }

    public function getLabelsForTask($userID, $groupID, $taskID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        $labels = $this->mysqliSelectFetchArray("SELECT labelID, labelName, labelDescription, labelColor FROM labels WHERE labelGroupID = ? ORDER BY labelOrder", $groupID);
        if ($labels) {
            foreach ($labels as $label) {
                $checkIfLabelIsActiveForTask = $this->mysqliSelectFetchObject("SELECT entryID FROM tasklabels WHERE taskID = ? AND labelID = ?", $taskID, $label->labelID);
                if ($checkIfLabelIsActiveForTask) $label->isUsed = true;
            }
            return $labels;
        }
        return 0;
    }

    public function deleteLabel($userID, $groupID, $labelID)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        $this->mysqliQueryPrepared("DELETE FROM labels WHERE labelID = ?", $labelID);
        return 1;
    }

    public function updateLabel($userID, $groupID, $labelID, $title, $description, $color)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        $sql = "UPDATE labels SET
            labelName = ?,
            labeLDescription = ?,
            labelColor = ?
            WHERE labelID = ?";
        $this->mysqliQueryPrepared($sql, $title, $description, $color, $labelID);
        return 1;
    }

    public function updateTaskLabel($userID, $groupID, $taskID, $labelID, $checkboxChecked)
    {
        if (!$this->checkGroupPermission($userID, $groupID)) return 0;
        if ($checkboxChecked == 'true') {
            $this->mysqliQueryPrepared("INSERT INTO tasklabels (labelID, taskID) VALUES (?, ?)", $labelID, $taskID);
        } else {
            $this->mysqliQueryPrepared("DELETE FROM tasklabels WHERE labelID = ? AND taskID = ?", $labelID, $taskID);
        }
        return 1;
    }

    public function createTask($userID, $type, $parentID, $title, $description, $prio)
    {
        if ($type == 'task' && !$this->checkGroupPermission($userID, $parentID)) return 0;
        $sql = "INSERT INTO tasks 
            (taskType, taskParentID, taskPriority, taskPriorityColor, taskTitle, taskDescription, taskState, taskDateCreated) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->mysqliQueryPrepared($sql, $type, $parentID, $prio, $this->getPriorityColor($prio), $title, $description, 'open', date('Y-m-d H:i'));
        return 1;
    }

    private function getPriorityColor($priority)
    {
        $colors = ['green', '#ffcc00', 'red'];
        return $colors[$priority - 1];
    }

    public function getSubtasks($userID, $parentID)
    {
        if (!$this->checkGroupPermission($userID, $this->getGroupIDOfSubtask($parentID))) return 0;
        if ($subtasks = $this->mysqliSelectFetchArray("SELECT * FROM tasks WHERE taskType = ? AND taskParentID = ?", 'subtask', $parentID)) {
            foreach ($subtasks as $task) {
                if ($subtaskCount = $this->getNumberOfSubtasks($task->taskID)) $task->subtaskCount = $subtaskCount;
                if ($task->taskAssignedBy) $task->assigneeNameShort = $this->getUserNameShort($task->taskAssignedBy);
                if ($task->taskState == 'open') $task->daysActive = $this->getDateDifference($task->taskDateCreated);
            }
            return $subtasks;
        }
        return 0;
    }

    private function getGroupIDOfSubtask($taskID)
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
        $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ? AND taskState = 'open'";
        if ($data = $this->mysqliSelectFetchObject($sql, $taskID)) return $data->number;
        return 0;
    }

    private function getUserNameShort($userID)
    {
        if ($userData = $this->mysqliSelectFetchObject("SELECT userNameShort FROM users WHERE userID = ?", $userID)) return $userData->userNameShort;
        return 0;
    }

    private function getDateDifference($date)
    {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d H:i')))->format('%r%a');
    }
}
