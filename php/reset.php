<?php
require('../config.php');
unset($_SESSION['userID']);

$username = $_GET['user'];
$password = $_GET['pw'];

$userData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM users WHERE userName = ? AND userPass = ?", $username, $password);
if (!$userData) $taskBoard->locationIndex("?error=invalidurl");

require('../html/head.php');
echo '
    <body>
    <div class="login-view">
        <div class="login-box" style="height:250px;">
            <div class="box-header">Reset password</div>
            <input class="input-login" type="password" id="resetPassword" placeholder="password"/>
            <input class="input-login" type="password" id="resetPasswordRepeat" placeholder="repeat password"/>
            <button class="button" onclick="userHandler.resetPw(\'' . $username . '\', \'' . $password . '\')">Reset Password</button>
        </div>
    </div>';
require('../html/bottom.php');
