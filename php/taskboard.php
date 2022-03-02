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

    public function mysqliQueryPrepared($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
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
        }
    }

    public function mysqliSelectFetchObject($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
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

    public function mysqliSelectFetchArray($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
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

    /**
     * returns true if $dateDiff is higher than groupArchiveTime of $groupID
     */
    private function archiveCheck($groupID, $dateDiff)
    {
        $groupData = $this->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupID = ?", $groupID);
        return $dateDiff >= $groupData->groupArchiveTime;
    }

    public function checkGroupPermission($userID, $groupID)
    {
        if ($this->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checkIfEmailIsTaken($email)
    {
        $username = $this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userMail = ?", $email);
        if ($username) {
            return 'taken';
        } else {
            return 'untaken';
        }
    }

    public function checkTaskPermission($userID, $task)
    {
        if ($task->taskType == 'task') {
            $groupID = $task->taskParentID;
        } else if ($task->taskType == 'subtask') {
            $parent = $task;
            do {
                $parent = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $parent->taskParentID);
                $type = $parent->taskType;
            } while ($type == 'subtask');
            $groupID = $parent->taskParentID;
        }
        return $this->checkGroupPermission($userID, $groupID);
    }

    public function checkUsername($username)
    {
        $count = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM users WHERE userName = ?", $username);
        return $count->number;
    }

    public function createComment($taskId, $type, $autor, $description, $date)
    {
        $sql = "INSERT INTO comments (commentTaskID, commentType, commentAutor, commentDescription, commentDate) VALUES (?, ?, '$autor', ?, '$date')";
        $this->mysqliQueryPrepared($sql, $taskId, $type, $description);
    }

    public function deleteGroup($id)
    {
        $this->mysqliQueryPrepared("DELETE FROM groups WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageGroup = ?", $id);
    }

    public function deleteTaskPermission($taskID, $userID, $type)
    {
        if ($type == 'task') {
            $groupID = $this->getParentIDOfTask($taskID);
        } else {
            $parent = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);;
            do {
                $parent = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $parent->taskParentID);
                $type = $parent->taskType;
            } while ($type == 'subtask');
            $groupID = $parent->taskParentID;
        }
        return $this->checkGroupPermission($userID, $groupID);
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

    public function getArchivedTasks()
    {
        $sql = "SELECT * FROM tasks WHERE taskState = 'archived' ORDER BY taskID DESC";
        $data = $this->mysqliSelectFetchArray($sql);

        if ($data != null) {
            foreach ($data as $i) {
                if ($this->checkGroupPermission($_SESSION['userID'], $i->taskParentID))
                    $tasks[] = $i;
            }
        }

        return $tasks;
    }

    private function getDateDifference($date)
    {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d H:i')))->format('%r%a');
    }

    public function getDateDifferenceDaysOnly($date)
    {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d')))->format('%r%a');
    }

    public function getParentIDOfTask($taskID)
    {
        $task = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);
        return $task->taskParentID;
    }

    public function getGroupNameByID($groupID)
    {
        if ($groupID) {
            $return = $this->mysqliSelectFetchObject("SELECT groupName FROM groups WHERE groupID = ?", $groupID);
            return $return->groupName;
        } else {
            return '';
        }
    }

    public function getGroupOwnerID($groupID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $return->groupOwner;
    }

    private function getGroupUnfolded($userID, $groupID)
    {
        $data = $this->mysqliSelectFetchObject("SELECT groupUnfolded FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID);
        return $data->groupUnfolded;
    }

    public function getMailByUserID($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userID = ?", $userID);
        return $return->userMail;
    }

    public function getMailState($userID)
    {
        $return = $this->mysqliSelectFetchObject("SELECT userMailState FROM users WHERE userID = ?", $userID);
        return $return->userMailState;
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

    private function getNumberOfSubtasks($taskId)
    {
        $sql = "SELECT * FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?";
        $data = $this->mysqliSelectFetchArray($sql, $taskId);

        $open = 0;
        $inProgress = 0;
        $finished = 0;

        if ($data != null) {
            foreach ($data as $i) {
                if ($i->taskState == 'open') {
                    $open += 1;
                } else if ($i->taskState == 'assigned') {
                    $inProgress += 1;
                } else if ($i->taskState == 'finished') {
                    $finished += 1;
                }
            }
        }

        $numberOfSubtasks = $open + $inProgress;

        if ($numberOfSubtasks == 1) {
            return '<div class="subtask_label">' . $numberOfSubtasks . ' Subtask</div>';
        } else if ($numberOfSubtasks > 1) {
            return '<div class="subtask_label">' . $numberOfSubtasks . ' Subtasks</div>';
        } else {
            return '';
        }
    }

    public function getUserData($userID)
    {
        $sql = "SELECT * FROM users WHERE userID = ?";
        return $this->mysqliSelectFetchObject($sql, $userID);
    }

    private function getUserListHTML($groupID)
    {
        $isOwner = $this->groupOwnerCheck($groupID, $_SESSION['userID']);
        $groupEntries = $this->mysqliSelectFetchArray("SELECT * FROM groupaccess WHERE groupID = ?", $groupID);
        $nightmodeEnabled = $this->getNightmodeEnabled($_SESSION['userID']);

        $html = '<div class="panel-item-content-item">
        <table>';
        $toggle = false;
        foreach ($groupEntries as $entry) {
            $userID = $entry->userID;
            if ($isOwner && ($userID != $_SESSION['userID'])) {
                $removeAccessHTML = '<button type="button" onclick="removeUserAccess(\'' . $groupID . '\',' . $userID . ', \'' . $this->getUsernameByID($userID) . '\')">Remove</button>';
            }
            if ($nightmodeEnabled) {
                if ($toggle) {
                    $color = '#1a1a1a';
                } else {
                    $color = '#333';
                }
            } else {
                if ($toggle) {
                    $color = '#fff';
                } else {
                    $color = '#f2f2f2';
                }
            }
            $html .=  '<tr style="background-color:' . $color . ';">
                    <td>' . $this->getUsernameByID($userID) . '</td>
                    <td>
                        ' . $removeAccessHTML . '
                    </td>
                </tr>';
            $toggle = !$toggle;
            unset($removeAccessHTML);
        }
        $html .= '</table>
            </div>';
        return $html;
    }

    public function getUserType($userID)
    {
        $user = $this->getUserData($userID);
        return $user->userType;
    }

    public function getPriorityColor($priority)
    {
        switch ($priority) {
            case 1:
                return 'green';

            case 2:
                return '#ffcc00';

            case 3:
                return 'red';

            default:
                return 'black';
        }
    }

    private function getTaskCount($type, $groupID, $state)
    {
        $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = ? AND taskParentID = ? AND taskState = ?";
        $data = $this->mysqliSelectFetchObject($sql, $type, $groupID, $state);
        if ($data != null) {
            if ((int) $data->number > 0) {
                $taskCount = '(' . $data->number . ')';
            } else {
                $taskCount = '';
            }
        }
        return $taskCount;
    }

    public function getUserIDByMail($mail)
    {
        $sql = "SELECT userID FROM users WHERE userMail = ?";
        $data = $this->mysqliSelectFetchObject($sql, $mail);
        return $data->userID;
    }

    public function getUserIDByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE userName = ?";
        $data = $this->mysqliSelectFetchObject($sql, $username);
        return $data->userID;
    }

    public function getUsernameByID($userID)
    {
        if ($userID == null || $userID == 'unknown' || $userID == 'Auto-Created') {
            return $userID;
        }
        $sql = "SELECT * FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userName;
    }

    /**
     * get number (int) how many group invites a user has
     */
    public function getUserGroupInvitesCount($userID)
    {
        $data = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tokens WHERE tokenType = 'joingroup' AND tokenUserID = ?;", $userID);
        if ($data) {
            return $data->number;
        }
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
    public function getUserVerificationState($userID)
    {
        $sql = "SELECT userMailState FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userMailState == 'verified';
    }

    public function getTasksByGroupID($groupID)
    {
        $sql = "SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? ORDER BY taskID DESC";
        return $this->mysqliSelectFetchArray($sql, $groupID);
    }

    public function getTaskType($taskID)
    {
        $taskData = $this->mysqliSelectFetchObject("SELECT taskType FROM tasks WHERE taskID = ?", $taskID);
        return $taskData->taskType;
    }

    public function getWeek()
    {
        if (date('W') % 2 == 1) {
            return 'odd';
        } else {
            return 'even';
        }
    }

    public function getWeekday()
    {
        return date('D');
    }

    public function groupOwnerCheck($groupID, $userID)
    {
        $groupOwnerID = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $groupOwnerID->groupOwner == $userID;
    }

    public function localstorageCreateJSCode()
    {
        $groups = $this->sqlGetActiveGroups();
        $localStorageInit = "
        var json = '{}';
        var obj = JSON.parse(json);
        ";
        foreach ($groups as $group) {
            $localStorageInit .= "obj['$group->groupID'] = '$group->groupName';\n";
        }
        $localStorageInit .= "localStorage.setItem('Groups', JSON.stringify(obj));";
        return $localStorageInit;
    }

    public function localstorageGroupUpdate($destionationUrl)
    {
        echo "
        <script>
            localStorage.removeItem('Groups');
            " . $this->localstorageCreateJSCode() . "
        </script>
        <META HTTP-EQUIV=\"refresh\" content=\"0;URL=$destionationUrl\">
        ";
        exit;
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

    private function moveToArchive($id)
    {
        $this->mysqliQueryPrepared("UPDATE tasks SET taskState = 'archived' WHERE taskID = ?", $id);
    }

    private function parseParent($taskType, $parentID)
    {
        if ($taskType == 'task') {
            return '<a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $parentID . '">' . $this->getGroupNameByID($parentID) . '</a>';
        } else {
            return '<a href="' . DIR_SYSTEM . 'php/details.php?action=taskDetails&id=' . $parentID . '">' . $parentID . '</a>';
        }
    }

    public function printArchive()
    {
        $html = '<div class="group-box">
                    <div class="group-top-bar">
                        Archive
                    </div>';
        $html .= $this->printTaskTable($this->getArchivedTasks());
        $html .=  '</div>';
        echo $html;
    }

    public function printComments($id, $type)
    {
        $sql = "SELECT * FROM comments WHERE commentTaskID = ?";
        $data = $this->mysqliSelectFetchArray($sql, $id);
        if ($data != null) {
            $html = '<table>';
            foreach ($data as $i) {
                $html .= '
                    <tr class="comment-background">
                        <td width="15%">' . $this->getUsernameByID($i->commentAutor) . ':</td>
                        <td style="font-size:14px;">' . $this->addTagsToUrlsInString($i->commentDescription) . '</td>
                        <td width="18%">' . $i->commentDate . '</td>
                        <td style="white-space: nowrap;">
                            <div class="editgroup-button" onclick="openEditCommentForm(' . $i->commentID . ', \'' . $i->commentDescription . '\')">
                                Edit
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </div>
                            <div class="editgroup-button" onclick="deleteComment(' . $i->commentID . ', ' . $i->commentTaskID . ')">
                                Delete
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>
                        </td>
                    </tr>';
            }
            $html .= '</table>';
        } else {
            $html =  '';
        }
        $html .= '<form action="action.php?action=createComment&id=' . $id . '&type=' . $type . '" autocomplete="off" method="post">
                    <table>
                        <tr>
                            <td><textarea cols="40" rows="3" type="text" name="description"></textarea></td>
                        </td>
                        <tr>
                            <td style="float:right;"><input type="submit" name="createcomment-submit" value="Comment"/></td>
                        </td>
                    </table>
                </form>
            </div>';
        return $html;
    }

    private function printGroup($group)
    {
        $groupName = $group->groupName;
        $groupID = $group->groupID;
        $openTasksCount = $this->getTaskCount('task', $groupID, 'open');
        $assignedTasksCount = $this->getTaskCount('task', $groupID, 'assigned');
        $finishedTasksCount = $this->getTaskCount('task', $groupID, 'finshed');

        $groupContentID = 'groupContent_' . $groupName;
        $groupUnfoldButtonID = 'groupUnfoldButton_' . $groupName;

        $mobileLine = '';
        if ($openTasksCount != '') {
            $mobileLine .= $openTasksCount . ' Open ';
        }
        if ($assignedTasksCount != '') {
            $mobileLine .= $assignedTasksCount . ' In progress';
        }

        $html =  '
        <div class="group-box">
            <div class="group-top-bar">
                <div class="group_top_bar_left">
                    <a href="php/details.php?action=groupDetails&id=' . $groupID . '"><p>' . $groupName . '</p></a>
                </div>
                <div class="group_top_bar_right">
                    <p>' . $mobileLine . '</p>
                    <div class="group_dropbtn" id="' . $groupUnfoldButtonID . '" onclick="toggleUnfoldArea(\'' . $groupContentID . '\',\'' . $groupUnfoldButtonID . '\')">
                        <p><i class="fa fa-caret-down" aria-hidden="true"></i></p>
                    </div>
                </div>
            </div>
            <div class="group-content" id="groupContent_' . $groupName . '">
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>Open ' . $openTasksCount . '</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'open' ORDER BY taskPriority DESC, taskID ", $groupID);
        $html .=  '
                </div>
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>In progress ' . $assignedTasksCount . '</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'assigned' ORDER BY taskPriority DESC, taskDateAssigned", $groupID);
        $html .=  '
                </div>
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>Done ' . $finishedTasksCount . '</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'finished' ORDER BY taskDateFinished", $groupID);
        $html .=  '
                </div>
            </div>
        </div>';
        if ($this->getGroupUnfolded($_SESSION['userID'], $groupID) == 'true') {
            $html .= '<script>toggleUnfoldArea(\'' . $groupContentID . '\',\'' . $groupUnfoldButtonID . '\', \'true\')</script>';
        }
        echo $html;
    }

    public function printGroups($groups)
    {
        $counter = 0;
        foreach ($groups as $group) {
            $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND NOT taskState = 'archived'";
            $data = $this->mysqliSelectFetchObject($sql, $group->groupID);
            if ((int) $data->number > 0) {
                $this->printGroup($group);
                $counter += 1;
            }
        }
        if ($counter == 0) {
            echo '
            <div = class="emptypage-modal">
                <div class="emptypage">Nothing to do, go create some tasks or groups and start working :-)</div>
            </div>
            ';
        }
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
                    <th>STATE</th>
                    <th>PRIORITY</th>
                    <th>TOTAL_NUM_OF_TASKS</th>
                    <th>CURRENTLY_OPEN</th>
                    <th>CURRENTLY_IN_PROGRESS</th>
                    <th></th>
                </tr>';

        if ($groups != null) {
            if ($this->getNightmodeEnabled($_SESSION['userID'])) {
                $backgroundColor = '#333333';
            } else {
                $backgroundColor = '#fff';
            }
            $toggle = true;
            foreach ($groups as $group) {
                $groupID = $group->groupID;
                $totalTasks = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $groupID);
                $openTasks = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'open'", $groupID);
                $tasksInProgress = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'assigned'", $groupID);

                if ($_SESSION['userID'] == $this->getGroupOwnerID($groupID)) {
                    $deleteOrLeaveGroup = '<td><button type="button" onclick="deleteGroup(' . $groupID . ')">Delete Group</button>';
                } else {
                    $deleteOrLeaveGroup = '<button type="button" onclick="leaveGroup(' . $groupID . ')">Leave Group</button>';
                }

                $toggle = !$toggle;
                if ($toggle) {
                    $html .= '<tr style="background-color:' . $backgroundColor . ';">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '
                    <td>' . $groupID . '</td>
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $groupID . '">' . $group->groupName . '</a></td>
                    <td>' . $group->groupState . '</td>
                    <td>' . $group->groupPriority . '</td>
                    <td>' . $totalTasks->number . '</td>
                    <td>' . $openTasks->number . '</td>
                    <td>' . $tasksInProgress->number . '</td>
                    <td>' . $deleteOrLeaveGroup . '</td>
                </tr>
                ';
            }
        }
        $html .= '</table></div>';
        echo $html;
    }

    public function printGroupDetails($group)
    {
        $groupID = $group->groupID;
        $userID = $_SESSION['userID'];
        $groupOwnerCheck = $userID == $this->getGroupOwnerID($groupID);
        if ($groupOwnerCheck) {
            $editGroupButton = '
            <div class="button" onclick="openEditGroupForm(' . $groupID . ', \'' . $group->groupName . '\', ' . $group->groupPriority . ', ' . $group->groupArchiveTime . ')">
                <p><i class="fa fa-edit" aria-hidden="true"></i></p>
            </div>';
        }
        $html = '<div class="group-box">
                    <div class="top-bar">
                        <div class="top-bar-left">
                            <div class="top_bar_title"><p>' . $group->groupName . '</p></div>
                            <div class="button" onclick="openShowUsersPopup()">
                                <i class="fa fa-user fa-2x" aria-hidden="true"></i>
                            </div>
                            ' . $editGroupButton . '
                        </div>
                        <div class="top-bar-right">
                            <div class="dropbtn" id="groupdetailsUnfoldButton" onclick="toggleUnfoldArea(\'groupDetailsButtons\',\'groupdetailsUnfoldButton\')">
                                <p><i class="fa fa-caret-down" aria-hidden="true"></i></p>
                            </div>
                        </div>';

        $inviteToken = $this->mysqliSelectFetchObject("SELECT tokenToken FROM tokens WHERE tokenGroupID = ? AND tokenType = 'groupinvite'", $groupID);
        if ($groupOwnerCheck) {
            if ($group->groupInvites == 'enabled') {
                $groupInvites = '
                ' . DIR_SYSTEM . 'php/action.php?action=joingroup&t=' . $inviteToken->tokenToken . '
                <div class="panel-item-top-bar-button">
                    <a href="' . DIR_SYSTEM . 'php/action.php?action=refreshinvite&id=' . $groupID . '"> <i class="fa fa-refresh" aria-hidden="true"></i> </a>
                </div>
                <form action="action.php?action=groupinvites&invites=disable&id=' . $groupID . '" autocomplete="off" method="post" >
                    <input class="button" type="submit" name="groupinvites-submit" value="Disable Invites"/>
                </form>
            ';
            } else {
                $groupInvites = '
            <form action="action.php?action=groupinvites&invites=enable&id=' . $groupID . '" autocomplete="off" method="post" >
            <input class="button" type="submit" name="groupinvites-submit" value="Enable Invites"/>
            </form>
            ';
            }
        }

        if ($groupOwnerCheck && $group->groupState == 'active') {
            $changeGroupState = '
                <form action="action.php?action=groupstate&state=hide&id=' . $groupID . '" autocomplete="off" method="post" >
                    <input class="button" type="submit" name="groupstate-submit" value="Hide Group"/>
                </form>
            ';
        } else if ($groupOwnerCheck && $group->groupState == 'hidden') {
            $changeGroupState = '
                <form action="action.php?action=groupstate&state=activate&id=' . $groupID . '" autocomplete="off" method="post" >
                    <input class="button" type="submit" name="groupstate-submit" value="Show Group"/>
                </form>
            ';
        }

        if ($groupOwnerCheck) {
            $inviteUser = '<form action="action.php?action=generateToken&id=' . $groupID . '" autocomplete="off" method="post" >
                    <input type="text" name="name" placeholder="username"/>
                    <input class="button" type="submit" name="groupinvite-submit" value="Invite"/>
                </form>';
            $deleteGroup = '<button class="button" type="button" onclick="deleteGroup(' . $groupID . ')">Delete Group</button>';
        } else {
            $leaveGroup = '<button class="button" type="button" onclick="leaveGroup(' . $groupID . ')">Leave Group</button>';
        }

        if($this->getGroupUnfolded($userID, $groupID) == 'true') $groupUnfoldedCheckbox = 'checked';
        $groupUnfolded = '<div>
                <input id="groupUnfoldCheckbox" type="checkbox" ' . $groupUnfoldedCheckbox . '>
                <small>Unfolded by default on mobile</small>
            </div>
            <script>groupUnfoldCheckboxListener('.$groupID.')</script>';

        $html .= '</div>
            <div class="group__deatils__buttons__hidden" id="groupDetailsButtons">
                ' . $groupUnfolded . '
                ' . $groupInvites . '
                ' . $inviteUser . '
                ' . $changeGroupState . '
                ' . $leaveGroup . '
                ' . $deleteGroup . '
            </div>
                <div class="group__details__content">
                ';
        $html .= $this->printTaskTable($this->getTasksByGroupID($groupID));
        $html .= '</div>
        </div>

        <div class="bg-modal" id="bg-modal-groupusers">
            <div class="modal-content" id="groupusers-modal-content">
                <div class="modal-header">
                  Groupusers
                  <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-groupusers"></i>
                </div>
                ' . $this->getUserListHTML($groupID) . '
            </div>
        </div>';
        echo $html;
    }

    public function printInviteMessages($userID)
    {
        $invites = $this->getGroupInvites($userID);
        if ($invites) {
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

    public function printPanelContentDetails($type)
    {
        if ($type == 'appointment') {
            $sql = "SELECT * FROM messages WHERE messageType = 'appointment' AND messageOwner = ? ORDER BY messageDate, messageID DESC";
        } else if ($type == 'motd') {
            $sql = "SELECT * FROM messages WHERE messageType = 'motd' AND messageOwner = ? ORDER BY messageDate, messageID DESC";
        } else if ($type == 'rt') {
            $sql = "SELECT * FROM messages WHERE messageType = 'repeatingtask' AND messageOwner = ? ORDER BY messageDate, messageID DESC";
        }
        $data = $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);

        if ($data != null) {
            foreach ($data as $i) {
                $tasks[] = $i;
            }
        }

        $html = '<div class="group-box">
                <div class="big-top-bar">
                    <div class="top-bar-item">Repeating Tasks</div>
                </div>
            </div>
            <div class="group-content">
            ';

        $html =  '
            <table>
                <tr>
                    <td>ID</td>
                    <td>GROUP</td>
                    <td>TITLE</td>
                    <td>WEEKDAY</td>
                    <td>QUANTITY</td>
                    <td>STATE</td>
                    <td>DATE</td>
                    <td></td>
                </tr>';

        if ($tasks != null) {
            if ($this->getNightmodeEnabled($_SESSION['userID'])) {
                $backgroundColor = '#333333';
            } else {
                $backgroundColor = '#f2f2f2';
            }
            $toggle = true;
            foreach ($tasks as $task) {
                $toggle = !$toggle;
                if ($toggle) {
                    $html .= '<tr style="background-color:' . $backgroundColor . ';">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '
                    <td>' . $task->messageID . '</td>
                    <td>' . $this->getGroupNameByID($task->messageGroup) . '</td>
                    <td>' . $task->messageTitle . '</td>
                    <td>' . $task->messageWeekday . '</td>
                    <td>' . $task->messageQuantity . '</td>
                    <td>' . $task->messageState . '</td>
                    <td>' . $task->messageDate . '</td>
                    <td>
                        <div class="panel-item-delete-button" onclick="deleteMessage(\'' . $task->messageID . '\')">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </div>
                    </td>
                </tr>
                ';
            }
        }

        $html .= '</table>
            </div>';
        echo $html;
    }

    private function printPanel($type, $unfolded, $spec = '')
    {
        return '<div class="panel-item">' . $this->panelHeader($type) . $this->panelContent($type, $unfolded, $spec) . '</div>';
    }

    private function panelHeader($type)
    {
        if ($type == 'appointment') {
            $createButtonID = 'createAppointmentButton';
            $onclick = 'panels.openAddAppointmentForm()';
            $detailsActionName = 'appointmentDetails';
            $unfoldButtonID = 'appointmentUnfoldButton';
            $contentAreaID = 'appointmentPanelContentArea';
            $titleID = 'appointmentPanelTitle';
            $title = '';
        } else if ($type == 'motd') {
            $createButtonID = 'createMOTDButton';
            $onclick = 'panels.openAddMotdForm()"';
            $detailsActionName = 'motdDetails';
            $unfoldButtonID = 'motdUnfoldButton';
            $contentAreaID = 'motdPanelContentArea';
            $titleID = 'motdPanelTitle';
            $title = '';
        } else if ($type == 'queue') {
            return '<div class="panel-item-top-bar">
                    <div class="top-bar-left">
                        <p id="queuePanelTitle"></p>
                    </div>
                    <div class="top-bar-right">
                        <input type="text" id="queueItem" name="queueItem">
                        <input type="checkbox" id="queueHighprio" name="queueHighprio" style="outline: 1px solid red;">
                        <input type="submit" id="queueSubmit "name="add-queue-submit" value="Add" onclick="panels.addQueueTask()"/>
                        <div class="panel_item_top_bar_unfold_button" id="queueUnfoldButton" onclick="toggleUnfoldArea(\'queuePanelContentArea\',\'queueUnfoldButton\')">
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>';
        } else if ($type == 'weather') {
            return '<div class="panel-item-top-bar">
                    <div class="top-bar-left">
                        <p>Weather</p>
                    </div>
                    <div class="panel_item_top_bar_unfold_button" id="weatherUnfoldButton" onclick="toggleUnfoldArea(\'weatherPanelContentArea\',\'weatherUnfoldButton\')">
                       <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </div>
                </div>';
        } else if ($type == 'timetable') {
            return '<div class="panel-item-top-bar">
                    <div class="top-bar-left">
                        <p>Timetable (KW' . date("W") . ')</p>
                    </div>
                    <div class="top-bar-right">
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
        }

        return '<div class="panel-item-top-bar">
            <div class="top-bar-left">
                <p id="' . $titleID . '">' . $title . '</p>
            </div>
            <div class="top-bar-right">
                <div class="panel-item-top-bar-button" id="' . $createButtonID . '" onclick="' . $onclick . '">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </div>
                <div class="panel-item-top-bar-button">
                    <a href="' . DIR_SYSTEM . 'php/details.php?action=' . $detailsActionName . '"> <i class="fa fa-list" aria-hidden="true"></i> </a>
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
                <div class="weather__block">
                  <div class="weather__date" id="weatherPrevDate' . $i . '"></div>
                  <img src="" alt="" id="weatherPrevIcon' . $i . '" />
                  <div class="weather__temp" id="weatherPrevTemp' . $i . '"></div>
                </div>';
            }
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'weatherPanelContentArea\',\'weatherUnfoldButton\', \'true\')';
            return '<div class="weather__panel__content" id="weatherPanelContentArea">
                    <div class="weather">
                        <div class="weather__input">
                            <form action="' . DIR_SYSTEM . 'php/action.php?action=updateWeatherCity" autocomplete="off" method="post" >
                                <input type="text" name="city" placeholder="cityname">
                                <input type="submit" name="update-weather-submit" value="Update" />
                            </form>
                        </div>
                        <h2 class="weather__city"><h2>
                        <div class="weather__block">
                          <img src="" alt="" class="weather__icon" />
                          <div class="weather__temp"></div>
                        </div>
                        <div class="weather__description"></div>
                        <div class="weather__humidity"></div>
                        <div class="weather__wind"></div>
                    </div>
                    <div class="weather__forecast">
                        <h2 class="weather__city">5-Day Forecast<h2>
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
                    "SELECT * FROM timetableentrys WHERE timetableID = ? AND timetableDate = ? ORDER BY timetableTimeStart",
                    $spec,
                    date('Y-m-d')
                );
                if ($tasks) {
                    $currentTime = date('H:i');
                    $nextTask = null;
                    for ($i = 0; $i < count($tasks); $i++) {
                        if ($currentTime > $tasks[$i]->timetableTimeEnd) {
                            $prevTask = $tasks[$i];
                        }
                        if ($currentTime > $tasks[$i]->timetableTimeStart && $currentTime < $tasks[$i]->timetableTimeEnd) {
                            $activeTask = $tasks[$i];
                        }
                        if ($currentTime < $tasks[$i]->timetableTimeStart && !$nextTask) {
                            $nextTask = $tasks[$i];
                            if ($i < count($tasks) - 1) $nextTask2 = $tasks[$i + 1];
                        }
                    }
                }
            }
            $content = '';
            if ($prevTask) {
                $content .= '<div class="timetable__panel__prevtask">
                        <div class="timetable__content__task__time">' . $prevTask->timetableTimeStart . ' - ' . $prevTask->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $prevTask->timetableText . '</div>
                    </div>';
            }
            if ($activeTask) {
                $content .= '<div class="timetable__panel__activetask">
                        <div class="timetable__content__task__time">' . $activeTask->timetableTimeStart . ' - ' . $activeTask->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $activeTask->timetableText . '</div>
                    </div>';
            }
            if ($nextTask) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask->timetableTimeStart . ' - ' . $nextTask->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask->timetableText . '</div>
                    </div>';
            }
            if ($nextTask2) {
                $content .= '<div class="timetable__panel__nexttask">
                        <div class="timetable__content__task__time">' . $nextTask2->timetableTimeStart . ' - ' . $nextTask2->timetableTimeEnd . '</div>
                        <div class="timetable__content__task__text">' . $nextTask2->timetableText . '</div>
                    </div>';
            }
            if ($unfolded == 'true') $unfoldPanel = 'toggleUnfoldArea(\'timetablePanelContentArea\',\'timetableUnfoldButton\', \'true\')';
            return '<div class="panel-item-area" id="timetablePanelContentArea">
                    ' . $content . '
                    <script>' . $unfoldPanel . '</script>
                </div>';
        }
    }

    public function printPanels()
    {
        $userID = $_SESSION['userID'];
        $panelData = $this->mysqliSelectFetchObject("SELECT * FROM panels WHERE userID = ?", $userID);
        $panelHTML = '';
        $panelCounter = 0;
        if ($panelData->panelMOTD == 'true') {
            $panelHTML .= $this->printPanel('motd', $panelData->panelMOTDUnfolded);
            $panelCounter++;
        }
        if ($panelData->panelAppointment == 'true') {
            $panelHTML .= $this->printPanel('appointment', $panelData->panelAppointmentUnfolded);
            $panelCounter++;
        }
        if ($panelData->panelQueue == 'true') {
            $panelHTML .= $this->printPanel('queue', $panelData->panelQueueUnfolded);
            $panelCounter++;
        }
        if ($panelData->panelWeather == 'true') {
            $panelHTML .= $this->printPanel('weather', $panelData->panelWeatherUnfolded, $panelData->panelWeatherCity);
            $panelCounter++;
        }
        if ($panelData->panelTimetable == 'true') {
            $timetable = $this->mysqliSelectFetchObject("SELECT timetableID FROM timetables WHERE timetableUserID = ? AND timetableWeek = ?", $userID, date('W'));
            $panelHTML .= $this->printPanel('timetable', $panelData->panelTimetableUnfolded, $timetable->timetableID);
            $panelCounter++;
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

    private function printSubtaskPanel($id)
    {
        $openTasksCount = $this->getTaskCount('subtask', $id, 'open');
        $assignedTasksCount = $this->getTaskCount('subtask', $id, 'assigned');
        $finishedTasksCount = $this->getTaskCount('subtask', $id, 'finshed');
        $html = '
            <div class="taskdetails_panel_right">
                <div class="group-box">
                    <div class="group-top-bar">
                        <div class="group_top_bar_left">
                            <p>Subtasks</p>
                        </div>
                        <div class="group_top_bar_right">
                            <div class="group_dropbtn" id="groupUnfoldButton_subtask" onclick="toggleUnfoldArea(\'groupContent_subtask\',\'groupUnfoldButton_subtask\')">
                                <p><i class="fa fa-caret-down" aria-hidden="true"></i></p>
                            </div>
                        </div>
                    </div>
                    <div class="group-content" id="groupContent_subtask">
                        <div class="single__content__subtask">
                            <div class="single-top-bar">
                            <p>Open ' . $openTasksCount . '</p>
                            </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'open' ORDER BY taskPriority DESC", $id);
        $html .= '</div>
                    <div class="single__content__subtask">
                        <div class="single-top-bar">
                        <p>In progress ' . $assignedTasksCount . '</p>
                        </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'assigned' ORDER BY taskDateAssigned", $id);
        $html .= '</div>
                    <div class="single__content__subtask">
                        <div class="single-top-bar">
                        <p>Done ' . $finishedTasksCount . '</p>
                        </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'finished' ORDER BY taskDateFinished", $id);
        $html .=    '</div>
                </div>
            </div>
        </div>';
        return $html;
    }

    private function printTask($taskData)
    {
        switch ($taskData->taskState) {
            case 'open':
                $dateDiff = $this->getDateDifference($taskData->taskDateCreated);
                break;
            case 'assigned':
                $dateDiff = $this->getDateDifference($taskData->taskDateAssigned);
                break;
            case 'finished':
                $dateDiff = $this->getDateDifference($taskData->taskDateFinished);
                break;
            default:
                break;
        }

        if ($taskData->taskType == 'task' && $taskData->taskState == 'finished' && $this->archiveCheck($taskData->taskParentID, $dateDiff)) {
            $this->moveToArchive($taskData->taskID);
        } else {
            $html = '<a href="' . DIR_SYSTEM . 'php/details.php?action=taskDetails&id=' . $taskData->taskID . '">
                <div class="box">
                    <div class="priority" style="background-color: ' . $taskData->taskPriorityColor . ';"></div>';
            $html .= '<div class="content">
                <div class="text">
                    ' . $taskData->taskTitle . '
                </div>
                <div class="emptyspace">&nbsp;</div>';

            $html .= '<div class="bottom">
                <div class="bottom_label">
                id_' . $taskData->taskID . '
                </div>';

            if ($taskData->taskAssignedBy) {
                $userID = $taskData->taskAssignedBy;
                $sql = "SELECT * FROM users WHERE userID = ?";
                $userData = $this->mysqliSelectFetchObject($sql, $userID);
                $assignerShort = $userData->userNameShort;
                $html .= '<div class="bottom_label">' . $assignerShort . '</div>';
            }

            if ($taskData->taskState != 'finished') {
                if ($taskData->taskState == 'open' && $dateDiff == 0) {
                    $html .= '<div class="new_label">NEW</div>';
                } else if ($taskData->taskState == 'open' && $dateDiff > 31) {
                    $html .= '<div class="bottom_label" style="background-color:red;color:#fff;">' . $dateDiff . '</div>';
                } else {
                    $html .= '<div class="bottom_label">' . $dateDiff . '</div>';
                }
            }
            $html .= $this->getNumberOfSubtasks($taskData->taskID);
            $html .= '</div>
                    </div>
                </div>
            </a>';

            return $html;
        }
    }

    public function printTaskDetails($task, $id)
    {
        $html = $this->printTaskDetailsNew($task);
        $subtaskcount = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?", $task->taskID);
        if ($subtaskcount->number > 0) {
            $html .= $this->printSubtaskPanel($id);
        }
        $this->addTaskDataToLocalstorage($task);
        echo $html;
    }

    public function printTaskDetailsNew($task)
    {
        if ($task->taskType == 'subtask') {
            $backButton = '<a href="' . DIR_SYSTEM . 'php/details.php?action=taskDetails&id=' . $task->taskParentID . '"> 
                <div class="button"><p><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</p></div></a>';
        } else {
            $backButton = '';
        }
        $buttons = '<button class="button" onclick="openUpdateTaskForm()">Update</button>
            <button class="button" type="button" onclick="deleteTask(\'' . $task->taskID . '\')">Delete</button>
            <button class="button" id="createSubtaskButton" type="button">Create Subtask</button>';
        switch ($task->taskState) {
            case 'open':
                $buttons .= '<form action="action.php?action=assign&id=' . $task->taskID . '" autocomplete="off" method="post" ><input class="button" type="submit" name="assign-submit" value="Start Work"/></form>';
                break;

            case 'assigned':
                $buttons .= '<form action="action.php?action=stateOpen&id=' . $task->taskID . '" autocomplete="off" method="post" ><input class="button" type="submit" name="stateopen-submit" value="Back to Open"/></form>';
                $buttons .= '<form action="action.php?action=finishTask&id=' . $task->taskID . '" autocomplete="off" method="post" ><input class="button" type="submit" name="finish-submit" value="Finish"/></form>';
                break;

            case 'finished':
                $buttons .= '<form action="action.php?action=stateOpen&id=' . $task->taskID . '" autocomplete="off" method="post" ><input class="button" type="submit" name="stateopen-submit" value="Back to Open"/></form>';
                $buttons .= '<form action="action.php?action=assign&id=' . $task->taskID . '" autocomplete="off" method="post" ><input class="button" type="submit" name="assign-submit" value="Back to in progress"/></form>';
                break;

            default:
                break;
        }
        $priority = $task->taskPriority;
        switch ($priority) {
            case 1:
                $priority = 'low';
                break;

            case 2:
                $priority = 'normal';
                break;

            case 3:
                $priority = 'high';
                break;

            default:
                break;
        }
        $html = '
            <div class="taskdetails_panel">
                <div class="taskdetails_panel_left">
                    <div class="top-bar">
                        <div class="top-bar-left">' . $backButton . '</div>
                        <div class="top-bar-right">' . $buttons . '</div>
                    </div>
                    <table style="clear:both;">
                        <tr>
                            <td>ID:</td>
                            <td>' . $task->taskID . '</td>
                        </tr>
                        <tr>
                            <td>Priority:</td>
                            <td>' . $priority . '</td>
                        </tr>
                        <tr>
                            <td>Parent:</td>
                            <td>' . $this->parseParent($task->taskType, $task->taskParentID) . '</td>
                        </tr>
                        <tr>
                            <td>Title:</td>
                            <td>' . $task->taskTitle . '</td>
                        </tr>
                        <tr>
                            <td>Description:</td>
                            <td>' . $this->addTagsToUrlsInString($task->taskDescription) . '</td>
                        </tr>
                        <tr>
                            <td>State:</td>
                            <td>' . $task->taskState . '</td>
                        </tr>
                        <tr>
                            <td>Date Created:</td>
                            <td>' . $task->taskDateCreated . '</td>
                        </tr>
                        <tr>
                            <td>Date Assigned:</td>
                            <td>' . $task->taskDateAssigned . '</td>
                        </tr>
                        <tr>
                            <td>Assigned By:</td>
                            <td>' . $this->getUsernameByID($task->taskAssignedBy) . '</td>
                        </tr>
                        <tr>
                            <td>Date Finished:</td>
                            <td>' . $task->taskDateFinished . '</td>
                        </tr>
                    </table>
        ';
        $html .= $this->printComments($task->taskID, $task->taskType);
        return $html;
    }

    private function printTaskTable($tasks)
    {
        $html =  '
            <table style="margin-top:10px;">
                <tr">
                    <th>ID</th>
                    <th>TITLE</th>
                    <th>DESCRIPTION</th>
                    <th>GROUP_ID</th>
                    <th>PRIORITY</th>
                    <th>DATE_CREATED</th>
                    <th>DATE_ASSIGNED</th>
                    <th>ASSIGNED_BY</th>
                    <th>DATE_FINISHED</th>
                    <th></th>
                </tr>';
        if ($tasks != null) {
            if ($this->getNightmodeEnabled($_SESSION['userID'])) {
                $backgroundColor = '#333333';
            } else {
                $backgroundColor = '#fff';
            }
            $toggle = true;
            foreach ($tasks as $task) {
                $toggle = !$toggle;
                if ($toggle) {
                    $html .= '<tr style="background-color:' . $backgroundColor . ';">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=taskDetails&id=' . $task->taskID . '">' . $task->taskID . '</a></td>
                    <td>' . $task->taskTitle . '</td>
                    <td>' . $task->taskDescription . '</td>
                    <td>' . $task->taskParentID . '</td>
                    <td>' . $task->taskPriority . '</td>
                    <td>' . $task->taskDateCreated . '</td>
                    <td>' . $task->taskDateAssigned . '</td>
                    <td>' . $this->getUsernameByID($task->taskAssignedBy) . '</td>
                    <td>' . $task->taskDateFinished . '</td>
                    <td style="white-space: nowrap;">
                        <div class="editgroup-button" onclick="deleteTask(' . $task->taskID . ')">
                            Delete
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </div>
                    </td>
                </tr>
                ';
            }
        }

        $html .= '</table>';
        return $html;
    }

    public function printTasksFromSameState($sql, $id)
    {
        $data = $this->mysqliSelectFetchArray($sql, $id);

        $html = '';
        if ($data != null) {
            foreach ($data as $i) {
                $html .= $this->printTask($i);
            }
        }
        return $html;
    }

    private function addTaskDataToLocalstorage($task)
    {
        echo "
        <script>
            var json = '{}';
            var obj = JSON.parse(json);

            obj['taskID'] = $task->taskID;
            obj['taskType'] = '$task->taskType';
            obj['taskParentID'] = $task->taskParentID;
            obj['taskPriority'] = $task->taskPriority;
            obj['taskTitle'] = '$task->taskTitle';
            obj['taskDescription'] = '" . preg_replace('/\s+/', ' ', $task->taskDescription) . "';
            localStorage.setItem('TaskData', JSON.stringify(obj));
        </script>";
    }

    public function printUserDetails($userID)
    {
        $userData = $this->mysqliSelectFetchObject("SELECT * FROM users WHERE userID = ?", $userID);
        $ownedGroups = $this->mysqliSelectFetchArray("SELECT * FROM groups WHERE groupOwner = ?", $userID);
        $groupAccess = $this->mysqliSelectFetchArray("SELECT * FROM groupaccess WHERE userID = ?", $userID);

        $backButton = '<div style="float:left;"><a href="' . DIR_SYSTEM . 'php/admin.php">
            <div class="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</div></a></div>';

        $ownedGroupsHTML = '';
        if ($ownedGroups) {
            foreach ($ownedGroups as $group) {
                $ownedGroupsHTML .= '
                <tr>
                    <td><a href="' . DIR_SYSTEM . 'php/details.php?action=groupDetails&id=' . $group->groupID . '">' . $group->groupName . '</a></td>
                </tr>';
            }
        }

        $groupAccessHTML = '';
        if ($ownedGroups) {
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
                    <td>Mail-State</td>
                    <td>' . $userData->userMailState . '</td>
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

    public function printVerifyMailMessage($userID)
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

    public function sqlGetActiveGroups($userID = '')
    {
        $sql = "SELECT g.* 
                FROM groups g
                    LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
                WHERE  ga.userID = ? AND g.groupState = 'active'
                ORDER BY g.groupPriority DESC";
        if ($userID == '') {
            return $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        } else {
            return $this->mysqliSelectFetchArray($sql, $userID);
        }
    }

    public function sqlGetAllGroups($userID = '')
    {
        $sql = "SELECT g.* 
                FROM groups g
                    LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
                WHERE  ga.userID = ?
                ORDER BY g.groupPriority DESC";
        if ($userID == '') {
            return $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        } else {
            return $this->mysqliSelectFetchArray($sql, $userID);
        }
    }

    public function sqlGetHiddenGroups($userID = '')
    {
        $sql = "SELECT g.* 
                FROM groups g
                    LEFT JOIN groupaccess ga ON g.groupID = ga.groupID
                WHERE  ga.userID = ? AND g.groupState = 'hidden'
                ORDER BY g.groupPriority DESC";
        if ($userID == '') {
            return $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        } else {
            return $this->mysqliSelectFetchArray($sql, $userID);
        }
    }
}
