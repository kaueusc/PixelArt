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

  $sql = "SELECT username, title, isadmin FROM users
    JOIN pixel_art ON users.pixel_art_id=pixel_art.id;";

  //Submit the query
  $results = $mysqli->query($sql);
  if(!$results){
    echo $mysqli->error;
    exit();
  }

  $mysqli->close();
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
                <a class="p-2 current_page" href="menu.php">Menu</a>
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
    <h1 id="head"> Collabart Artworks</h1>
    <div class="center">
      <!-- If logged in, link to artwork. If not, show message. -->
      <?php if ( isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] ) : ?>
        <a href="pixelart.php?username=<?php echo $_SESSION["username"]?>"> <p id="yourart"> Your artwork </p></a>
      <?php else: ?>
        <p id="yourart"> Log in to have your own art! </p>
      <?php endif; ?>
    </div>

    <div class="table_div">
        <table class="table">
            <thead>
              <tr>
                <th scope="col">Username</th>
                <th scope="col">Artwork Title</th>
              </tr>
            </thead>
            <tbody>

              <!-- Sample entry -->
              <!-- <tr>
                <td>Otto</td>
                <td><a href="pixelart.php">Butterfly</a></td>
              </tr> -->

              <?php while($row = $results->fetch_assoc()) : ?>
                <tr>
                <!-- If admin, can delete user -->
                <?php if (( isset($_SESSION["isadmin"]) && $_SESSION["isadmin"] ) && $row["isadmin"]==0) : ?>
                  
                  <td><a onclick="return confirm('Are you sure you want to remove account <?php echo $row['username']?>?')" 
                  href="delete_user.php?username=<?php echo $row["username"]?>"><?php echo $row["username"]?></a></td>

                <?php else: ?>
                  <td><?php echo $row["username"]?></td>
                <?php endif; ?>

                  <td><a href="pixelart.php?username=<?php echo $row["username"]?>"><?php echo $row["title"]?></a></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>