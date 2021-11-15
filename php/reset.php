<?php
    require('../config.php');
    unset($_SESSION['userID']);
    
    $username = $_GET['user'];
    $password = $_GET['pw'];

    $con = $taskBoard->mysqliConnect();
    $sql = "SELECT * FROM users WHERE userName=? AND userPass=?;";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        $taskBoard->locationIndex("?error=sqlerror");
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!$row = mysqli_fetch_object($result)) {
            $taskBoard->locationIndex("?error=invalidurl");
        }
    }
    
    require('../html/head.php');
    echo '
    <body>
    <div class="login-view">
        <div class="login-box" style="height:250px;">
            <div class="box-header">Reset password</div>
                <form action="'.DIR_SYSTEM.'php/reset.inc.php?user='.$username.'&pw='.$password.'" autocomplete="off" method="post" >
                    <input class="input-login" type="password" name="password" placeholder="password"/>
                    <input class="input-login" type="password" name="passwordRepeat" placeholder="repeat password"/>
                    <input class="submit-login" type="submit" name="reset-submit" value="Reset Password"/>
                </form>
            </div>
            </div>
        </body>
    </html>';
    require('../html/bottom.php'); 