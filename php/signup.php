<?php
require('../config.php');
require('../html/head.php');
echo '
    <body>
    <div class="login-view">
        <div class="login-box" style="height:350px;">
            <div class="box-header">Signup</div>
                <input class="input-login" type="text" id="signupUsername" placeholder="username"/>
                <input class="input-login" type="text" id="signupEmail" placeholder="E-mail"/>
                <input class="input-login" type="password" id="signupPassword" placeholder="password"/>
                <input class="input-login" type="password" id="signupPasswordRepeat" placeholder="repeat password"/>
                <button class="button" onclick="userHandler.signup()">Signup</button>
            </div>
        </div>
    </div>';
require('../html/bottom.php');
