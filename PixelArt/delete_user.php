<?php
//Small transition page to delete user then send admin back to menu page
require 'config/config.php';

//As a check, make sure user is admin.
if ( isset($_SESSION["isadmin"]) && $_SESSION["isadmin"] ){

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($mysqli->connect_errno) {
        echo $mysqli->connect_error;
        exit();
    }
    // Prepared statements
    $statement = $mysqli->prepare("DELETE FROM users WHERE username = ?");
    $statement->bind_param("s", $_GET["username"]);
    $executed = $statement->execute();
    if(!$executed){
        echo $mysqli->error;
        exit();
    }
    $statement->close();
    $mysqli->close();
}
header("Location: menu.php");
?>