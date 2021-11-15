<?php
    require('../config.php');
    if ($_SESSION['userID'] != 1) {
        $taskBoard->locationIndex();
    }

    require('../html/top-bar.php');

    $sql = "SELECT * FROM users";
    if ($data = $taskBoard->mysqliSelectFetchArray($sql)) {
        $users = '<div class="panel-item-content-item">
            <table style="border:5px solid #fff;">';
        foreach ($data as $row) {
            ($toggle) ? $color = '#fff' : $color = 'f2f2f2';
            $users .=  '<tr style="background-color:'.$color.';">
                    <td><a href="details.php?action=userDetails&userID='.$row->userID.'">'.$row->userName.'</td>
                    <td>
                        <button type="button" onclick="deleteUser(\''.$row->userName.'\','.$row->userID.')">Delete User</button>
                    </td>
                </tr>';
            $toggle = !$toggle;
        }
        $users .=  '</table>
            </div>';
    }

    echo '
    <div class="group-box">
        USERS
        <form action="'.DIR_SYSTEM.'php/admin.inc.php?action=deleteUser" autocomplete="off" method="post" >
            <input type="text" placeholder="username" name="username"/>
            <input type="submit" name="deleteuser-form" value="Delete User"/>
        </form>
        '.$users.'
    </div>';
    require('../html/bottom.php'); 