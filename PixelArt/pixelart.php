<?php
    require 'config/config.php';

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($mysqli->connect_errno){
        echo $mysqli->connect_error;
        exit();
    }
    $mysqli->set_charset("utf8");

    //Note: there are 2 users related to this page, the logged in SESSION user, and the GET user whose artwork we are looking at.

    //Get logged in user's colors
    if ( isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] ) {
        //First get their 4 color id's
        $statement_colorid = $mysqli->prepare("SELECT * FROM users WHERE username=?");
        $statement_colorid->bind_param("s", $_SESSION["username"]);
        $executed_colorid = $statement_colorid->execute();
        if(!$executed_colorid){
            echo $mysqli->error;
        }

        $result_colorid = $statement_colorid->get_result();
        $row = $result_colorid->fetch_assoc();

        $color1id = $row["color1_id"];
        $color2id = $row["color2_id"];
        $color3id = $row["color3_id"];
        $color4id = $row["color4_id"];

        //Using the color id's, get the color names too.
        //Note: if admin, get access to all colors.
        if (isset($_SESSION["isadmin"]) && $_SESSION["isadmin"]){
            $statement_color = $mysqli->prepare("SELECT id, color FROM colors");
        }
        else{
            $statement_color = $mysqli->prepare("SELECT id, color FROM colors WHERE id=? OR id=? OR id=? OR id=?");
            $statement_color->bind_param("iiii", $color1id, $color2id, $color3id, $color4id);
        }
        $executed_color = $statement_color->execute();
        if(!$executed_color){
            echo $mysqli->error;
        }
        $result_color = $statement_color->get_result();
        $statement_color->close();
    }

    //check if GET user actually exists or not.
    $statement_userexists = $mysqli->prepare("SELECT * FROM users WHERE username=?");
    $statement_userexists->bind_param("s", $_GET["username"]);
    $executed_userexists = $statement_userexists->execute();
    if(!$executed_userexists){
        echo $mysqli->error;
    }

    $result_userexists = $statement_userexists->get_result();
    $statement_userexists->close();
    //If username does not exist, back out of this entire function, display error.
    if($result_userexists->num_rows != 1) {
        $userdne = "User you searched for does not exist. Go back!";
        $mysqli->close();
    }

    else{
        //Get title and string from GET user's art
        $statement = $mysqli->prepare("SELECT title, pixelcolorstring FROM users
        JOIN pixel_art ON users.pixel_art_id=pixel_art.id WHERE username=?");
        $statement->bind_param("s", $_GET["username"]);
        $executed = $statement->execute();
        if(!$executed){
            echo $mysqli->error;
        }

        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        $statement->close();

        $mysqli->close();
    }   
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixelart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="container-fluid bg-light p-2">
        <div class="row gx-0">
            <div class="col-6 d-flex">
                <a class="logo" href="index.php">Collabart</a>
                <a class="p-2" href="menu.php">Menu</a>
            </div>
            <div class="col-6 d-flex justify-content-end">
            <!-- Depending on whether you are logged in or not, show different things -->
              <?php if ( isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] ) : ?>
                <div class="dropdown">
                  <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $_SESSION["username"]; ?>
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="pixelart.php?username=<?php echo $_SESSION["username"]?>">My Artwork</a></li>
                    <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                  </ul>
                </div>
              <?php else:?>
                <a class="p-2 text-right" href="login.php">Login</a>
                <a class="p-2 text-right" href="register_form.php">Sign up</a>
              <?php endif;?>
            </div>
        </div>
    </nav>
    <!-- If get user does not exist, only display error message; don't display pixel-art -->
    <?php if( isset($userdne) && !empty($userdne) ):?>
        <br>
        <h2> &emsp; <?php echo $userdne; ?></h2>
    <?php else: ?>

    <h1 id="head"> <?php echo $_GET["username"]?>'s Pixelart: <span id="title"> <?php echo $row['title']?> </span></h1>

    <div class="container">
        <div class="row gx-0">
            <div class="col-12 col-lg-8 artwork">
            
            </div>

            <!-- ONLY SHOW FORM IF LOGGED IN -->
            <div class="col-12 col-lg-4 formpad">
            <?php if ( isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] ) : ?>
            <form action="" method="POST">

                <!-- Only show if admin or same user. If not, hide it.  -->
                <?php if ( (isset($_SESSION["isadmin"]) && $_SESSION["isadmin"]) || ($_SESSION["username"] == $_GET["username"]) ) : ?> 
                <div class="form-group row">
                    <label for="title-id" class="col-4 col-form-label">(Only for owner and admin) Change title of artwork:</label>
                    <div class="col-8 vertcenter">
                        <input type="text" id="title-id" name="title" value="<?php echo $row['title']?>">
                    </div>
                </div> <!-- .form-group -->
                <?php else: ?>
                <!-- Use hidden input if not admin/same user -->
                <input type="hidden" id="title-id" name="title" value="<?php echo $row['title']?>">
                <?php endif; ?>

                <div class="form-group row">
                    <label for="color-id" class="col-4 col-form-label">Change pixel color to:</label>
                    <div class="col-8 vertcenter">
                        <select name="color" id="color-id" class="form-control">
                            <option value="" selected disabled>Pick one of your colors</option>
                            <?php while($row_color = $result_color->fetch_assoc()) : ?>
                                <option value='<?php echo $row_color["id"]?>'><?php echo $row_color["color"]?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div> <!-- .form-group -->

                <input type="hidden" id="chosenpixel" name="chosenpixel" value=-1>
                <input type="hidden" id="username" name="username" value=<?php echo $_GET["username"]?>>

                <div class="form-group row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 mt-3">
                        <button type="submit" id="submitbutton" class="btn btn-primary" disabled="disabled">Save Change</button>
                    </div>
                </div> <!-- .form-group -->

            </form>
            <?php else: ?>
            Login to be able to collaborate with others people's pixel arts!
            <?php endif;?>
            </div> <!-- Formpad -->
        </div> <!-- Row -->
    </div> <!-- Container -->
    <br>
    <?php endif; ?>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script>
        //creates blank grid
        let chosenpixel=-1;
        let htmlString = `<div class="flexcontainer"></div>`;
        document.querySelector(".artwork").innerHTML += htmlString;
        for(let i=0; i<400; i++){
            let htmlString = `<div class="pixel" id="pix${i}"></div>`;
            document.querySelector(".flexcontainer").innerHTML += htmlString;
        }

        //calls viewArt at the beginning to draw the initial artwork. Check viewArt function at bottom of file.
        viewArt("<?php echo $row["pixelcolorstring"]?>");

        //To enable the submit button, either change title, or select both a pixel and color.
        let selectedpixel=false;
        let selectedcolor=false;

        //checks title has been edited. 
        //Note: if not logged in, will crash the rest of JS. However, that's ok since not logged in users
        //shouldn't be able to select pixels in the first place, so there's no need for JS for them.
        document.querySelector("#title-id").oninput = function(){
            document.querySelector("#submitbutton").disabled=false;
        }

        //checks color has been selected.         
        document.querySelector("#color-id").oninput = function(){
            selectedcolor=true;
            // If pixel already selected, enable submit.
            if(selectedpixel==true){
                document.querySelector("#submitbutton").disabled=false;
            }
        }

        //checks if pixel was selected. Add border, update value for form.
        let allpixels = document.querySelectorAll(".pixel");
		for(let i=0; i<allpixels.length; i++){
            allpixels[i].onclick = function(){
                // Reset old chosenpixel's border, shadow, zindex.
                if(chosenpixel>=0 && chosenpixel<400){
                    allpixels[chosenpixel].style.border = "1px solid black";
                    allpixels[chosenpixel].style.boxShadow = "0 0 0 0 red";
                    allpixels[chosenpixel].style.zIndex = 0;
                }
                //Update chosenpixel, as well as hidden value in form.
                chosenpixel = i;
			    console.log("Clicked pixel: "+i);
                document.querySelector("#chosenpixel").value = i;
                //Update styling
				this.style.border = "1px solid red";
                this.style.boxShadow = "0 0 10px 10px red";
                this.style.zIndex = 1;

                selectedpixel=true;
                //If color already selected, enable submit.
                if(selectedcolor==true){
                    document.querySelector("#submitbutton").disabled=false;
                }
            }
        }

        //Define ajaxPost – called whenever user submits the form.
        function ajaxPost(endpointUrl, postdata, returnFunction){
			var xhr = new XMLHttpRequest();
			xhr.open('POST', endpointUrl, true);
			// POST requests also require some information in the header. For example, the type of content that will be sent over
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.onreadystatechange = function(){
				if (xhr.readyState == XMLHttpRequest.DONE) {
					if (xhr.status == 200) {
						returnFunction( xhr.responseText );
					} else {
						alert('AJAX Error.');
						console.log(xhr.status);
					}
				}
			}
			// in a POST request, need to send data separately like below, unlike GET request.
			xhr.send(postdata);
		};

        // Edit behavior of submitting the form.
		document.querySelector("form").onsubmit = function(event){
			event.preventDefault();
			//Get all the form values.
            let username = document.querySelector("#username").value;
			let title = document.querySelector("#title-id").value.trim();
            //It's possible that you submitted without selecting color and/or pixel (when you edited title only). Thus, we need edge case, 
            //which will be evaluated in the backend function.
            let color="nochange";
            let pixel=-1;
            if(selectedpixel==true && selectedcolor==true){
                color = document.querySelector("#color-id").value;
                pixel = document.querySelector("#chosenpixel").value;
            }

			// Call the ajax function: make a ajaxPost request
            ajaxPost("ajaxfunctions.php", `username=${username}&title=${title}&color=${color}&pixel=${pixel}`, viewArt);
		}

        //viewArt is a javascript function that creates the artwork:
        //It takes the string and parses it to add background color to each pixel
        function viewArt(string) {
            console.log("pixelcolorstring is currently:"+ string);
            //create Javascript Object with keys as the letters and values as the color.
            let letter_to_color = {
                a: 'white',
                b: 'black',
                c: 'gray',
                d: 'yellow',
                e: 'goldenrod',
                f: 'orange',
                g: 'orangered',
                h: 'red',
                i: 'maroon',
                j: 'pink',
                k: 'lightgreen',
                l: 'green',
                m: 'darkgreen',
                n: 'lightskyblue',
                o: 'cyan',
                p: 'teal',
                q: 'blue',
                r: 'navy',
                s: 'purple',
                t: 'magenta',
                u: 'saddlebrown',
                v: 'tan'
            };
            //for each pixel, change its backgroundColor based on the letter->color mapping for that char in the string.
            for(let i=0; i<400; i++){
                document.querySelector(`#pix${i}`).style.backgroundColor = letter_to_color[string[i]];
            }
            //Also update artwork title on header section.
            let title = document.querySelector("#title-id").value.trim();
            document.querySelector("#title").innerText = title;
        }
    </script>
</body>
</html>