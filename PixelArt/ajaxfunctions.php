<?php 
    require 'config/config.php';

    //Assoc array mapping color_id to letter in string. Later on, JS does mapping from letter to color.
    $color_id_to_letter = array_combine(range(1,22),range('a','v'));

    //When this instance is created, it also attempts to connect to the database with these credentials
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    // If there is an error, connect_errno will return some kind of error code
    if($mysqli->connect_errno){
      echo $mysqli->connect_error;
      exit();
    }
    //To display accent characters and etc. correctly using the utf8 character set
    $mysqli->set_charset("utf8");
    
    //First get the pixelcolorstring
    $statement = $mysqli->prepare("SELECT pixelcolorstring, pixel_art_id FROM users 
    JOIN pixel_art ON users.pixel_art_id=pixel_art.id WHERE username=?");
	$statement->bind_param("s", $_POST["username"]);
	$executed = $statement->execute();
	if(!$executed){
		echo $mysqli->error;
		exit();
	}

	$results = $statement->get_result();
    $statement->close();
    $row = $results->fetch_assoc();
    $pixelstring = $row["pixelcolorstring"];
    $pixelartid = $row["pixel_art_id"];
    //Check if pixel and color were actually selected (check pixelart.php's form onsubmit):
    //If pixel=-1, then it is a valid value, continue as normal.
    if($_POST["pixel"]!=-1){ 
        //In the string, edit the selected pixel to the new letter
        $pixelstring[$_POST["pixel"]]=$color_id_to_letter[$_POST["color"]];

        //Use another prepared statement to update the fields (title, pixelcolorstring). Use retreived pixel art id.
        $statement_update = $mysqli->prepare("UPDATE pixel_art SET title=?, pixelcolorstring=? WHERE pixel_art.id=?");
        $statement_update->bind_param("ssi", $_POST['title'], $pixelstring, $pixelartid);
        $executed_update = $statement_update->execute();
        if(!$executed_update){
            echo $mysqli->error;
            exit();
        }
        $statement_update->close();
    }
    else{ // ONLY EDIT TITLE!
        //Use another prepared statement to update the fields (title, pixelcolorstring). Use retreived pixel art id.
        $statement_update = $mysqli->prepare("UPDATE pixel_art SET title=? WHERE pixel_art.id=?");
        $statement_update->bind_param("si", $_POST['title'], $pixelartid);
        $executed_update = $statement_update->execute();
        if(!$executed_update){
            echo $mysqli->error;
            exit();
        }
        $statement_update->close();
    }

    echo $pixelstring;

    $mysqli->close();
?>