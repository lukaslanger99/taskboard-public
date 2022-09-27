<?php
require('config.php');
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
                <div class="login__inner">
                    <h2>Login</h2>
                    <div className="login__group">
                        <label htmlFor="username">Username</label>
                        <input type="text" id="loginUsername" placeholder="username" value="' . $_GET['username'] . '"/><br>
                    </div>
                    <div className="login__group">
                        <label htmlFor="password">Password</label>
                        <input type="password" id="loginPassword" placeholder="password"/><br>
                    </div>
                    <button class="button" onclick="userHandler.login()">Login</button>
                </div>
                <div class="login__signup">
                    <a href="' . DIR_SYSTEM . 'php/recover.php">Recover password</a>
                    <div class="login__signup__bottom">
                    <div class="light-text">No account?</div>
                        <a href="' . DIR_SYSTEM . 'php/signup.php">Create an account</a>
                    </div>
                </div>';
    if ($_GET['success'] == 'SIGNUP') {
        echo '<script>printSuccessToast(\'SIGNUP\')</script>';
    };
}
require('html/bottom.php');
