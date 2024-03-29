<?php
require('../config.php');
$_SESSION['enteredUrl'] = str_replace('createTask=true', '', $_SERVER['REQUEST_URI']);
if (!$_SESSION['userID']) {
    $taskBoard->locationIndex();
}

$user = $taskBoard->getUserData($_SESSION['userID']);

if ($user->userMailStatus == 'verified') {
    $verifyStatus = '<p style="color:green;">Verified</p>';
} else {
    $verifyStatus = '<a href="' . DIR_SYSTEM . 'php/profile.inc.php?action=resendverifymail" style="color:red;text-decoration:underline;"> Verify now!</a>';
}

require('../html/top-bar.php');
echo '
        <div class="group-box">
            <table>
                <tr>
                    <td>Username:</td>
                    <td>' . $user->userName . '</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>' . $user->userMail . '</td>
                    <td>' . $verifyStatus . '</td>
                    <td>
                    <div class="panel-item-delete-button" onclick="printEditMailForm(\'' . $user->userMail . '\')">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </div>
                    </td>
                </tr>
            </table>

            <form action="' . DIR_SYSTEM . 'php/profile.inc.php?action=updateshortname" autocomplete="off" method="post">
                <input type="text" maxlength="3" name="usernameshort" value="' . $user->userNameShort . '">
                <input type="submit" name="updateshortname-submit" value="Update shortname"/>
            </form>

            <form action="' . DIR_SYSTEM . 'php/profile.inc.php?action=updatepassword" autocomplete="off" method="post">
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
                    <td>Invite From: ' . $ownerUsername . ' For: ' . $groupName . '</td>
                    <td>
                        <form action="' . DIR_SYSTEM . 'php/profile.inc.php?action=acceptinvite&t=' . $invite->tokenToken . '" autocomplete="off" method="post">
                            <input type="submit" name="acceptinvite-submit" value="Accept"/>
                        </form>
                    </td>
                    <td>
                        <form action="' . DIR_SYSTEM . 'php/profile.inc.php?action=rejectinvite&t=' . $invite->tokenToken . '" autocomplete="off" method="post">
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
    $nightModeStatus = 'checked';
} else {
    $nightModeStatus = '';
}

$panelData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM panels WHERE userID = ?", $user->userID);
$activePanels = [];
if ($panelData->panelMOTD == 'true') $motdStatus = 'checked';
if ($panelData->panelMOTDUnfolded == 'true') $motdUnfolded = 'checked';
$activePanels[$panelData->panelMOTDOrder] = [
    'type' => 'motd',
    'title' => 'Messages of the Day Panel',
    'activeID' => 'motdActiveCheckbox',
    'active' => $motdStatus,
    'unfoldedID' => 'motdUnfoldedCheckbox',
    'unfolded' => $motdUnfolded,
];
if ($panelData->panelAppointment == 'true') $appointmentStatus = 'checked';
if ($panelData->panelAppointmentUnfolded == 'true') $appointmentUnfolded = 'checked';
$activePanels[$panelData->panelAppointmentOrder] = [
    'type' => 'appointment',
    'title' => 'Appointment Panel',
    'activeID' => 'appointmentActiveCheckbox',
    'active' => $appointmentStatus,
    'unfoldedID' => 'appointmentUnfoldedCheckbox',
    'unfolded' => $appointmentUnfolded,
];
if ($panelData->panelQueue == 'true') $queueStatus = 'checked';
if ($panelData->panelQueueUnfolded == 'true') $queueUnfolded = 'checked';
$activePanels[$panelData->panelQueueOrder] = [
    'type' => 'queue',
    'title' => 'Queue Panel',
    'activeID' => 'queueActiveCheckbox',
    'active' => $queueStatus,
    'unfoldedID' => 'queueUnfoldedCheckbox',
    'unfolded' => $queueUnfolded,
];
if ($panelData->panelWeather == 'true') $weatherStatus = 'checked';
if ($panelData->panelWeatherUnfolded == 'true') $weatherUnfolded = 'checked';
$activePanels[$panelData->panelWeatherOrder] = [
    'type' => 'weather',
    'title' => 'Weather Panel',
    'activeID' => 'weatherActiveCheckbox',
    'active' => $weatherStatus,
    'unfoldedID' => 'weatherUnfoldedCheckbox',
    'unfolded' => $weatherUnfolded,
];
if ($panelData->panelTimetable == 'true') $timetableStatus = 'checked';
if ($panelData->panelTimetableUnfolded == 'true') $timetableUnfolded = 'checked';
$activePanels[$panelData->panelTimetableOrder] = [
    'type' => 'timetable',
    'title' => 'Timetable Panel',
    'activeID' => 'timetableActiveCheckbox',
    'active' => $timetableStatus,
    'unfoldedID' => 'timetableUnfoldedCheckbox',
    'unfolded' => $timetableUnfolded,
];
if ($panelData->panelMorningroutine == 'true') $morningroutineStatus = 'checked';
if ($panelData->panelMorningroutineUnfolded == 'true') $morningroutineUnfolded = 'checked';
$activePanels[$panelData->panelMorningroutineOrder] = [
    'type' => 'morningroutine',
    'title' => 'Morningroutine',
    'activeID' => 'morningroutineActiveCheckbox',
    'active' => $morningroutineStatus,
    'unfoldedID' => 'morningroutineUnfoldedCheckbox',
    'unfolded' => $morningroutineUnfolded,
];
$panelsHTML = '';
for ($i = 0; $i < count($activePanels); $i++) {
    $panelData = $activePanels[$i + 1];
    $panelsHTML .= $taskBoard->printPanelSettings(
        $panelData['type'],
        $panelData['title'],
        $panelData['activeID'],
        $panelData['active'],
        $panelData['unfoldedID'],
        $panelData['unfolded']
    );
}
echo '<div class="group-box">
        PANELS
        <div class="draggable__container" id="draggablePanelsContainer">
            ' . $panelsHTML . '
        </div>
        <script>addDraggableHelper(\'updatePanelOrder\')</script>
    </div>';

require('../html/bottom.php');
