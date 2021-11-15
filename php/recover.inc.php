<?php
    require('../config.php');
        
    switch ($_GET['action']) {
        case 'resetpw':
            if (isset($_POST['resetpw-submit'])) {
                $token = $_GET['t'];
                $pw = $_POST['pw'];
                $pwrepeat = $_POST['pwrepeat'];
                if (empty($pw) || empty($pwrepeat)) {
                    header("Location: " . DIR_SYSTEM . "php/recover.php?t=".$token."&error=emptyfield");
                    exit; 
                }
                else if ($pw != $pwrepeat) {
                    header("Location: " . DIR_SYSTEM . "php/recover.php?t=".$token."&error=pwnotequal");
                    exit; 
                }
                else {
                    $tokenData = $taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $token);
                    $taskBoard->mysqliQueryPrepared("UPDATE users SET userPass = ? WHERE userID = ?", password_hash($pw, PASSWORD_DEFAULT), $tokenData->tokenUserID);
                    $taskBoard->mysqliQueryPrepared("DELETE FROM tokens WHERE tokenToken = ?", $token);
                    $taskBoard->locationIndex('?success=pwreset');
                    exit;
                }
            }
            break;

        case 'recoverpw':
            if (isset($_POST['recoverpw-submit'])) {
                $mail = $_POST['mail'];
                $userID = $taskBoard->getUserIDByMail($mail);
                if ($userID) {
                    $token = $taskBoard->generateRandomString();
                    $sql = "INSERT INTO tokens (tokenType, tokenUserID, tokenToken) VALUES ('resetpw', ?, ?)";
                    $taskBoard->mysqliQueryPrepared($sql, $userID, $token);
                    $taskBoard->sendPWResetMail($mail, $token);
                    header("Location: " . DIR_SYSTEM . "php/recover.php?success=pwresetmailsend");
                    exit; 
                } else {
                    header("Location: " . DIR_SYSTEM . "php/recover.php?error=wrongmail");
                    exit; 
                }
            }
            break;
                
        default:
            break;
        }
    $taskBoard->locationIndex();
    exit;