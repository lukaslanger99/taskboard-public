<?php
    require('../config.php');
    if (isset($_POST['login-submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; 

        $con = $taskBoard->mysqliConnect();
        $sql = "SELECT * FROM users WHERE userName=?;";
        $stmt = mysqli_stmt_init($con);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $taskBoard->locationIndex("?error=sqlerror");
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_object($result)) {
                $pwdCheck = password_verify($password, $row->userPass);
                if ($pwdCheck) {
                    session_start();
                    $_SESSION['userID'] = $row->userID;
                    if ($_SESSION['enteredUrl']) {
                        $destinationUrl = DOMAIN . $_SESSION['enteredUrl'];
                    } else {
                        $destinationUrl = DIR_SYSTEM . "index.php?success=login";
                    }
                    $taskBoard->mysqliQueryPrepared("UPDATE users SET userLastLogin = CURRENT_TIMESTAMP WHERE userID = ?", $row->userID);
                    $taskBoard->localstorageGroupUpdate($destinationUrl);
                } else {
                    $taskBoard->locationIndex("?error=login");
                }
            }
            else {
                $taskBoard->locationIndex("?error=login");
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } else {
        $taskBoard->locationIndex();
    }