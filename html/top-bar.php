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
            <div class="top-bar-item">
                <a href="<?php echo DIR_SYSTEM?>"> TaskBoard </a>
            </div>
            <div class="button" id="createGroupButton"><p>Create Group</p></div>
            <div class="button" id="createTaskButton"><p>Create Task</p></div>
            <a href="<?php echo DIR_SYSTEM ?>php/groups.php"><div class="button"><p>Groups</p></div></a>
            <a href="<?php echo DIR_SYSTEM ?>php/archive.php"><div class="button"><p>Archive</p></div></a>
            <?php
                if ($userID == 1) {
                    echo '<a href="<?php echo DIR_SYSTEM ?>php/admin.php"><div class="button"><p>Admin</p></div></a>';
                }
            ?>
            <div class="dropbtn" onclick="showDropDownPopUp()">
               <p><i class="fa fa-arrow-down" aria-hidden="true"></i></p>
            </div>
        </div>