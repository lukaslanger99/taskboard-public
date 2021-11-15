<?php
    require('head.php');

    $user = $taskBoard->getUserData($_SESSION['userID']);
    if ($taskBoard->getNightmodeEnabled($user->userID)) {
        $nightModeState = 'checked';
    } else {
        $nightModeState = '';
    }
?>
    <body>
        <div class="top-bar">
            <div class="top-bar-item">
                <a href="<?php echo DIR_SYSTEM?>"> TaskBoard </a>
            </div>
            <?php
                if ($_SESSION['enteredUrl'] != '/taskboard/php/profile.php') {
                    echo '
                    <div class="dropdown">
                        <div class="dropbtn">
                            <i class="fa fa-user fa-2x" aria-hidden="true"></i>
                        </div>
                        <div class="dropdown-content">
                            <div class="dropdown-item">
                                <a href="'.DIR_SYSTEM.'php/profile.php">Settings</a>
                            </div>
                            <div>
                                Nightmode 
                                <label class="switch">
                                    <input id="nightmode-checkbox" type="checkbox" '.$nightModeState.'>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="dropdown-item">
                                <a href="'.DIR_SYSTEM.'php/logout.inc.php">Logout</a>
                            </div>
                        </div>
                    </div>';
                }
                if ($_SESSION['userID'] == 1) {
                    echo '
                    <a href="'.DIR_SYSTEM.'php/admin.php">
                        <div class="button">
                            Admin
                        </div>
                    </a>';
                }
            ?>
            <a href="<?php echo DIR_SYSTEM . 'php/archive.php'?>">
            <div class="button">
                Archive
            </div>
            </a>
            <a href="<?php echo DIR_SYSTEM . 'php/groups.php'?>">
            <div class="button">
                Groups
            </div>
            </a>
            <div class="button" id="createTaskButton">
                Create Task
            </div>
            <div class="button" id="createGroupButton">
                Create Group
            </div>
            <!-- </div> -->
        </div>