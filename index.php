<?php
require('config.php');
unset($_SESSION['delete']);
unset($_SESSION['deleteGroup']);
if ($_SESSION['userID']) {
    $_SESSION['enteredUrl'] = $_SERVER['REQUEST_URI'];
    require('html/top-bar.php');
    $taskBoard->printPanels();
    echo '<div class="group__boxes" id="group__boxes"></div>';
    echo '<script>indexHandler.printIndexGroups()</script>';
} else {
    require('html/head.php');
    echo '
            <body>
                <form action="' . DIR_SYSTEM . 'php/login.inc.php" autocomplete="off" method="post" >
                    <div class="login__inner">
                        <h2>Login</h2>
                        <div className="login__group">
                            <label htmlFor="username">Username</label>
                            <input type="text" name="username" placeholder="username" value="' . $_GET['username'] . '"/><br>
                        </div>
                        <div className="login__group">
                            <label htmlFor="password">Password</label>
                            <input type="password" name="password" placeholder="password"/><br>
                        </div>
                        <input type="submit" name="login-submit" value="Login"/>
                    </div>
                </form>
                <div class="login__signup">
                    <a href="' . DIR_SYSTEM . 'php/recover.php">Recover password</a>
                    <div class="login__signup__bottom">
                    <div class="light-text">No account?</div>
                        <a href="' . DIR_SYSTEM . 'php/signup.php">Create an account</a>
                    </div>
                </div>
            </body>
        </html>';
}
require('html/bottom.php');
