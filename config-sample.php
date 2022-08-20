<?php
error_reporting(E_ALL ^ E_NOTICE);
require('php/taskboard.php');

define("DIR_SYSTEM", "http://lukaslanger.bplaced.net/taskboard/");
define("DOMAIN", "http://lukaslanger.bplaced.net");

define("SERVER_NAME", "###");
define("USER", "###");
define("PASS", "####");
define("DB", "###");

define("NUMBER_OF_TOTAL_PANELS", 6);

session_start();
$taskBoard = new TaskBoard();
