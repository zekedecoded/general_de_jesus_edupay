<?php
session_start();

$_SESSION = [];

session_destroy();

header("Location: /gjcedupay/login.php");
exit;