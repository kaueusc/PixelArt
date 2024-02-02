<?php
  require 'config/config.php';

  //When this instance is created, it also attempts to connect to the database with these credentials
  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  // If there is an error, connect_errno will return some kind of error code
  if($mysqli->connect_errno){
    echo $mysqli->connect_error;
    exit();
  }
  //To display accent characters and etc. correctly using the utf8 character set
  $mysqli->set_charset("utf8");

  // Get up to 10 random user's pixelstrings from database (join with users ensures that the user still exists/has not been deleted yet).
  $sql = "SELECT pixelcolorstring, username, title FROM users
    JOIN pixel_art ON users.pixel_art_id=pixel_art.id
    ORDER BY RAND() LIMIT 10;";

  //Submit the query
  $results = $mysqli->query($sql);
  if(!$results){
    echo $mysqli->error;
    exit();
  }

  $mysqli->close();

  while($row = $results->fetch_assoc()){
      $stringarray[]=$row["pixelcolorstring"];
      $userarray[]=$row["username"];
      $titlearray[]=$row["title"];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
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
    <h1 id="head"> Collabart</h1>

    <div class="indexdiv">
        <h1 class="subheader"> Welcome to Collabart! </h1>
        <br>

        <h2 class="subheader"> What is Collabart? </h2>
        <p class="largertext"> Collabart is a website where you can collaborate with others to collectively create pixel art pieces. </p>

        <h2 class="subheader"> How does Collabart work? </h2>
        <p class="largertext"> Each user will given a 20x20 pixel art canvas, and also be randomly assigned just 4 colors that they can use. This makes art pieces that you make alone quite dull and limited in creativity. However, this is where the key theme of Collabart comes into play; other signed in users, who have different assigned colors, can use their colors to edit your artwork. That being said, only you can edit the title of your own artwork, which helps gives other users an idea of the final artwork you wish to create.</p>

        <?php if ( isset($_SESSION["isadmin"]) && $_SESSION["isadmin"]) : ?>
            <h2 class="subheader"> ADMIN PRIVILEGES </h2>
            <ul class="largertext">
                <li>Access to all 22 colors</li>
                <li>Can edit anyone's artwork title</li>
                <li>Can delete non-admin users</li>
            </ul>
        <?php endif; ?>

        <h2 class="subheader"> Sounds great! How do I begin? </h2>
        <p class="largertext"> All you need to do is register with a username and password; then you can log in and begin your artwork, or help other users develop their artwork! </p>

        <h2 class="subheader"> Current Artworks on Collabart: </h2>
 
        <h2 class="subheader"><span id="title"> </span> </h2>
        <div class="maxwidth">
            <div class="artwork">
            
            </div>
        </div>
        <br>
        
    </div>

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script>
        //creates blank grid
        let htmlString = `<div class="flexcontainer"></div>`;
        document.querySelector(".artwork").innerHTML += htmlString;
        for(let i=0; i<400; i++){
            let htmlString = `<div class="pixel" id="pix${i}"></div>`;
            document.querySelector(".flexcontainer").innerHTML += htmlString;
        }

        let index=0;
        let stringarray = <?php echo json_encode($stringarray); ?>;
        let userarray = <?php echo json_encode($userarray); ?>;
        let titlearray = <?php echo json_encode($titlearray); ?>;
        console.log(stringarray);

        setInterval(viewArt, 3000);

        //viewArt creates the artwork, slightly adapted from the function in the pixelart.php file:
        function viewArt() {
            let string = stringarray[index];
            console.log("pixelcolorstring is currently:"+ string);
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
            //Update to provide username and title
            document.querySelector("#title").innerText = userarray[index]+"'s Artwork - "+titlearray[index];

            //Update index to prepare for next iteration
            index++;
            if(index >= stringarray.length){
                index=0;
            }
        }
    </script>
</body>
</html>