<?php
    require('../config.php');
    unset($_SESSION['userID']);

    if (!isset($_POST['reset-submit'])) {
        $taskBoard->locationIndex();
    }

    $username = $_GET['user'];
    $oldPw = $_GET['pw'];
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];
    
    if (isset($_POST['reset-submit'])) {
        if (empty($password) || empty($passwordRepeat)) {
            header("Location: " . DIR_SYSTEM . "php/reset.php?error=emptyfields");
            exit; 
        }
        else if ($password != $passwordRepeat) {
            header("Location: " . DIR_SYSTEM . "php/reset.php?error=passwordcheck");
            exit; 
        }
        else {
            $con = $taskBoard->mysqliConnect();
            $sql = "SELECT userName FROM users WHERE userName = ? AND userPass = ?";
            $stmt = mysqli_stmt_init($con);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                $taskBoard->locationIndex("?error=sqlerror");
            }
            else {
                mysqli_stmt_bind_param($stmt, "ss", $username, $oldPw);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $resultCheck = mysqli_stmt_num_rows(($stmt));
                if ($resultCheck != 1) {
                    $taskBoard->locationIndex();
                }
                else {
                    $sql = "UPDATE users SET userPass = ? WHERE userName = ?";
                    $stmt = mysqli_stmt_init($con);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        $taskBoard->locationIndex("?error=sqlerror");
                    }
                    else {
                        $hashedPw = password_hash($password, PASSWORD_DEFAULT);

                        mysqli_stmt_bind_param($stmt, "ss", $hashedPw, $username);
                        mysqli_stmt_execute($stmt);

                        $taskBoard->locationIndex("?pwreset=success");
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } 
    else {
        $taskBoard->locationIndex();
    }