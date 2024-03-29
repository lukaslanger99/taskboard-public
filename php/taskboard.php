<?php
class TaskBoard
{
    public function mysqliConnect()
    {
        $mysqli = new mysqli(SERVER_NAME, USER, PASS, DB);

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        return $mysqli;
    }

    public function mysqliQueryPrepared($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
        } else {
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
            mysqli_stmt_execute($stmt);
        }
    }

    public function mysqliSelectFetchObject($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
        } else {
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_object($result);
            }
        }
    }

    public function mysqliSelectFetchArray($sql, ...$params)
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
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

    public function addTagsToUrlsInString($string)
    {
        $words = explode(' ', $string);
        foreach ($words as &$word) {
            if (filter_var($word, FILTER_VALIDATE_URL)) {
                $word = '<a style="text-decoration:underline;" href="' . $word . '" target="_blank">' . $word . '</a>';
            }
        }
        return implode(' ', $words);
    }

    public function checkIfEmailIsTaken($email)
    {
        if ($this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userMail = ?", $email)) return 'taken';
        return 'untaken';
    }

    public function deleteGroup($id)
    {
        $this->mysqliQueryPrepared("DELETE FROM groups WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageGroup = ?", $id);
    }

    public function deleteUser($userID)
    {
        $groups = $this->sqlGetAllGroups($userID);
        foreach ($groups as $group) {
            $this->deleteGroup($group->groupID);
        }
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE userID = ?", $userID);
        $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageOwner = ?", $userID);
        $this->mysqliQueryPrepared("DELETE FROM users WHERE userID = ?", $userID);
        $this->mysqliQueryPrepared("DELETE FROM panels WHERE userID = ?", $userID);
        $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenUserID = ?", $userID);
    }

    public function generateRandomString($length = 21)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getDateDifferenceDaysOnly($date)
    {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d')))->format('%r%a');
    }

    public function getGroupNameByID($groupID)
    {
        if ($groupID) {
            $return = $this->mysqliSelectFetchObject("SELECT groupName FROM groups WHERE groupID = ?", $groupID);
            return $return->groupName;
        }
        return '';
    }

    public function getGroupOwnerID($groupID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $return->groupOwner;
    }

    public function getMailByUserID($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userID = ?", $userID);
        return $return->userMail;
    }

    public function getMailStatus($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userMailStatus FROM users WHERE userID = ?", $userID);
        return $return->userMailStatus;
    }

    public function getNightmodeEnabled($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userNightmode FROM users WHERE userID = ?", $userID);
        return $return->userNightmode == 'true';
    }

    public function getNumberOfGroupUsers($groupID)
    {
        $sql = "SELECT COUNT(*) as number FROM groupaccess WHERE groupID = ?";
        $return = $this->mysqliSelectFetchObject($sql, $groupID);
        return (int) $return->number;
    }

    public function getNumberOfOwnedGroups($userID)
    {
        $sql = "SELECT COUNT(*) as number FROM groups WHERE groupOwner = ?";
        $return = $this->mysqliSelectFetchObject($sql, $userID);
        return (int) $return->number;
    }

    public function getUserData($userID)
    {
        $sql = "SELECT * FROM users WHERE userID = ?";
        return $this->mysqliSelectFetchObject($sql, $userID);
    }

    public function getPriorityColor($priority)
    {
        $colors = ['green', '#ffcc00', 'red'];
        return $colors[$priority - 1];
    }

    public function getUserIDByMail($mail)
    {
        $data = $this->mysqliSelectFetchObject("SELECT userID FROM users WHERE userMail = ?", $mail);
        return $data->userID;
    }

    public function getUserIDByUsername($username)
    {
        $data = $this->mysqliSelectFetchObject("SELECT * FROM users WHERE userName = ?", $username);
        return $data->userID;
    }

    public function getUsernameByID($userID)
    {
        if ($userID == null || $userID == 'unknown' || $userID == 'Auto-Created') return $userID;
        $data = $this->mysqliSelectFetchObject("SELECT * FROM users WHERE userID = ?", $userID);
        return $data->userName;
    }

    /**
     * get number (int) how many group invites a user has
     */
    public function getUserGroupInvitesCount($userID)
    {
        $sql = "SELECT COUNT(*) AS number FROM tokens WHERE tokenType = 'joingroup' AND tokenUserID = ?";
        if ($data = $this->mysqliSelectFetchObject($sql, $userID)) return $data->number;
        return 0;
    }

    /**
     * return all invite tokens the user has
     */
    private function getGroupInvites($userID)
    {
        return $this->mysqliSelectFetchArray("SELECT * FROM tokens WHERE tokenType = 'joingroup' AND tokenUserID = ?", $userID);
    }

    /**
     * return true if mail is verified
     * return false if mail is unverified
     */
    public function getUserVerificationStatus($userID)
    {
        $data = $this->mysqliSelectFetchObject("SELECT userMailStatus FROM users WHERE userID = ?", $userID);
        return $data->userMailStatus == 'verified';
    }

    public function getWeek()
    {
        if (date('W') % 2 == 1) return 'odd';
        return 'even';
    }

    public function getWeekday()
    {
        return date('D');
    }

    public function locationEnteredUrl($url, $getParam = '')
    {
        if (strpos($_SESSION['enteredUrl'], '?')) {
            header("Location: " . DOMAIN . $url . "&$getParam");
        } else {
            header("Location: " . DOMAIN . $url . "?$getParam");
        }
        exit;
    }

    public function locationIndex($getParam = '')
    {
        header("Location: " . DIR_SYSTEM . "index.php" . $getParam);
        exit;
    }

    public function locationWithDir($url)
    {
        header("Location: " . DIR_SYSTEM . $url);
        exit;
    }

    public function printGroupNames()
    {
        $groups = $this->sqlGetAllGroups();
        $html =  '
            <div class="group-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>STATUS</th>
                    <th>PRIORITY</th>
                    <th>TOTAL_NUM_OF_TASKS</th>
                    <th>CURRENTLY_OPEN</th>
                    <th></th>
                </tr>';

        if ($groups != null) {
            ($this->getNightmodeEnabled($_SESSION['userID'])) ? $backgroundColor = '#333333' : $backgroundColor = '#fff';
            $toggle = true;
            foreach ($groups as $group) {
                $groupID = $group->groupID;
                $totalTasks = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $groupID);
                $openTasks = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskStatus = 'open'", $groupID);

                if ($_SESSION['userID'] == $this->getGroupOwnerID($groupID)) {
                    $deleteOrLeaveGroup = '<td><button type="button" onclick="groupHandler.deleteGroup(' . $groupID . ')">Delete Group</button>';
                } else {
                    $deleteOrLeaveGroup = '<button type="button" onclick="leaveGroup(' . $groupID . ')">Leave Group</button>';
                }

                $toggle = !$toggle;
                ($toggle) ? $html .= '<tr style="background-color:' . $backgroundColor . ';">' : $html .= '<tr>';
                $html .= '
                    <td>' . $groupID . '</td>
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $groupID . '">' . $group->groupName . '</a></td>
                    <td>' . $group->groupStatus . '</td>
                    <td>' . $group->groupPriority . '</td>
                    <td>' . $totalTasks->number . '</td>
                    <td>' . $openTasks->number . '</td>
                    <td>' . $deleteOrLeaveGroup . '</td>
                </tr>
                ';
            }
        }
        $html .= '</table></div>';
        echo $html;
    }

    public function printInviteMessages($userID)
    {
        if ($invites = $this->getGroupInvites($userID)) {
            $html = '';
            foreach ($invites as $invite) {
                $groupID = $invite->tokenGroupID;
                $html .= '
                <a href="' . DIR_SYSTEM . 'php/profile.php">
                    <div class="dropdown_message">
                        <p><i class="fa fa-user"></i></p>
                        <div class="dropdown_message_text">
                           <p>Invite for ' . $this->getGroupNameByID($groupID) . '</p>
                           <p>from ' . $this->getUsernameByID($this->getGroupOwnerID($groupID)) . '</p>
                           <p class="dropdown_message_text_date">' . $invite->tokenDate . '</p>
                        </div>
                    </div>
                </a>';
            }
            return $html;
        }
        return '';
    }

    private function printPanel($type, $unfolded, $spec = '')
    {
        return '<div class="panel-item">' . $this->panelHeader($type) . $this->panelContent($type, $unfolded, $spec) . '</div>';
    }

    public function printPanelSettings($type, $title, $activeID, $activeStatus, $unfoldedID, $unfoldedStatus)
    {
        return '<div class="draggable__item__panelsettings draggable__item" draggable="true" data-type="' . $type . '">
                <p>' . $title . '</p>
                <label class="switch">
                    <input id="' . $activeID . '" type="checkbox" ' . $activeStatus . '/>
                    <span class="slider round"></span>
                </label>
                <small>Activate</small>
                <label class="switch">
                    <input type="checkbox" id="' . $unfoldedID . '" ' . $unfoldedStatus . '/>
                    <span class="slider round"></span>
                </label>
                <small>Unfolded by default on mobile</small>
            </div>';
    }

    private function panelHeader($type)
    {
        if ($type == 'appointment') {
            return '<div class="panel-item-top-bar">
                <div class="display__flex">
                    <p id="appointmentPanelTitle"></p>
                </div>
                <div class="display__flex">
                    <div class="panel-item-top-bar-button" id="openAppointmentCalendarButton" onclick="panels.openAppointmentCalendar()">
                        <i class="fa fa-calendar-day" aria-hidden="true"></i>
                    </div>
                    <div class="panel-item-top-bar-button" id="createAppointmentButton" onclick="panels.openAddAppointmentForm()">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </div>
                    <div class="panel_item_top_bar_unfold_button" id="appointmentUnfoldButton" onclick="toggleUnfoldArea(\'appointmentPanelContentArea\',\'appointmentUnfoldButton\')">
                       <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </div>
                </div>
            </div>';
        } else if ($type == 'motd') {
            $createButtonID = 'createMOTDButton';
            $onclick = 'panels.openAddMotdForm()"';
            $unfoldButtonID = 'motdUnfoldButton';
            $contentAreaID = 'motdPanelContentArea';
            $titleID = 'motdPanelTitle';
            $title = '';
        } else if ($type == 'queue') {
            return '<div class="panel-item-top-bar">
                    <div class="queue__top-bar-left">
                        <p id="queuePanelTitle"></p>
                    </div>
                    <div class="queue__top-bar-right">
                        <input class="queue__input__text" type="text" id="queueItem" name="queueItem">
                        <input class="queue__input__check" type="checkbox" id="queueHighprio" name="queueHighprio" style="outline: 1px solid red;">
                        <input type="submit" id="queueSubmit "name="add-queue-submit" value="Add" onclick="panels.addQueueTask()"/>
                        <div class="panel_item_top_bar_unfold_button" id="queueUnfoldButton" onclick="toggleUnfoldArea(\'queuePanelContentArea\',\'queueUnfoldButton\')">
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>';
        } else if ($type == 'weather') {
            return '<div class="panel-item-top-bar">
                    <div class="display__flex">
                        <p>Weather</p>
                    </div>
                    <div class="panel_item_top_bar_unfold_button" id="weatherUnfoldButton" onclick="toggleUnfoldArea(\'weatherPanelContentArea\',\'weatherUnfoldButton\')">
                       <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </div>
                </div>';
        } else if ($type == 'timetable') {
            return '<div class="panel-item-top-bar">
                    <div class="display__flex">
                        <p>Timetable (KW' . date("W") . ')</p>
                    </div>
                    <div class="display__flex">
                        <div class="panel-item-top-bar-button" id="timetableCurrentWeekButton" onclick="timetable.timetablePopup(\'current\')">
                            Current week
                        </div>
                        <div class="panel-item-top-bar-button" id="timetableNextWeekButton" onclick="timetable.timetablePopup(\'next\')">
                            Next week
                        </div>
                        <div class="panel_item_top_bar_unfold_button" id="timetableUnfoldButton" onclick="toggleUnfoldArea(\'timetablePanelContentArea\',\'timetableUnfoldButton\')">
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>';
        } else if ($type == 'morningroutine') {
            return '<div class="panel-item-top-bar">
                    <div class="queue__top-bar-left">
                        <p id="morningroutinePanelTitle"></p>
                    </div>
                    <div class="queue__top-bar-right">
                        <div class="panel-item-top-bar-button" onclick="panels.showMorningroutinePopup()">
                            <i class="fa fa-list" aria-hidden="true"></i>
                        </div>
                        <div class="panel-item-top-bar-button" onclick="panels.resetMorningroutine()">
                            <i class="fa fa-rotate-right" aria-hidden="true"></i>
                        </div>
                        <input class="queue__input__text" type="text" id="morningroutineItem" name="morningroutineItem">
                        <input type="submit" id="morningroutineSubmit "name="add-morningroutine-submit" value="Add" onclick="panels.addMorningroutineTask()"/>
                        <div class="panel_item_top_bar_unfold_button" id="morningroutineUnfoldButton" onclick="toggleUnfoldArea(\'morningroutinePanelContentArea\',\'morningroutineUnfoldButton\')">
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>';
        }

        return '<div class="panel-item-top-bar">
            <div class="display__flex">
                <p id="' . $titleID . '">' . $title . '</p>
            </div>
            <div class="display__flex">
                <div class="panel-item-top-bar-button" id="' . $createButtonID . '" onclick="' . $onclick . '">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </div>
                <div class="panel_item_top_bar_unfold_button" id="' . $unfoldButtonID . '" onclick="toggleUnfoldArea(\'' . $contentAreaID . '\',\'' . $unfoldButtonID . '\')">
                   <i class="fa fa-caret-down" aria-hidden="true"></i>
                </div>
            </div>
        </div>';
    }

    private function panelContent($type, $unfolded, $spec)
    {
        if ($type == 'appointment') {
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'appointmentPanelContentArea\',\'appointmentUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="appointmentPanelContentArea">
                    <script>
                        panels.printAppointments()
                        ' . $unfoldPanel . '
                    </script>
                </div>';
        } else if ($type == 'motd') {
            $this->mysqliQueryPrepared("UPDATE users SET userLastMotd = CURRENT_TIMESTAMP WHERE userID = ?", $_SESSION['userID']);
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'motdPanelContentArea\',\'motdUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="motdPanelContentArea">
                    <script>
                        panels.printMotd()
                        ' . $unfoldPanel . '
                        </script>
                </div>';
        } else if ($type == 'queue') {
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'queuePanelContentArea\',\'queueUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="queuePanelContentArea">
                    <script>
                        panels.printQueueTasks()
                    ' . $unfoldPanel . '
                    </script>
                </div>';
        } else if ($type == 'weather') {
            $prevRows = '';
            for ($i = 1; $i < 6; $i++) {
                $prevRows .= '
                <div class="weather__block__forecast">
                  <div class="weather__date" id="weatherPrevDate' . $i . '"></div>
                  <img src="" alt="" id="weatherPrevIcon' . $i . '" />
                  <div class="weather__temp" id="weatherPrevTemp' . $i . '"></div>
                </div>';
            }
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'weatherPanelContentArea\',\'weatherUnfoldButton\', \'true\')';
            return '<div class="weather__panel__content" id="weatherPanelContentArea">
                    <div class="weather">
                        <div class="weather__input">
                            <input type="text" id="weatherPanelCity" name="city" placeholder="cityname">
                            <button class="button" onclick="weather.updateWeatherCity()">Update</button>
                        </div>
                        <h2 class="weather__city"><h2>
                        <div class="weather__block">
                          <img src="" alt="" class="weather__icon" />
                          <div class="weather__temp__big"></div>
                        </div>
                        <div class="weather__description weather__font__small"></div>
                        <div class="weather__humidity weather__font__small"></div>
                    </div>
                    <div class="weather__forecast">
                        <p class="weather__forecast__header">5-Day Forecast</p>
                    ' . $prevRows . '
                    </div>
                    <script>
                        weather.fetchWeather(\'' . $spec . '\')
                        weather.fetchForecast(\'' . $spec . '\')
                        ' . $unfoldPanel . '
                    </script>
                </div>';
        } else if ($type == 'timetable') {
            if ($spec != '') {
                $tasks = $this->mysqliSelectFetchArray(
                    "SELECT * FROM timetableentrys WHERE timetableID = ? AND timetableWeekday = ? ORDER BY timetableTimeStart",
                    $spec,
                    date('D')
                );
                if ($tasks) {
                    $currentTime = date('H:i');
                    $nextTask = null;
                    for ($i = 0; $i < count($tasks); $i++) {
                        if ($currentTime > $tasks[$i]->timetableTimeEnd) $prevTask = $tasks[$i];
                        if ($currentTime > $tasks[$i]->timetableTimeStart && $currentTime < $tasks[$i]->timetableTimeEnd) $activeTasks[] = $tasks[$i];
                        if ($currentTime < $tasks[$i]->timetableTimeStart && !$nextTask) {
                            $nextTask = $tasks[$i];
                            if ($i < count($tasks) - 1) $nextTask2 = $tasks[$i + 1];
                            if ($i < count($tasks) - 2) $nextTask3 = $tasks[$i + 2];
                            if ($i < count($tasks) - 3) $nextTask4 = $tasks[$i + 3];
                        }
                    }
                }
            }
            ($activeTasks) ? $prevAndActiveTasksCount = count($activeTasks) : $prevAndActiveTasksCount = 0;
            if ($prevTask) $prevAndActiveTasksCount++;
            $content = '';
            if ($prevTask) {
                $content .= '<div class="timetable__panel__prevtask">
                        <div class="timetable__content__task__time">' . $prevTask->timetableTimeStart . ' - ' . $prevTask->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $prevTask->timetableText . '</div>
                    </div>';
            }
            if ($activeTasks) {
                foreach ($activeTasks as $activeTask) {
                    $content .= '<div class="timetable__panel__activetask">
                            <div class="timetable__content__task__time">' . $activeTask->timetableTimeStart . ' - ' . $activeTask->timetableTimeEnd . '</div>
                            <div class="timetable__content__task__text">' . $activeTask->timetableText . '</div>
                        </div>';
                }
            }
            if ($nextTask && $prevAndActiveTasksCount < 4) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask->timetableTimeStart . ' - ' . $nextTask->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask->timetableText . '</div>
                    </div>';
            }
            if ($nextTask2 && $prevAndActiveTasksCount < 3) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask2->timetableTimeStart . ' - ' . $nextTask2->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask2->timetableText . '</div>
                    </div>';
            }
            if ($nextTask3 && $prevAndActiveTasksCount < 2) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask3->timetableTimeStart . ' - ' . $nextTask3->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask3->timetableText . '</div>
                    </div>';
            }
            if ($nextTask4 && !$prevTask && $prevAndActiveTasksCount == 0) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask4->timetableTimeStart . ' - ' . $nextTask4->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask4->timetableText . '</div>
                    </div>';
            }
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'timetablePanelContentArea\',\'timetableUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="timetablePanelContentArea">
                    ' . $content . '
                    <script>' . $unfoldPanel . '</script>
                </div>';
        } else if ($type == 'morningroutine') {
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'morningroutinePanelContentArea\',\'morningroutineUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="morningroutinePanelContentArea">
                    <script>
                        panels.printMorningroutineTasks()
                    ' . $unfoldPanel . '
                    </script>
                </div>';
        }
    }

    public function printPanels()
    {
        $userID = $_SESSION['userID'];
        $panelData = $this->mysqliSelectFetchObject("SELECT * FROM panels WHERE userID = ?", $userID);
        $panelHTML = '';
        $panelCounter = 0;
        $activePanels = [];
        if ($panelData->panelMOTD == 'true') {
            $activePanels[$panelData->panelMOTDOrder] = [
                'name' => 'motd',
                'unfolded' => $panelData->panelMOTDUnfolded,
                'spec' => ''
            ];
        }
        if ($panelData->panelAppointment == 'true') {
            $activePanels[$panelData->panelAppointmentOrder] = [
                'name' => 'appointment',
                'unfolded' => $panelData->panelAppointmentUnfolded,
                'spec' => ''
            ];
        }
        if ($panelData->panelQueue == 'true') {
            $activePanels[$panelData->panelQueueOrder] = [
                'name' => 'queue',
                'unfolded' => $panelData->panelQueueUnfolded,
                'spec' => ''
            ];
        }
        if ($panelData->panelWeather == 'true') {
            $activePanels[$panelData->panelWeatherOrder] = [
                'name' => 'weather',
                'unfolded' => $panelData->panelWeatherUnfolded,
                'spec' => $panelData->panelWeatherCity
            ];
        }
        if ($panelData->panelTimetable == 'true') {
            $timetable = $this->mysqliSelectFetchObject("SELECT timetableID FROM timetables WHERE timetableUserID = ? AND timetableWeek = ?", $userID, date('W'));
            $activePanels[$panelData->panelTimetableOrder] = [
                'name' => 'timetable',
                'unfolded' => $panelData->panelTimetableUnfolded,
                'spec' => $timetable->timetableID
            ];
        }
        if ($panelData->panelMorningroutine == 'true') {
            $activePanels[$panelData->panelMorningroutineOrder] = [
                'name' => 'morningroutine',
                'unfolded' => $panelData->panelMorningroutineUnfolded,
                'spec' => ''
            ];
        }
        for ($i = 0; $i < NUMBER_OF_TOTAL_PANELS; $i++) {
            $panelData = $activePanels[$i + 1];
            if ($panelData) {
                $panelHTML .= $this->printPanel($panelData['name'], $panelData['unfolded'], $panelData['spec']);
                $panelCounter++;
            }
        }
        if ($panelCounter > 0) {
            $html = '
            <div class="panel">
            ' . $panelHTML . '
            </div>
            ';
            echo $html;
        }
    }

    public function printUserDetails($userID)
    {
        $userData = $this->mysqliSelectFetchObject("SELECT * FROM users WHERE userID = ?", $userID);
        $groupAccess = $this->mysqliSelectFetchArray("SELECT * FROM groupaccess WHERE userID = ?", $userID);

        $backButton = '<div style="float:left;"><a href="' . DIR_SYSTEM . 'php/admin.php">
            <div class="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</div></a></div>';

        $ownedGroupsHTML = '';
        $groupAccessHTML = '';
        if ($ownedGroups = $this->mysqliSelectFetchArray("SELECT * FROM groups WHERE groupOwner = ?", $userID)) {
            foreach ($ownedGroups as $group) {
                $ownedGroupsHTML .= '
                <tr>
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $group->groupID . '">' . $group->groupName . '</a></td>
                </tr>';
            }
            foreach ($groupAccess as $group) {
                $groupID = $group->groupID;
                $groupAccessHTML .= '
                <tr>
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $groupID . '">' . $this->getGroupNameByID($groupID) . '</a></td>
                </tr>';
            }
        }

        $html = '
        ' . $backButton . '
        <div class="group-box">
            USER
            <table>
                <tr>
                    <td>Username</td>
                    <td>' . $userData->userName . '</td>
                </tr>
                <tr>
                    <td>Mail</td>
                    <td>' . $userData->userMail . '</td>
                </tr>
                <tr>
                    <td>Mail-Status</td>
                    <td>' . $userData->userMailStatus . '</td>
                </tr>
            </table>
        </div>
        
        <div class="group-box">
            OWNED GROUPS
            <table>
                ' . $ownedGroupsHTML . '
            </table>
        </div>

        <div class="group-box">
            GROUPACCESS
            <table>
                ' . $groupAccessHTML . '
            </table>
        </div>';
        return $html;
    }

    public function printVerifyMailMessage()
    {
        return '<a href="' . DIR_SYSTEM . 'php/profile.php">
            <div class="dropdown_message">
                <p><i class="fa fa-envelope"></i></p>
                <p>Verify your mail please!</p>
            </div>
        </a>';
    }

    private function pwResetMailHTML($verifyUrl)
    {
        $html = '<!DOCTYPE html>
        <html>
        
        <head>
            <title></title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <style type="text/css">
                @media screen {
                    @font-face {
                        font-family: \'Lato\';
                        font-style: normal;
                        font-weight: 400;
                        src: local(\'Lato Regular\'), local(\'Lato-Regular\'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: normal;
                        font-weight: 700;
                        src: local(\'Lato Bold\'), local(\'Lato-Bold\'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: italic;
                        font-weight: 400;
                        src: local(\'Lato Italic\'), local(\'Lato-Italic\'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: italic;
                        font-weight: 700;
                        src: local(\'Lato Bold Italic\'), local(\'Lato-BoldItalic\'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format(\'woff\');
                    }
                }
        
                /* CLIENT-SPECIFIC STYLES */
                body,
                table,
                td,
                a {
                    -webkit-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                }
        
                table,
                td {
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                }
        
                img {
                    -ms-interpolation-mode: bicubic;
                }
        
                /* RESET STYLES */
                img {
                    border: 0;
                    height: auto;
                    line-height: 100%;
                    outline: none;
                    text-decoration: none;
                }
        
                table {
                    border-collapse: collapse !important;
                }
        
                body {
                    height: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
        
                /* iOS BLUE LINKS */
                a[x-apple-data-detectors] {
                    color: inherit !important;
                    text-decoration: none !important;
                    font-size: inherit !important;
                    font-family: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                }
        
                /* MOBILE STYLES */
                @media screen and (max-width:600px) {
                    h1 {
                        font-size: 32px !important;
                        line-height: 32px !important;
                    }
                }
        
                /* ANDROID CENTER FIX */
                div[style*="margin: 16px 0;"] {
                    margin: 0 !important;
                }
            </style>
        </head>
        
        <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
            <!-- HIDDEN PREHEADER TEXT -->
            <div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: \'Lato\', Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;"> We\'re thrilled to have you here! Get ready to dive into your new account. </div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <!-- LOGO -->
                <tr>
                    <td bgcolor="#FFA73B" align="center">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#FFA73B" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                    <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1> <img src=" https://img.icons8.com/clouds/100/000000/handshake.png" width="125" height="120" style="display: block; border: 0px;" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">You have requested to reset your password. Just press the button below.</p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                                                <table border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#FFA73B"><a href="' . $verifyUrl . '" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFA73B; display: inline-block;">Reset Password</a></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr> <!-- COPY -->
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 0px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">If that doesn\'t work, copy and paste the following link in your browser:</p>
                                </td>
                            </tr> <!-- COPY -->
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 20px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;"><a href="' . $verifyUrl . '" target="_blank" style="color: #FFA73B;">' . $verifyUrl . '</a></p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 20px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">If you have any questions, just reply to this email—we\'re always happy to help out.</p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">Cheers,<br>Taskboard Team</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                    <p style="margin: 0;">If these emails get annoying, please feel free to <a href="#" target="_blank" style="color: #111111; font-weight: 700;">unsubscribe</a>.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        
        </html>';
        return $html;
    }

    public function sendPWResetMail($mail, $token)
    {
        $verifyUrl = DIR_SYSTEM . 'php/recover.php?t=' . $token;

        $subject = 'Reset your password for taskboard';

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: lukaslanger@bplaced.net\r\n";
        $header .= "Reply-To: $mail\r\n";

        mail($mail, $subject, $this->pwResetMailHTML($verifyUrl), $header);
    }

    public function sendVerifyMail($userID, $mail)
    {
        $token = $this->generateRandomString();
        $verifyUrl = DIR_SYSTEM . 'php/profile.inc.php?action=verifyMail&t=' . $token;
        $this->mysqliQueryPrepared("INSERT INTO tokens (tokenType, tokenUserID, tokenToken) VALUES ('verifymail', ?, ?)", $userID, $token);

        $subject = 'Verify your mail for taskboard';

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: lukaslanger@bplaced.net\r\n";
        $header .= "Reply-To: $mail\r\n";

        mail($mail, $subject, $this->verifyMailHTML($verifyUrl), $header);
    }

    private function verifyMailHTML($verifyUrl)
    {
        $html = '<!DOCTYPE html>
        <html>
        
        <head>
            <title></title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <style type="text/css">
                @media screen {
                    @font-face {
                        font-family: \'Lato\';
                        font-style: normal;
                        font-weight: 400;
                        src: local(\'Lato Regular\'), local(\'Lato-Regular\'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: normal;
                        font-weight: 700;
                        src: local(\'Lato Bold\'), local(\'Lato-Bold\'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: italic;
                        font-weight: 400;
                        src: local(\'Lato Italic\'), local(\'Lato-Italic\'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format(\'woff\');
                    }
        
                    @font-face {
                        font-family: \'Lato\';
                        font-style: italic;
                        font-weight: 700;
                        src: local(\'Lato Bold Italic\'), local(\'Lato-BoldItalic\'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format(\'woff\');
                    }
                }
        
                /* CLIENT-SPECIFIC STYLES */
                body,
                table,
                td,
                a {
                    -webkit-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                }
        
                table,
                td {
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                }
        
                img {
                    -ms-interpolation-mode: bicubic;
                }
        
                /* RESET STYLES */
                img {
                    border: 0;
                    height: auto;
                    line-height: 100%;
                    outline: none;
                    text-decoration: none;
                }
        
                table {
                    border-collapse: collapse !important;
                }
        
                body {
                    height: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
        
                /* iOS BLUE LINKS */
                a[x-apple-data-detectors] {
                    color: inherit !important;
                    text-decoration: none !important;
                    font-size: inherit !important;
                    font-family: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                }
        
                /* MOBILE STYLES */
                @media screen and (max-width:600px) {
                    h1 {
                        font-size: 32px !important;
                        line-height: 32px !important;
                    }
                }
        
                /* ANDROID CENTER FIX */
                div[style*="margin: 16px 0;"] {
                    margin: 0 !important;
                }
            </style>
        </head>
        
        <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
            <!-- HIDDEN PREHEADER TEXT -->
            <div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: \'Lato\', Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;"> We\'re thrilled to have you here! Get ready to dive into your new account. </div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <!-- LOGO -->
                <tr>
                    <td bgcolor="#FFA73B" align="center">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#FFA73B" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                    <h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1> <img src=" https://img.icons8.com/clouds/100/000000/handshake.png" width="125" height="120" style="display: block; border: 0px;" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">We\'re excited to have you get started. First, you need to confirm your account. Just press the button below.</p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                                                <table border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#FFA73B"><a href="' . $verifyUrl . '" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFA73B; display: inline-block;">Confirm Account</a></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr> <!-- COPY -->
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 0px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">If that doesn\'t work, copy and paste the following link in your browser:</p>
                                </td>
                            </tr> <!-- COPY -->
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 20px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;"><a href="' . $verifyUrl . '" target="_blank" style="color: #FFA73B;">' . $verifyUrl . '</a></p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 20px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">If you have any questions, just reply to this email—we\'re always happy to help out.</p>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                                    <p style="margin: 0;">Cheers,<br>Taskboard Team</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                            <tr>
                                <td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
                                    <p style="margin: 0;">If these emails get annoying, please feel free to <a href="#" target="_blank" style="color: #111111; font-weight: 700;">unsubscribe</a>.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        
        </html>';
        return $html;
    }

    public function sqlGetAllGroups($userID = '')
    {
        $sql = "SELECT g.* 
                FROM groups g
                    LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
                WHERE  ga.userID = ?
                ORDER BY g.groupPriority DESC";
        if ($userID == '') return $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        return $this->mysqliSelectFetchArray($sql, $userID);
    }
}
