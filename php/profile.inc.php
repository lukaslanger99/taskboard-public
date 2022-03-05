<?php
    require('../config.php');
        
    $userID = $_SESSION['userID'];
    switch ($_GET['action']) {
        case 'updateshortname':
            if (isset($_POST['updateshortname-submit'])) {
                $newShortname = $_POST['usernameshort'];
                if (empty($newShortname)) {
                    header("Location: " . DIR_SYSTEM . "php/profile.php?error=emptyfield");
                    exit; 
                }
                else if (!preg_match("/^[a-zA-Z0-9]*$/", $newShortname)) {
                    header("Location: " . DIR_SYSTEM . "php/signup.php?error=invalidcharacters");
                    exit; 
                }
                else {
                    $con = $taskBoard->mysqliConnect();
                    $sql = "UPDATE users SET userNameShort = ? WHERE userID = $userID;";
                    $stmt = mysqli_stmt_init($con);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        $taskBoard->locationIndex("?error=sqlerror");
                    }
                    else {
                        mysqli_stmt_bind_param($stmt, "s", $newShortname);
                        mysqli_stmt_execute($stmt);
                        header("Location: " . DIR_SYSTEM . "php/profile.php?success=updateshortname");
                        exit;
                    }
                }
                mysqli_stmt_close($stmt);
                mysqli_close($con);
            }
            break;

        case 'updatepassword':
            if (isset($_POST['updatepassword-submit'])) {
                $passwordOld = $_POST['passwordold'];
                $passwordNew = $_POST['passwordnew'];
                $passwordNewRepeat = $_POST['passwordnewrepeat'];
                if (empty($passwordOld) || empty($passwordNew) || empty($passwordNewRepeat)) {
                    header("Location: " . DIR_SYSTEM . "php/profile.php?error=emptyfields");
                    exit; 
                }
                else if ($passwordNew != $passwordNewRepeat) {
                    header("Location: " . DIR_SYSTEM . "php/signup.php?error=passwordcheck");
                    exit; 
                }
                else {
                    $sql = "SELECT * FROM users WHERE userID = ?";
                    $data = $taskBoard->mysqliSelectFetchObject($sql, $userID);
                    if (!password_verify($passwordOld, $data->userPass)) {
                        header("Location: " . DIR_SYSTEM . "php/signup.php?error=wrongpw");
                        exit; 
                    }
                    else {
                        $hashedPw = password_hash($passwordNew, PASSWORD_DEFAULT);
                        $con = $taskBoard->mysqliConnect();
                        $sql = "UPDATE users SET userPass = ? WHERE userID = $userID";
                        $stmt = mysqli_stmt_init($con);
                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                            $taskBoard->locationIndex("?error=sqlerror");
                        }
                        else {
                            mysqli_stmt_bind_param($stmt, "s", $hashedPw);
                            mysqli_stmt_execute($stmt);
                        
                            header("Location: " . DIR_SYSTEM . "php/profile.php?success=updatepw");
                            exit;
                        }
                    }
                }
            }
            break;

        case 'acceptinvite':
            if (isset($_POST['acceptinvite-submit'])) {
                $tokenData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $_GET['t']);
                if ($tokenData->tokenUserID == $_SESSION['userID']) {
                    $owner = $taskBoard->getUserData($taskBoard->getGroupOwnerID($token->tokenGroupID));
                    if ($owner->userType == 'normal' && $taskBoard->getNumberOfGroupUsers($token->tokenGroupID) > 5) {
                        header("Location: " . DIR_SYSTEM . "php/profile.php?error=maxgroupusers");
                    }
                    $taskBoard->mysqliQueryPrepared("INSERT INTO groupaccess (groupID, userID) VALUES (?, ?)", $tokenData->tokenGroupID, $tokenData->tokenUserID);
                    $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenToken = ?", $tokenData->tokenToken);
                    $taskBoard->locationWithDir("php/details.php?action=groupDetails&id=".$tokenData->tokenGroupID."&success=joinedgroup");
                }
            }
            break;

        case 'rejectinvite':
            if (isset($_POST['rejectinvite-submit'])) {
                $tokenData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $_GET['t']);
                if ($tokenData->tokenUserID == $_SESSION['userID']) {
                    $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenToken = ?", $tokenData->tokenToken);
                }
            }
            break;

        case 'resendverifymail':
            $taskBoard->sendVerifyMail($userID, $taskBoard->getMailByUserID($userID));
            header("Location: " . DIR_SYSTEM . "php/profile.php?success=mailsend");
            exit;

        case 'toggleNightmode':
            $taskBoard->mysqliQueryPrepared("UPDATE users SET userNightmode = ? WHERE userID = ?", $_GET['n'], $userID);
            if ($_SESSION['enteredUrl']) {
                $destinationUrl = DOMAIN.$_SESSION['enteredUrl'];
            } else {
                $destinationUrl = DIR_SYSTEM."php/profile.php";
            }
            $taskBoard->locationEnteredUrl($_SESSION['enteredUrl'], "nightmodechange=true");
            header("Location: ".$destinationUrl);
            exit;

        case 'updateMail':
            $mail = $_POST['mail'];
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                header("Location: " . DIR_SYSTEM . "php/profile.php?error=invalidinput");
                exit; 
            } else if ($taskBoard->mysqliSelectFetchObject("SELECT userMail FROM users WHERE userMail = ?", $mail)) {
                header("Location: " . DIR_SYSTEM . "php/profile.php?error=mailtaken");
                exit;
            } 
            else {
                $taskBoard->mysqliQueryPrepared("UPDATE users SET userMail = ?, userMailState = 'unverified' WHERE userID = ?", $mail, $userID);
                $taskBoard->sendVerifyMail($userID, $taskBoard->getMailByUserID($userID));
                header("Location: " . DIR_SYSTEM . "php/profile.php?success=updatemail");
                exit;
            }
            break;

        case 'verifyMail':
            $token = $_GET['t'];
            $tokenData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $token);
            if ($tokenData) {
                $userID = $tokenData->tokenUserID;
                $taskBoard->mysqliQueryPrepared("UPDATE users SET userMailState = 'verified' WHERE userID = ?", $userID);
                $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenToken = ?", $token);
                $_SESSION['userID'] = $userID;
                header("Location: " . DIR_SYSTEM . "php/profile.php?success=verify");
                exit;
            } else {
                header("Location: " . DIR_SYSTEM . "php/profile.php?error=invalidtoken");
                exit;
            }
            break;
            
        default:
            $taskBoard->locationIndex();
            break;
    }
    header("Location: " . DIR_SYSTEM . "php/profile.php");
    exit;