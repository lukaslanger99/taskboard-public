<?php
    require('../config.php');
    require('../html/head.php');
    $token = $_GET['t'];
    if ($taskBoard->mysqliSelectFetchObject("SELECT * FROM tokens WHERE tokenToken = ?", $token)) {
        echo '
            <body>
            <div class="login-view">
                <div class="login-box">
                    <div class="box-header">Reset Password</div>
                        <form action="'.DIR_SYSTEM.'php/recover.inc.php?action=resetpw&t='.$token.'" autocomplete="off" method="post" >
                            <input class="input-login" type="password" name="pw" placeholder="password"/><br>
                            <input class="input-login" type="password" name="pwrepeat" placeholder="password repeat"/><br>
                            <input class="submit-login" type="submit" name="resetpw-submit" value="Submit"/>
                        </form>
                </div>
            </div>
            </body>
        </html>';
    } else {
        echo '
            <body>
            <div class="login-view">
                <div class="login-box">
                    <div class="box-header">Recover Password</div>
                        <form action="'.DIR_SYSTEM.'php/recover.inc.php?action=recoverpw" autocomplete="off" method="post" >
                            <input class="input-login" type="text" name="mail" placeholder="mail"/><br>
                            <input class="submit-login" type="submit" name="recoverpw-submit" value="Submit"/>
                        </form>
                </div>
            </div>
            </body>
        </html>';
    }
    require('../html/bottom.php'); 