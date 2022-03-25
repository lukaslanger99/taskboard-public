<?php
require('../config.php');
if (isset($_POST['login-submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $userData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM users WHERE userName = ?", $username);
    if ($userData) {
        $pwdCheck = password_verify($password, $userData->userPass);
        if ($pwdCheck) {
            session_start();
            $_SESSION['userID'] = $userData->userID;
            $taskBoard->mysqliQueryPrepared("UPDATE users SET userLastLogin = CURRENT_TIMESTAMP WHERE userID = ?", $userData->userID);
            if ($_SESSION['enteredUrl']) {
                header("Location: " . DOMAIN . $_SESSION['enteredUrl']);
                exit;
            } else {
                $taskBoard->locationWithDir("index.php?success=login");
            }
        }
    }
    $taskBoard->locationIndex("?error=login&username=".$username);
}
$taskBoard->locationIndex();