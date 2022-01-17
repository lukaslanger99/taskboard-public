<?php
class TaskBoard {
    public function mysqliConnect() {
        $mysqli = new mysqli(SERVER_NAME, USER, PASS, DB);

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        return $mysqli;
    }

    public function mysqliQueryPrepared($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '') {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
        }
        else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
            }
            else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            }
            else if ($value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            }
            else  if ($value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            }
            else  if ($value5 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            }
            else {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            }
            mysqli_stmt_execute($stmt);
        }
    }

    public function mysqliSelectFetchObject($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '') {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
        }
        else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
            }
            else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            }
            else if ($value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            }
            else  if ($value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            }
            else  if ($value5 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            }
            else {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_object($result);
            }
        }
    }

    public function mysqliSelectFetchArray($sql, $value = '', $value2 = '', $value3 = '', $value4 = '', $value5 = '') {
        $mysqli = $this->mysqliConnect();
        $stmt = mysqli_stmt_init($mysqli);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            var_dump($sql);
            $this->locationIndex("?error=sqlerror");
        }
        else {
            if ($value == '' && $value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
            }
            else if ($value2 == '' && $value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "s", $value);
            }
            else if ($value3 == '' && $value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "ss", $value, $value2);
            }
            else  if ($value4 == '' && $value5 == '') {
                mysqli_stmt_bind_param($stmt, "sss", $value, $value2, $value3);
            }
            else  if ($value5 == '') {
                mysqli_stmt_bind_param($stmt, "ssss", $value, $value2, $value3, $value4);
            }
            else {
                mysqli_stmt_bind_param($stmt, "sssss", $value, $value2, $value3, $value4, $value5);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                while($obj = mysqli_fetch_object($result)){
                    $data[] = $obj;
                }
            }
            return $data;
        }
    }

    public function addTagsToUrlsInString($string) {
        $words = explode(' ', $string);
        foreach ($words as &$word) {
            if (filter_var($word, FILTER_VALIDATE_URL)) {
                $word = '<a style="text-decoration:underline;" href="'.$word.'">'.$word.'</a>';
            }
        }
        return implode(' ', $words);
    }

    /**
     * returns true if $dateDiff is higher than groupArchiveTime of $groupID
     */
    private function archiveCheck($groupID, $dateDiff) {
        $groupData = $this->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupID = ?", $groupID);
        return $dateDiff >= $groupData->groupArchiveTime;
    }

    public function checkGroupPermission($userID, $groupID) {
        if ($this->mysqliSelectFetchObject("SELECT * FROM groupaccess WHERE userID = ? AND groupID = ?", $userID, $groupID)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checkIfEmailIsTaken($email) {
        $username = $this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userMail = ?", $email);
        if ($username) {
            return 'taken';
        } else {
            return 'untaken';
        }
    }

    public function checkTaskPermission($userID, $task) {
        if ($task->taskType == 'task') {
            $groupID = $task->taskParentID;
        }
        else if ($task->taskType == 'subtask') {
            $parent = $task;
            do {
                $parent = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $parent->taskParentID);
                $type = $parent->taskType;
            } while ($type == 'subtask');
            $groupID = $parent->taskParentID;
        }
        return $this->checkGroupPermission($userID, $groupID);
    }

    public function checkUsername($username) {
        $count = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM users WHERE userName = ?", $username);
        return $count->number;
    }

    public function createComment($taskId, $type, $autor, $description, $date) {
        $sql = "INSERT INTO comments (commentTaskID, commentType, commentAutor, commentDescription, commentDate) VALUES (?, ?, '$autor', ?, '$date')";
        $this->mysqliQueryPrepared($sql, $taskId, $type, $description);
    }

    public function deleteGroup($id) {
        $this->mysqliQueryPrepared("DELETE FROM groups WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tasks WHERE taskType = 'task' AND taskParentID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM groupaccess WHERE groupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenGroupID = ?", $id);
        $this->mysqliQueryPrepared("DELETE FROM messages WHERE messageGroup = ?", $id);
    }

    public function deleteTaskPermission($taskID, $userID, $type) {
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

    public function deleteUser($userID) {
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

    public function generateRandomString($length = 21) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getArchivedTasks() {
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

    private function getDateDifference($date) {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d H:i')))->format('%r%a'); 
    }

    public function getDateDifferenceDaysOnly($date) {
        $tmpDate = new DateTime($date);
        return $tmpDate->diff(new DateTime(date('Y-m-d')))->format('%r%a'); 
    }

    public function getParentIDOfTask($taskID) {
        $task = $this->mysqliSelectFetchObject("SELECT * FROM tasks WHERE taskID = ?", $taskID);
        return $task->taskParentID;
    }

    public function getGroupNameByID($groupID) {
        if ($groupID) {
            $return = $this->mysqliSelectFetchObject("SELECT groupName FROM groups WHERE groupID = ?", $groupID);
            return $return->groupName;
        } else {
            return '';
        }
    }

    public function getGroupOwnerID($groupID) {
        $return = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $return->groupOwner;
    }

    public function getMailByUserID($userID) {
        $return = $this->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userID = ?", $userID);
        return $return->userMail;  
    }

    public function getMailState($userID) {
        $return = $this->mysqliSelectFetchObject("SELECT userMailState FROM users WHERE userID = ?", $userID);
        return $return->userMailState;
    }

    public function getNightmodeEnabled($userID) {
        $return = $this->mysqliSelectFetchObject("SELECT userNightmode FROM users WHERE userID = ?", $userID);
        return $return->userNightmode == 'true';
    }

    public function getNumberOfGroupUsers($groupID) {
        $sql = "SELECT COUNT(*) as number FROM groupaccess WHERE groupID = ?";
        $return = $this->mysqliSelectFetchObject($sql, $groupID);
        return (int) $return->number;
    }

    public function getNumberOfOwnedGroups($userID) {
        $sql = "SELECT COUNT(*) as number FROM groups WHERE groupOwner = ?";
        $return = $this->mysqliSelectFetchObject($sql, $userID);
        return (int) $return->number;
    }

    private function getNumberOfSubtasks($taskId) {
        $sql = "SELECT * FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?";
        $data = $this->mysqliSelectFetchArray($sql, $taskId);

        $open = 0;
        $inProgress = 0;
        $finished = 0;
        
        if ($data != null) {
            foreach ($data as $i) {
                if ($i->taskState == 'open') {
                    $open += 1;
                } 
                else if ($i->taskState == 'assigned') {
                    $inProgress += 1;
                } 
                else if ($i->taskState == 'finished') {
                    $finished += 1;
                } 
            }
        }

        $numberOfSubtasks = $open + $inProgress;

        if ($numberOfSubtasks == 1) {
            return '<div class="subtask-item">'.$numberOfSubtasks.' Subtask</div>';
        } else if ($numberOfSubtasks > 1) {
            return '<div class="subtask-item">'.$numberOfSubtasks.' Subtasks</div>';
        } else {
            return '';
        }
    }

    public function getUserData($userID) {
        $sql = "SELECT * FROM users WHERE userID = ?";
        return $this->mysqliSelectFetchObject($sql, $userID);
    }

    private function getUserListHTML($groupID) {
        $isOwner = $this->groupOwnerCheck($groupID, $_SESSION['userID']);
        $groupEntries = $this->mysqliSelectFetchArray("SELECT * FROM groupaccess WHERE groupID = ?", $groupID);
        $nightmodeEnabled = $this->getNightmodeEnabled($_SESSION['userID']);

        $html = '<div class="panel-item-content-item">
        <table>';
        $toggle = false;
        foreach ($groupEntries as $entry) {
            $userID = $entry->userID;
            if ($isOwner && ($userID != $_SESSION['userID'])) {
                $removeAccessHTML = '<button type="button" onclick="removeUserAccess(\''.$groupID.'\','.$userID.', \''.$this->getUsernameByID($userID).'\')">Remove</button>';
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
            $html .=  '<tr style="background-color:'.$color.';">
                    <td>'.$this->getUsernameByID($userID).'</td>
                    <td>
                        '.$removeAccessHTML.'
                    </td>
                </tr>';
            $toggle = !$toggle;
            unset($removeAccessHTML);
        }
        $html .= '</table>
            </div>';
        return $html;
    }

    public function getUserType($userID) {
        $user = $this->getUserData($userID);
        return $user->userType;
    }

    public function getPriorityColor($priority) {
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

    private function getSubtaskCount($taskId, $state) {
        $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'subtasks' AND taskParentID = ? AND taskState = ?";
        $data = $this->mysqliSelectFetchObject($sql, $taskId, $state);

        if ($data != null) {
            foreach ($data as $i) {
                if ((int) $i->number > 0) {
                    $taskCount = '('.$i->number.')';
                } else {
                    $taskCount = '';
                }
            }
        }
        
        return $taskCount;
    }

    private function getTaskCount($group, $state) {
        $sql = "SELECT COUNT(*) AS number FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = ?";
        $data = $this->mysqliSelectFetchObject($sql, $group->groupID, $state);
        if ($data != null) {
            if ((int) $data->number > 0) {
                $taskCount = '('.$data->number.')';
            } else {
                $taskCount = '';
            }
        }
        return $taskCount;
    }

    public function getUserIDByMail($mail) {
        $sql = "SELECT userID FROM users WHERE userMail = ?";
        $data = $this->mysqliSelectFetchObject($sql, $mail);
        return $data->userID;
    }

    public function getUserIDByUsername($username) {
        $sql = "SELECT * FROM users WHERE userName = ?";
        $data = $this->mysqliSelectFetchObject($sql, $username);
        return $data->userID;
    }

    private function getUserLastMotd($userID) {
        $sql = "SELECT userLastMotd FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userLastMotd;
    }

    public function getUsernameByID($userID) {
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
    public function getUserGroupInvitesCount($userID) {
        $data = $this->mysqliSelectFetchObject("SELECT COUNT(*) AS number FROM tokens WHERE tokenType = 'joingroup' AND tokenUserID = ?;", $userID);
        if ($data) {
            return $data->number;
        }
        return 0;
    }

    /**
     * return true if mail is verified
     * return false if mail is unverified
     */
    public function getUserVerificationState($userID) {
        $sql = "SELECT userMailState FROM users WHERE userID = ?";
        $data = $this->mysqliSelectFetchObject($sql, $userID);
        return $data->userMailState == 'verified';
    }

    public function getTasksByGroupID($groupID) {
        $sql = "SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? ORDER BY taskID DESC";
        return $this->mysqliSelectFetchArray($sql, $groupID);
    }

    public function getTaskType($taskID) {
        $taskData = $this->mysqliSelectFetchObject("SELECT taskType FROM tasks WHERE taskID = ?", $taskID);
        return $taskData->taskType;
    }

    public function getWeek() {
        if (date('W') % 2 == 1) {
            return 'odd';
        } else {
            return 'even';
        }
    }

    public function getWeekday() {
        return date('D');
    }

    public function groupOwnerCheck($groupID, $userID) {
        $groupOwnerID = $this->mysqliSelectFetchObject("SELECT groupOwner FROM groups WHERE groupID = ?", $groupID);
        return $groupOwnerID->groupOwner == $userID;
    }

    public function localstorageCreateJSCode() {
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

    public function localstorageGroupUpdate($destionationUrl) {
        echo "
        <script>
            localStorage.removeItem('Groups');
            ".$this->localstorageCreateJSCode()."
        </script>
        <META HTTP-EQUIV=\"refresh\" content=\"0;URL=$destionationUrl\">
        ";
        exit;
    }
    
    public function locationEnteredUrl($url, $getParam = '') {
        if (strpos($_SESSION['enteredUrl'], '?')) {
            header("Location: " . DOMAIN.$url."&$getParam");
        } else {
            header("Location: " . DOMAIN.$url."?$getParam");
        }
        exit;
    }

    public function locationIndex($getParam = '') {
        header("Location: " . DIR_SYSTEM . "index.php" . $getParam);
        exit;
    }

    public function locationWithDir($url) {
        header("Location: " . DIR_SYSTEM . $url);
        exit;
    }

    private function moveToArchive($id) {
        $this->mysqliQueryPrepared("UPDATE tasks SET taskState = 'archived' WHERE taskID = ?", $id);
    }

    private function parseParent($taskType, $parentID) {
        if ($taskType == 'task') {
            return '<a href="'.DIR_SYSTEM.'php/details.php?action=groupDetails&id='.$parentID.'">'.$this->getGroupNameByID($parentID).'</a>';
        } else {
            return '<a href="'.DIR_SYSTEM.'php/details.php?action=taskDetails&id='.$parentID.'">'.$parentID.'</a>';
        }
    }

    public function printArchive() {
        $html = '<div class="group-box">
                    <div class="group-top-bar">
                        Archive
                    </div>';
        $html .= $this->printTaskTable($this->getArchivedTasks());
        $html .=  '</div>';
        echo $html;
    }

    public function printComments($id, $type) {
        $sql = "SELECT * FROM comments WHERE commentTaskID = ?";
        $data = $this->mysqliSelectFetchArray($sql, $id);
        if ($data != null) {
            $html = '<table>';
            foreach ($data as $i) {
                $html .= '
                    <tr class="comment-background">
                        <td width="15%">'.$this->getUsernameByID($i->commentAutor).':</td>
                        <td style="font-size:14px;">' . $this->addTagsToUrlsInString($i->commentDescription) . '</td>
                        <td width="18%">' . $i->commentDate . '</td>
                        <td style="white-space: nowrap;">
                            <div class="editgroup-button" onclick="openEditCommentForm('.$i->commentID.', \''.$i->commentDescription.'\')">
                                Edit
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </div>
                            <div class="editgroup-button" onclick="deleteComment('.$i->commentID.', '.$i->commentTaskID.')">
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
        $html .= '<form action="action.php?action=createComment&id='.$id.'&type='.$type.'" autocomplete="off" method="post">
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

    private function printGroup($group) {
        $groupName = $group->groupName;
        $groupID = $group->groupID;
        $openTasksCount = $this->getTaskCount($group, 'open');
        $assignedTasksCount = $this->getTaskCount($group, 'assigned');
        $finishedTasksCount = $this->getTaskCount($group, 'finshed');

        $html =  '
        <div class="group-box">
            <div class="group-top-bar">
                <a href="php/details.php?action=groupDetails&id=' . $groupID . '">' . $groupName . '</a>
            </div>
            <div class="group-content">
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>Open '.$openTasksCount.'</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'open' ORDER BY taskPriority DESC, taskID ", $groupID);
        $html .=  '
                </div>
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>In progress '.$assignedTasksCount.'</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'assigned' ORDER BY taskPriority DESC, taskDateAssigned", $groupID);
        $html .=  '
                </div>
                <div class="single-content">
                    <div class="single-top-bar">
                    <p>Done '.$finishedTasksCount.'</p>
                    </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'task' AND taskParentID = ? AND taskState = 'finished' ORDER BY taskDateFinished", $groupID);
        $html .=  '
                </div>
            </div>
        </div>';
        echo $html;
    }

    public function printGroups($groups) {
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

    public function printGroupNames() {
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
                    $deleteOrLeaveGroup = '<td><button type="button" onclick="deleteGroup('.$groupID.')">Delete Group</button>';
                } else {
                    $deleteOrLeaveGroup = '<button type="button" onclick="leaveGroup('.$groupID.')">Leave Group</button>';
                }

                $toggle = !$toggle;
                if ($toggle) {
                    $html .= '<tr style="background-color:'.$backgroundColor.';">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '
                    <td>' . $groupID . '</td>
                    <td><a href="'.DIR_SYSTEM.'php/details.php?action=groupDetails&id=' . $groupID . '">' . $group->groupName . '</a></td>
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

    public function printGroupDetails($group) {
        $groupID = $group->groupID;
        $html = '<div class="group-box">
                    <div class="big-top-bar">
                        <div class="top-bar-item">
                            '.$group->groupName.'
                        </div>
                        <div class="editgroup-button" onclick="openShowUsersPopup()">
                            <i class="fa fa-user fa-2x" aria-hidden="true"></i>
                        </div>';

        $inviteToken = $this->mysqliSelectFetchObject("SELECT tokenToken FROM tokens WHERE tokenGroupID = ? AND tokenType = 'groupinvite'", $groupID);
        if ($group->groupInvites == 'enabled') {
            $groupInvites = '
            <td>
                '. DIR_SYSTEM .'php/action.php?action=joingroup&t='. $inviteToken->tokenToken.'
            </td>
            <td>
                <div class="panel-item-top-bar-button">
                    <a href="' . DIR_SYSTEM . 'php/action.php?action=refreshinvite&id='.$groupID.'"> <i class="fa fa-refresh" aria-hidden="true"></i> </a>
                </div>
            </td>
            <td>
                <form action="action.php?action=groupinvites&invites=disable&id='.$groupID.'" autocomplete="off" method="post" >
                    <input type="submit" name="groupinvites-submit" value="Disable Invites"/>
                </form>
            </td>
            ';
        } else {
            $groupInvites = '
            <td>
                <form action="action.php?action=groupinvites&invites=enable&id='.$groupID.'" autocomplete="off" method="post" >
                    <input type="submit" name="groupinvites-submit" value="Enable Invites"/>
                </form>
            </td>
            ';
        }

        if ($group->groupState == 'active') {
            $changeGroupState = '
            <td>
                <form action="action.php?action=groupstate&state=hide&id='.$groupID.'" autocomplete="off" method="post" >
                    <input type="submit" name="groupstate-submit" value="Hide Group"/>
                </form>
            </td>
            ';
        } else if ($group->groupState == 'hidden') {
            $changeGroupState = '
            <td>
                <form action="action.php?action=groupstate&state=activate&id='.$groupID.'" autocomplete="off" method="post" >
                    <input type="submit" name="groupstate-submit" value="Show Group"/>
                </form>
            </td>
            ';
        }

        if ($_SESSION['userID'] == $this->getGroupOwnerID($groupID)) {
            $html .= '
            <div class="editgroup-button" onclick="openEditGroupForm('.$groupID.', \''.$group->groupName.'\', '.$group->groupPriority.', '.$group->groupArchiveTime.')">
                Edit
                <i class="fa fa-edit" aria-hidden="true"></i>
            </div>
            <div style="float:right;">
                    <table>
                        <tr>
                            '.$groupInvites.'
                            <td>
                                <form action="action.php?action=generateToken&id='.$groupID.'" autocomplete="off" method="post" >
                                    <input type="text" name="name" placeholder="username"/>
                                    <input type="submit" name="groupinvite-submit" value="Invite"/>
                                </form>
                            </td>
                            '.$changeGroupState.'
                            <td><button type="button" onclick="deleteGroup('.$groupID.')">Delete Group</button></td>
                        </tr>
                    </table>
                </div>';
        } else {
            $html .= '
            <div style="float:right;">
                <button type="button" onclick="leaveGroup('.$groupID.')">Leave Group</button>
            </div>';
        }

        $html .= '</div>
                <div class="group-content">
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
                '.$this->getUserListHTML($groupID).'
            </div>
        </div>';
        echo $html;
    }

    private function printAppointmentOrMOTD($message) {
        if ($message->messageType == 'appointment') {
            $jsMethodName = 'printEditAppointmentForm';
        } else if ($message->messageType == 'motd') {
            $jsMethodName = 'printEditMessageForm';
            if (new DateTime($message->messageDate) > new DateTime($this->getUserLastMotd($_SESSION['userID']))) {
                $redOutline = 'style="outline: 1px solid red;"';
            } else {
                $redOutline = '';
            }
        }
        $string = '
        <div class="panel-item-message-title" '.$redOutline.'>
            '.date("d.m.y", strtotime($message->messageDate)).' - '.$this->addTagsToUrlsInString($message->messageTitle).'
            <small style="color:#d1d1e0">'.$this->getUsernameByID($message->messageOwner).' - '.$this->getGroupNameByID($message->messageGroup).'</small>
        </div>';
        if ($message->messageOwner == $_SESSION['userID'] || $this->groupOwnerCheck($message->messageGroup, $_SESSION['userID']) || $_SESSION['userID'] == 1) {
            $string .= '
            <div class="panel-item-delete-button" onclick="'.$jsMethodName.'('.$message->messageID.', \''.$message->messageTitle.'\', \''.$message->messageDate.'\')">
                <i class="fa fa-edit" aria-hidden="true"></i>
            </div>
            <div class="panel-item-delete-button" onclick="deleteMessage(\''.$message->messageID.'\')">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </div>';
        }
        return $string;
    }

    private function printRT($task) {
        $string = '
        <div class="panel-item-message-title">
        '.$task->messageTitle.' 
        </div>
        <div class="panel-item-delete-button" onclick="printEditRTForm('.$task->messageID.', \''.$task->messageTitle.'\', \''.$task->messageWeekday.'\', \''.$task->messageQuantity.'\')">
        <i class="fa fa-edit" aria-hidden="true"></i>
        </div>
        <div class="panel-item-check-button">
            <a href="' . DIR_SYSTEM . 'php/action.php?action=repeatingTaskDone&id='.$task->messageID.'"> <i class="fa fa-check" aria-hidden="true"></i> </a>
        </div>';
        return $string;
    }

    public function printPanelContentDetails($type) {
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
                    $html .= '<tr style="background-color:'.$backgroundColor.';">';
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
                        <div class="panel-item-delete-button" onclick="deleteMessage(\''.$task->messageID.'\')">
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

    private function printPanel($type) {
        if ($type == 'appointment') {
            $title = 'Appointments';
            $createButtonID = 'createAppointmentButton';
            $detailsActionName = 'appointmentDetails';

            $sql = "SELECT m.* 
            FROM messages m
                LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
            WHERE  ga.userID = ? AND m.messageType = 'appointment'
            ORDER BY m.messageDate";
            $data = $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
        } else if ($type == 'motd') {
            $createButtonID = 'createMOTDButton';
            $detailsActionName = 'motdDetails';
            
            $sql = "SELECT m.* 
            FROM messages m
                LEFT JOIN groupaccess ga ON m.messageGroup = ga.groupID
            WHERE  ga.userID = ? AND m.messageType = 'motd'
            ORDER BY m.messageDate DESC, messageID DESC";
            $data = $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);
            if ($data) {
                if (count($data) > 1) {
                    $title = 'MessageBoard ('.count($data).' Messages)';
                } else if(count($data) == 1) {
                    $title = 'MessageBoard (1 Message)';
                }
            } else {
                $title = 'MessageBoard';
            }
        } else if ($type == 'rt') {
            $title = 'Today\'s Tasks';
            $createButtonID = 'createRTButton';
            $detailsActionName = 'repeatingtasksDetails';

            $currentDay = $this->getWeekday();
            $week = $this->getWeek();
            $sql = "SELECT * FROM messages WHERE messageOwner = ? AND messageType = 'repeatingtask' AND (messageWeekday = ? OR messageWeekday = 'everyday') AND (messageQuantity = ? OR messageQuantity = 'everyweek')";
            $data = $this->mysqliSelectFetchArray($sql, $_SESSION['userID'], $currentDay, $week);
        }

        $html = '
        <div class="panel-item">
            <div class="panel-item-content">
                <div class="panel-item-top-bar">
                    <div class="top-bar-left">
                        <p>'.$title.'</p>
                    </div>
                    <div class="top-bar-right">
                        <div class="panel-item-top-bar-button" id="'.$createButtonID.'">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        </div>
                        <div class="panel-item-top-bar-button">
                        <a href="' . DIR_SYSTEM . 'php/details.php?action='.$detailsActionName.'"> <i class="fa fa-list" aria-hidden="true"></i> </a>
                        </div>
                    </div>
                </div>
                <div class="panel-item-area">

        ';

        $toggle = false;
        if ($data) {
            ($this->getNightmodeEnabled($_SESSION['userID'])) ? $backgroundColor = '#333333' : $backgroundColor = '#f2f2f2';

            foreach ($data as $task) {
                if ($type == 'appointment') {
                    if ($this->getDateDifferenceDaysOnly($task->messageDate) > 0) {
                        $sql = "DELETE FROM messages WHERE messageID = ?";
                        $this->mysqliQueryPrepared($sql, $task->messageID);
                    } else {
                        $taskHTML = $this->printAppointmentOrMOTD($task);
                    }

                } else if ($type == 'motd') {
                    $taskHTML = $this->printAppointmentOrMOTD($task);
        
                } else if ($type == 'rt') {
                    if ($task->messageState == '' || $this->getDateDifferenceDaysOnly($task->messageState) > 0) {
                        $taskHTML = $this->printRT($task);
                    }
                }

                if ($taskHTML) {
                    if ($toggle) {
                        $html .= '<div class="panel-item-content-item" style="background-color:'.$backgroundColor.';">'.$taskHTML.'</div>';
                    } else {
                        $html .= '<div class="panel-item-content-item">'.$taskHTML.'</div>';
                    }
                    $toggle = !$toggle;
                }
                unset($taskHTML);
            }
        }

        $html .= '</div>
            </div>
        </div>';

        if ($type == 'motd') {
            $this->mysqliQueryPrepared("UPDATE users SET userLastMotd = CURRENT_TIMESTAMP WHERE userID = ?", $_SESSION['userID']);
        }
        return $html;
    }

    public function printPanels() {
        $panelData = $this->mysqliSelectFetchObject("SELECT * FROM panels WHERE userID = ?", $_SESSION['userID']);
        $panelHTML = '';
        $panelCounter = 0;
        if ($panelData->panelRT == 'true') {
            $panelHTML .= $this->printPanel('rt');
            $panelCounter++;
        }
        if ($panelData->panelMOTD == 'true') {
            $panelHTML .= $this->printPanel('motd');
            $panelCounter++;
        }
        if ($panelData->panelAppointment == 'true') {
            $panelHTML .= $this->printPanel('appointment');
            $panelCounter++;
        }
        if ($panelData->panelQueue == 'true') {
            $panelHTML .= $this->printQueue();
            $panelCounter++;
        }
        if ($panelCounter > 0) {
            $html = '
            <div class="panel">
            '.$panelHTML.'
            </div>
            <div class="bg-modal" id="bg-modal-editmessageform">
            <div class="modal-content" style="height:180px">
            <div class="modal-header">
            Edit Message
            <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-editmessageform"></i>
            </div>
            <div id="editmessageform"></div>
            </div>
            </div>
            ';
            echo $html;
        }
    }

    private function printQueue() {
        
        $html = '
        <div class="panel-item">
            <div class="panel-item-content">
                <div class="panel-item-top-bar">
                    <div class="top-bar-left">
                        <p>Queue</p>
                    </div>
                    <div class="top-bar-right">
                    <form action="'.DIR_SYSTEM.'php/action.php?action=addQueue" autocomplete="off" method="post" >
                    <input type="text" name="item">
                    <input type="checkbox" name="highprio" style="outline: 1px solid red;">
                    <input type="submit" name="add-queue-submit" value="Add" />
                    </form>
                    </div>
                </div>
                <div class="panel-item-area">

        ';

        $sql = "SELECT * FROM messages WHERE messageOwner = ? AND messageType = 'queue' ORDER BY messagePrio, messageID";
        $data = $this->mysqliSelectFetchArray($sql, $_SESSION['userID']);

        $toggle = false;
        $first = true;
        if ($data) {
            ($this->getNightmodeEnabled($_SESSION['userID'])) ? $backgroundColor = '#333333' : $backgroundColor = '#f2f2f2';

            foreach ($data as $task) {
                $string = '
                <div class="panel-item-message-title">
                '.$task->messageTitle.' 
                </div>
                <div class="panel-item-check-button">
                    <a href="' . DIR_SYSTEM . 'php/action.php?action=deleteMessage&id='.$task->messageID.'"> <i class="fa fa-check" aria-hidden="true"></i> </a>
                </div>';
                if ($first) {
                    $html .= '<div class="panel-item-content-item" style="outline: 1px solid red;">'.$string.'</div>';
                    $first = false;
                } else {
                    if ($toggle) {
                        $html .= '<div class="panel-item-content-item" style="background-color:'.$backgroundColor.';">'.$string.'</div>';
                    } else {
                        $html .= '<div class="panel-item-content-item">'.$string.'</div>';
                    }
                }
                $toggle = !$toggle;
                unset($string);
            }
        }

        $html .= '</div>
            </div>
        </div>';
        return $html;
    }

    private function printSubtaskPanel($id) {
        $openTasksCount = $this->getSubtaskCount($id, 'open');
        $assignedTasksCount = $this->getSubtaskCount($id, 'assigned');
        $finishedTasksCount = $this->getSubtaskCount($id, 'finshed');

        $html = '
            <div class="showtaskdetails-panel">
                <div class="group-box">
                    <div class="subtask-top-bar">
                        <div class="top-bar-item">
                            Subtasks
                        </div>
                </div>
                    <div class="group-content">
                        <div class="single-content-subtask">
                            <div class="single-top-bar-subtask">
                            Open '.$openTasksCount.'
                            </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'open' ORDER BY taskPriority DESC", $id);
        $html .= '</div>
                    <div class="single-content-subtask">
                        <div class="single-top-bar-subtask">
                        In progress '.$assignedTasksCount.'
                            </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'assigned' ORDER BY taskDateAssigned", $id);
        $html .= '</div>
                    <div class="single-content-subtask">
                        <div class="single-top-bar-subtask">
                        Done '.$finishedTasksCount.'
                            </div>';
        $html .= $this->printTasksFromSameState("SELECT * FROM tasks WHERE taskType = 'subtask' and taskParentID = ? AND taskState = 'finished' ORDER BY taskDateFinished", $id);
        $html .=    '</div>
                </div>
            </div>
        </div>';
        return $html;
    }

    private function printTask($taskData) {
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
            $html = '<a href="'.DIR_SYSTEM.'php/details.php?action=taskDetails&id=' . $taskData->taskID . '">';
            if ($taskData->taskType == 'task') {
                $html .= '<div class="box">
                    <div class="priority" style="background-color: ' . $taskData->taskPriorityColor . ';"></div>';
            } else if ($taskData->taskType == 'subtask') {
                $html .= '<div class="subtask-box">
                    <div class="subtask-priority" style="background-color: ' . $taskData->taskPriorityColor . ';"></div>';
            }
            $html .= '<div class="content">
                <div class="text">
                    ' . $taskData->taskTitle . '
                </div>
                <div class="emptyspace">&nbsp;</div>';
            
            $html .= '<div class="bottom">
                <div class="bottom-item">
                id_' . $taskData->taskID . '
                </div>';

            if ($taskData->taskAssignedBy) {
                $userID = $taskData->taskAssignedBy;
                $sql = "SELECT * FROM users WHERE userID = ?";
                $userData = $this->mysqliSelectFetchObject($sql, $userID);
                $assignerShort = $userData->userNameShort;
                $html .= '<div class="bottom-item">' . $assignerShort . '</div>';
            }

            if ($taskData->taskState != 'finished') {
                if ($taskData->taskState == 'open' && $dateDiff == 0) {
                    $html .= '<div class="new-item">NEW</div>';
                } else if ($taskData->taskState == 'open' && $dateDiff > 31) {
                    $html .= '<div class="bottom-item" style="background-color:red;color:#fff;">' . $dateDiff . '</div>';
                } else {
                    $html .= '<div class="bottom-item">' . $dateDiff . '</div>';
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

    public function printTaskDetails($task, $id) {
        $html = '<div width="99.5%">';
        $html .= $this->printTaskDetailsNew($task);
        $subtaskcount = $this->mysqliSelectFetchObject("SELECT COUNT(*) as number FROM tasks WHERE taskType = 'subtask' AND taskParentID = ?", $task->taskID);
        if ($subtaskcount->number > 0) {
            $html .= $this->printSubtaskPanel($id);
        }
        $html .= '</div>';
        $this->addTaskDataToLocalstorage($task);
        echo $html;
    }

    public function printTaskDetailsNew($task) {
        if ($task->taskType == 'subtask') {
            $backButton = '<div style="float:left;"><a href="' . DIR_SYSTEM . 'php/details.php?action=taskDetails&id='.$task->taskParentID.'"> 
                <div class="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</div></a></div>';
        } else {
            $backButton = '';
        }
        $buttons = '<div style="clear:both;"><button class="button-list" id="updatetask-button" type="button" >Update</button>
                        <button class="button-list" type="button" onclick="deleteTask(\''.$task->taskID.'\')">Delete</button>
                        <button class="button-list" id="createSubtaskButton" type="button">Create Subtask</button>';
        switch ($task->taskState) {
            case 'open':
                $buttons .= '<form class="button-list" action="action.php?action=assign&id=' . $task->taskID . '" autocomplete="off" method="post" ><input type="submit" name="assign-submit" value="Start Work"/></form>';
                break;

            case 'assigned':
                $buttons .= '<form class="button-list" action="action.php?action=stateOpen&id=' . $task->taskID . '" autocomplete="off" method="post" ><input type="submit" name="stateopen-submit" value="Back to Open"/></form>';
                $buttons .= '<form class="button-list" action="action.php?action=finishTask&id=' . $task->taskID . '" autocomplete="off" method="post" ><input type="submit" name="finish-submit" value="Finish"/></form>';
                break;

            case 'finished':
                $buttons .= '<form class="button-list" action="action.php?action=stateOpen&id=' . $task->taskID . '" autocomplete="off" method="post" ><input type="submit" name="stateopen-submit" value="Back to Open"/></form>';
                $buttons .= '<form class="button-list" action="action.php?action=assign&id=' . $task->taskID . '" autocomplete="off" method="post" ><input type="submit" name="assign-submit" value="Back to in progress"/></form>';
                break;

            default:
                break;
        }
        $buttons .= '</div>';

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
            <div class="showtaskdetails-panel">
                '.$backButton.'
                <div class="taskdetails-buttons">
                    '.$buttons.'
                </div>
                <table style="clear:both;">
                    <tr>
                        <td>ID:</td>
                        <td>'.$task->taskID.'</td>
                    </tr>
                    <tr>
                        <td>Priority:</td>
                        <td>'.$priority.'</td>
                    </tr>
                    <tr>
                        <td>Parent:</td>
                        <td>'.$this->parseParent($task->taskType, $task->taskParentID).'</td>
                    </tr>
                    <tr>
                        <td>Title:</td>
                        <td>'.$task->taskTitle.'</td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td>'.$this->addTagsToUrlsInString($task->taskDescription).'</td>
                    </tr>
                    <tr>
                        <td>State:</td>
                        <td>'.$task->taskState.'</td>
                    </tr>
                    <tr>
                        <td>Date Created:</td>
                        <td>'.$task->taskDateCreated.'</td>
                    </tr>
                    <tr>
                        <td>Date Assigned:</td>
                        <td>'.$task->taskDateAssigned.'</td>
                    </tr>
                    <tr>
                        <td>Assigned By:</td>
                        <td>'.$this->getUsernameByID($task->taskAssignedBy).'</td>
                    </tr>
                    <tr>
                        <td>Date Finished:</td>
                        <td>'.$task->taskDateFinished.'</td>
                    </tr>
                </table>
        ';
        $html .= $this->printComments($task->taskID, $task->taskType);
        return $html;
    }

    private function printTaskTable($tasks) {
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
                    $html .= '<tr style="background-color:'.$backgroundColor.';">';
                } else {
                    $html .= '<tr>';
                }
                $html .= '
                    <td><a href="'.DIR_SYSTEM.'php/details.php?action=taskDetails&id=' . $task->taskID . '">' . $task->taskID . '</a></td>
                    <td>' . $task->taskTitle . '</td>
                    <td>' . $task->taskDescription . '</td>
                    <td>' . $task->taskParentID . '</td>
                    <td>' . $task->taskPriority . '</td>
                    <td>' . $task->taskDateCreated . '</td>
                    <td>' . $task->taskDateAssigned . '</td>
                    <td>' . $this->getUsernameByID($task->taskAssignedBy) . '</td>
                    <td>' . $task->taskDateFinished . '</td>
                    <td style="white-space: nowrap;">
                        <div class="editgroup-button" onclick="deleteTask('.$task->taskID.')">
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

    public function printTasksFromSameState($sql, $id) {
        $data = $this->mysqliSelectFetchArray($sql, $id);

        $html = '';
        if ($data != null) {
            foreach ($data as $i) {
                $html .= $this->printTask($i);
            }
        }
        return $html;
    }

    private function addTaskDataToLocalstorage($task) {
        echo "
        <script>
            var json = '{}';
            var obj = JSON.parse(json);

            obj['taskID'] = $task->taskID;
            obj['taskType'] = '$task->taskType';
            obj['taskParentID'] = $task->taskParentID;
            obj['taskPriority'] = $task->taskPriority;
            obj['taskTitle'] = '$task->taskTitle';
            obj['taskDescription'] = '$task->taskDescription';
            localStorage.setItem('TaskData', JSON.stringify(obj));
        </script>";
    }

    public function printUserDetails($userID) {
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
                    <td><a href="'.DIR_SYSTEM.'php/details.php?action=groupDetails&id='.$group->groupID.'">'.$group->groupName.'</a></td>
                </tr>';
            }
        }

        $groupAccessHTML = '';
        if ($ownedGroups) {
            foreach ($groupAccess as $group) {
                $groupID = $group->groupID;
                $groupAccessHTML .= '
                <tr>
                    <td><a href="'.DIR_SYSTEM.'php/details.php?action=groupDetails&id='.$groupID.'">'.$this->getGroupNameByID($groupID).'</a></td>
                </tr>';
            }
        }

        $html = '
        '.$backButton.'
        <div class="group-box">
            USER
            <table>
                <tr>
                    <td>Username</td>
                    <td>'.$userData->userName.'</td>
                </tr>
                <tr>
                    <td>Mail</td>
                    <td>'.$userData->userMail.'</td>
                </tr>
                <tr>
                    <td>Mail-State</td>
                    <td>'.$userData->userMailState.'</td>
                </tr>
            </table>
        </div>
        
        <div class="group-box">
            OWNED GROUPS
            <table>
                '.$ownedGroupsHTML.'
            </table>
        </div>

        <div class="group-box">
            GROUPACCESS
            <table>
                '.$groupAccessHTML.'
            </table>
        </div>';
        return $html;
    }

    private function pwResetMailHTML($verifyUrl) {
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
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#FFA73B"><a href="'.$verifyUrl.'" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFA73B; display: inline-block;">Reset Password</a></td>
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
                                    <p style="margin: 0;"><a href="'.$verifyUrl.'" target="_blank" style="color: #FFA73B;">'.$verifyUrl.'</a></p>
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

    public function sendPWResetMail($mail, $token) {
        $verifyUrl = DIR_SYSTEM.'php/recover.php?t='.$token;

        $subject = 'Reset your password for taskboard';

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: lukaslanger@bplaced.net\r\n";
        $header .= "Reply-To: $mail\r\n";

        mail($mail, $subject, $this->pwResetMailHTML($verifyUrl), $header);
    }

    public function sendVerifyMail($userID, $mail) {
        $token = $this->generateRandomString();
        $verifyUrl = DIR_SYSTEM.'php/profile.inc.php?action=verifyMail&t='.$token;
        $this->mysqliQueryPrepared("INSERT INTO tokens (tokenType, tokenUserID, tokenToken) VALUES ('verifymail', ?, ?)", $userID, $token);

        $subject = 'Verify your mail for taskboard';

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: lukaslanger@bplaced.net\r\n";
        $header .= "Reply-To: $mail\r\n";

        mail($mail, $subject, $this->verifyMailHTML($verifyUrl), $header);
    }

    private function verifyMailHTML($verifyUrl) {
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
                                                        <td align="center" style="border-radius: 3px;" bgcolor="#FFA73B"><a href="'.$verifyUrl.'" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFA73B; display: inline-block;">Confirm Account</a></td>
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
                                    <p style="margin: 0;"><a href="'.$verifyUrl.'" target="_blank" style="color: #FFA73B;">'.$verifyUrl.'</a></p>
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
    
    public function sqlGetActiveGroups($userID = '') {
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

    public function sqlGetAllGroups($userID = '') {
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

    public function sqlGetHiddenGroups($userID = '') {
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