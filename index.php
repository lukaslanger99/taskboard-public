<?php
    require('config.php');
    unset($_SESSION['delete']);
    unset($_SESSION['deleteGroup']);
    if ($_SESSION['userID']) {
        $_SESSION['enteredUrl'] = $_SERVER['REQUEST_URI'];
        require('html/top-bar.php'); 
        $taskBoard->printPanels();
        if (DIR_SYSTEM == "http://lukaslanger.bplaced.net/taskboard/") {
            $taskBoard->printGroups($taskBoard->sqlGetGroups());
        }
    } else {
        require('html/head.php');
        echo '
            <body>
            <div class="login-view">
                <div class="login-box">
                    <div class="box-header">Login</div>
                        <form action="'.DIR_SYSTEM.'php/login.inc.php" autocomplete="off" method="post" >
                            <input class="input-login" type="text" name="username" placeholder="username" value="'.$_GET['username'].'"/><br>
                            <input class="input-login" type="password" name="password" placeholder="password"/><br>
                            <input class="submit-login" type="submit" name="login-submit" value="Login"/>
                        </form>
                </div>
                <div class="signup-area">
                    <a href="'.DIR_SYSTEM.'php/recover.php">Recover password</a>
                    <table style="margin:auto;">
                        <tr>
                            <td><div class="light-text">No account?</div></td>
                            <td><a href="'.DIR_SYSTEM.'php/signup.php">Create an account</a></td>
                        </tr>
                    </table>
                </div>
            </div>
            </body>
        </html>';
    }
    require('html/bottom.php'); 