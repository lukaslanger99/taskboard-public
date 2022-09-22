<?php
require('../config.php');
unset($_SESSION['userID']);

if (!isset($_POST['signup-submit'])) {
    $taskBoard->locationIndex();
}

$username = $_POST['username'];
$usernameShort = substr($username, 0, 3);
$email = $_POST['email'];
$password = $_POST['password'];
$passwordRepeat = $_POST['passwordRepeat'];

if (isset($_POST['signup-submit'])) {
    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=emptyfields&username" . $username . "&email=" . $email);
        exit;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $username)) {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=invalidinput");
        exit;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=invalidmail&username=" . $username);
        exit;
    } else if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=invalidusername&email=" . $email);
        exit;
    } else if ($taskBoard->checkIfEmailIsTaken($email) == 'taken') {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=emailtaken&username=" . $username);
        exit;
    } else if ($password != $passwordRepeat) {
        header("Location: " . DIR_SYSTEM . "php/signup.php?error=passwordcheck&username=" . $username . "&email=" . $email);
        exit;
    } else {
        $sql = "SELECT userName FROM users WHERE userName = ?";
        $usernameTakenCheck = $taskBoard->mysqliSelectFetchObject($sql, $username);
        if ($usernameTakenCheck->userName) {
            $taskBoard->locationIndex("?error=usertaken&email=" . $email);
        } else {
            //insert user
            $hashedPw = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (userName, userNameShort, userMail, userPass) VALUES (?, ?, ?, ?)";
            $taskBoard->mysqliQueryPrepared($sql, $username, $usernameShort, $email, $hashedPw);
            //insert task group
            $user = $taskBoard->mysqliSelectFetchObject("SELECT * FROM users WHERE userName = ?", $username);
            $sql = "INSERT INTO groups (groupName, groupOwner) VALUES (?, ?);";
            $taskBoard->mysqliQueryPrepared($sql, 'Tasks', $user->userID);
            //groupaccess
            $group = $taskBoard->mysqliSelectFetchObject("SELECT * FROM groups WHERE groupName = ? AND groupOwner = ?", 'Tasks', $user->userID);
            $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES ( ?, ?)", $group->groupID, $group->groupOwner);
            //insert panel entry
            $taskBoard->mysqliQueryPrepared("INSERT INTO panels (userID) VALUES (?)", $user->userID);
            //send verify mail
            $taskBoard->sendVerifyMail($user->userID, $user->userMail);
            $taskBoard->locationIndex("?success=SIGNUP");
        }
    }
} else {
    $taskBoard->locationIndex();
}
