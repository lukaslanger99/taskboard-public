<?php
    require('../config.php');
    $_SESSION['enteredUrl'] = str_replace('createTask=true', '', $_SERVER['REQUEST_URI']);
    if (!$_SESSION['userID']) {
        $taskBoard->locationIndex();
    }
    
    require('../html/top-bar.php'); 
    $taskBoard->printArchive();
    require('../html/bottom.php'); 
?>
    </body>
</html>