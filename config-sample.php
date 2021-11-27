<?php
    error_reporting(E_ALL ^ E_NOTICE);
    require('php/taskboard.php');
    
    if (__DIR__ == "/users/lukaslanger/www/taskboard") {
        define("DIR_SYSTEM", "http://lukaslanger.bplaced.net/taskboard/");
        define("DOMAIN", "http://lukaslanger.bplaced.net");
    } else {
        define("DIR_SYSTEM", "http://localhost/lukaslanger/taskboard/taskboard/");
    }

    define("SERVER_NAME", "###");
    define("USER", "###");
    define("PASS", "####");
    define("DB", "###");

    session_start();
    $taskBoard = new TaskBoard();