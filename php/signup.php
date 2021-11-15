<?php
    require('../config.php');
    require('../html/head.php');
    echo '
    <body>
    <div class="login-view">
        <div class="login-box" style="height:350px;">
            <div class="box-header">Signup</div>
                <form action="'.DIR_SYSTEM.'php/signup.inc.php" autocomplete="off" method="post" >
                    <input class="input-login" type="text" name="username" placeholder="username"/>
                    <input class="input-login" type="text" name="email" placeholder="E-mail"/>
                    <input class="input-login" type="password" name="password" placeholder="password"/>
                    <input class="input-login" type="password" name="passwordRepeat" placeholder="repeat password"/>
                    <input class="submit-login" type="submit" name="signup-submit" value="Signup"/>
                </form>
            </div>
            </div>
        </body>
    </html>';
    require('../html/bottom.php'); 