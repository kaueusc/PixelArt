<?php
//Small transition page to logout then send user to login page again

require 'config/config.php';
session_destroy(); // Destroys all existing session variables
header("Location: login.php");
?>