<?php
    require('../config.php');
    $_SESSION['enteredUrl'] = str_replace('createTask=true', '', $_SERVER['REQUEST_URI']);
    if (!$_SESSION['userID']) {
        $taskBoard->locationIndex();
    }

    $user = $taskBoard->getUserData($_SESSION['userID']);

    if ($user->userMailState == 'verified') {
        $verifyState = '<p style="color:green;">Verified</p>';
    } else {
        $verifyState = '<a href="'.DIR_SYSTEM.'php/profile.inc.php?action=resendverifymail" style="color:red;text-decoration:underline;"> Verify now!</a>';
    }

    require('../html/top-bar.php'); 
    echo '
        <div class="group-box">
            <table>
                <tr>
                    <td>Username:</td>
                    <td>'.$user->userName.'</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>'.$user->userMail.'</td>
                    <td>'.$verifyState.'</td>
                    <td>
                    <div class="panel-item-delete-button" onclick="printEditMailForm(\''.$user->userMail.'\')">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </div>
                    </td>
                </tr>
            </table>

            <form action="'.DIR_SYSTEM.'php/profile.inc.php?action=updateshortname" autocomplete="off" method="post">
                <input type="text" maxlength="3" name="usernameshort" value="'.$user->userNameShort.'">
                <input type="submit" name="updateshortname-submit" value="Update shortname"/>
            </form>

            <form action="'.DIR_SYSTEM.'php/profile.inc.php?action=updatepassword" autocomplete="off" method="post">
                <input type="password" name="passwordold" placeholder="old password"/>
                <input type="password" name="passwordnew" placeholder="new password"/>
                <input type="password" name="passwordnewrepeat" placeholder="repeat new password"/>
                <input type="submit" name="updatepassword-submit" value="Update password"/>
            </form>
        </div>
    ';

    $invites = $taskBoard->mysqliSelectFetchArray("SELECT * FROM tokens WHERE tokenUserID = ? AND tokenType = 'joingroup'", $user->userID);

    if ($invites) {
        $html = '
        <div class="group-box">
            <table>';
        foreach ($invites as $invite) {
            if ($taskBoard->getDateDifferenceDaysOnly($invite->tokenDate) > 7) {
                $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenToken = ?", $invite->tokenToken);
            } else {
                $ownerUsername = $taskBoard->getUsernameByID($taskBoard->getGroupOwnerID($invite->tokenGroupID));
                $groupName = $taskBoard->getGroupNameByID($invite->tokenGroupID);
                $html .= '
                <tr>
                    <td>Invite From: '.$ownerUsername . ' For: '.$groupName.'</td>
                    <td>
                        <form action="'.DIR_SYSTEM.'php/profile.inc.php?action=acceptinvite&t='.$invite->tokenToken.'" autocomplete="off" method="post">
                            <input type="submit" name="acceptinvite-submit" value="Accept"/>
                        </form>
                    </td>
                    <td>
                        <form action="'.DIR_SYSTEM.'php/profile.inc.php?action=rejectinvite&t='.$invite->tokenToken.'" autocomplete="off" method="post">
                            <input type="submit" name="rejectinvite-submit" value="Reject"/>
                        </form>
                    </td>
                </tr>';
            }
        }
        $html .= '</table>
            </div>';
        echo $html;
    }
    
    if ($taskBoard->getNightmodeEnabled($user->userID)) {
        $nightModeState = 'checked';
    } else {
        $nightModeState = '';
    }

    echo '
    <div class="group-box">
        <div>
        Nightmode 
        <label class="switch">
          <input id="nightmode-checkbox" type="checkbox" '.$nightModeState.'>
          <span class="slider round"></span>
        </label>
        </div>
    </div>';

    $panelData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM panels WHERE userID = ?", $user->userID);

    if ($panelData->panelRT == 'true') {
        $rtState = 'checked';
    } else {
        $rtState = '';
    }

    if ($panelData->panelMOTD == 'true') {
        $motdState = 'checked';
    } else {
        $motdState = '';
    }

    if ($panelData->panelAppointment == 'true') {
        $appointmentState = 'checked';
    } else {
        $appointmentState = '';
    }

    if ($panelData->panelQueue == 'true') {
        $queueState = 'checked';
    } else {
        $queueState = '';
    }

    echo '
    <div class="group-box">
        PANELS

        <table>
            <tr>
                <td>Repeating Tasks Panel</td>
                <td>
                    <label class="switch">
                    <input id="rtpanel-checkbox" type="checkbox" '.$rtState.'>
                      <span class="slider round"></span>
                    </label>
                </td>
            </tr>
            <tr>
                <td>Messages of the Day Panel</td>
                <td>
                    <label class="switch">
                    <input id="motdpanel-checkbox" type="checkbox" '.$motdState.'>
                      <span class="slider round"></span>
                    </label>
                </td>
            </tr>
            <tr>
                <td>Appointment Panel</td>
                <td>
                    <label class="switch">
                    <input id="appointmentpanel-checkbox" type="checkbox" '.$appointmentState.'>
                      <span class="slider round"></span>
                    </label>
                </td>
            </tr>
            <tr>
                <td>Queue Panel</td>
                <td>
                    <label class="switch">
                    <input id="queuepanel-checkbox" type="checkbox" '.$queueState.'>
                      <span class="slider round"></span>
                    </label>
                </td>
            </tr>
        </table>
    </div>';

    require('../html/bottom.php'); 