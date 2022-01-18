<?php
    require('head.php');
    $userID = $_SESSION['userID'];
    if ($taskBoard->getNightmodeEnabled($userID)) {
        $nightModeState = 'checked';
    } else {
        $nightModeState = '';
    }
?>
    <body>
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="top_bar_title">
                    <a href="<?php echo DIR_SYSTEM?>"><p>TaskBoard</p></a>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="button" id="createGroupButton"><p>Create Group</p></div>
                <div class="button" id="createTaskButton"><p>Create Task</p></div>
                <a href="<?php echo DIR_SYSTEM ?>php/groups.php"><div class="button"><p>Groups</p></div></a>
                <a href="<?php echo DIR_SYSTEM ?>php/archive.php"><div class="button"><p>Archive</p></div></a>
                <?php
                if ($userID == 1) {
                    echo '<a href="'.DIR_SYSTEM.'php/admin.php"><div class="button"><p>Admin</p></div></a>';
                }
                ?>
                <div class="dropbtn" onclick="showDropdown()">
                    <p><i class="fa fa-caret-down" aria-hidden="true"></i></p>
                </div>
                <div class="dropdown_content" id="dropdown_content">
                    <a href="<?php echo DIR_SYSTEM ?>php/profile.php">
                        <div class="dropdown_button">
                            <p><i class="fa fa-cog"></i></p>
                            <p>Settings</p>
                        </div>
                    </a>
                    <div class="dropdown_button_nightmode">
                        <p><i class="fa fa-moon"></i></p>
                        <p>Nightmode</p>
                        <label class="switch">
                            <input id="nightmode-checkbox" type="checkbox" <?php echo $nightModeState ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <a href="<?php echo DIR_SYSTEM ?>php/logout.inc.php">
                        <div class="dropdown_button">
                            <p><i class="fa fa-sign-out"></i></p>
                            <p>Logout</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>