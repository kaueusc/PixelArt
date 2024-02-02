<?php
require 'config/config.php';

//Using session variables, we can check if user is already logged in or not. If logged in, kick them out of this page
if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {

	//If we get $_POST["username"], it means user tried to submit the registration form (as opposed to user just getting to the page (GET request))
	if ( isset($_POST['username']) && isset($_POST['password']) ) {
		//Checking username and password was filled out.
		if ( empty($_POST['username']) || empty($_POST['password']) ) {
			$error = "Please enter username and password.";
		}
		else {
			//User filled out both. Connect to DB.
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

			if($mysqli->connect_errno) {
				echo $mysqli->connect_error;
				exit();
			}

			//First, check if username is already there.
			$statement_alreadyused = $mysqli->prepare("SELECT * FROM users WHERE username=?");
			$statement_alreadyused->bind_param("s", $_POST["username"]);
			$executed_alreadyused = $statement_alreadyused->execute();
			if(!$executed_alreadyused){
				echo $mysqli->error;
			}
			//Get number of results
			$statement_alreadyused->store_result();
			$numrows=$statement_alreadyused->num_rows();
			$statement_alreadyused->close();
			//If we get ANY result back, it means this username or email is taken!
			if($numrows>0){
				$error = "Username or email has already been taken. Please choose another one.";
			}
			else{
				//Before creating user, first create pixel art and get its id.
				$title_template = $_POST["username"] . "'s artwork";
				//Defaut pixelstring (white canvas)
				$pixel_string="";
				for($i=0; $i<400; $i++){
					//a is white, base color
					$pixel_string=$pixel_string . "a";
				}
				$statement_art = $mysqli->prepare("INSERT INTO pixel_art(title, pixelcolorstring) VALUE(?,?)");
				$statement_art->bind_param("ss", $title_template, $pixel_string);
				$executed_art = $statement_art->execute();
				if(!$executed_art){
					echo $mysqli->error;
				}
				//Get id of just created pixel_art entry. Could possibly use @@IDENTITY, but feel safer using this way to get id.
				$art_id = $statement_art->insert_id;

				$statement_art->close();

				//Get 4 random values for colors (id is between 1 and 26, since there are 26 colors)
				$array_color_id=range(1,22);
				shuffle($array_color_id);
				$color1=$array_color_id[0];
				$color2=$array_color_id[1];
				$color3=$array_color_id[2];
				$color4=$array_color_id[3];

				//Hash the password
				$password = hash("sha256", $_POST["password"]);
				//Add this info as a new record into the newly created user table.
				$statement = $mysqli->prepare("INSERT INTO users(username, password, isadmin, pixel_art_id, color1_id, color2_id, color3_id, color4_id) VALUE(?,?,0,$art_id, $color1, $color2, $color3, $color4)");
				$statement->bind_param("ss", $_POST["username"], $password);
				$executed = $statement->execute();
				if(!$executed){
					echo $mysqli->error;
				}
				$statement->close();

				$success_message="Account Created! Go to login page to begin!";
			}
			$mysqli->close();
		} 
	}
}
else{
	//This user is logged in so they don't need to see this page. Redirect them to the home page.
	header("Location: menu.php");
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register | Collabart</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
	<nav class="container-fluid bg-light p-2">
        <div class="row">
			<div class="col-6 d-flex">
				<a class="logo" href="index.php">Collabart</a>
                <a class="p-2" href="menu.php">Menu</a>
            </div>
            <div class="col-6 d-flex justify-content-end">
    
                <a class="p-2 text-right" href="login.php">Login</a>
                <a class="p-2 text-right current_page" href="register_form.php">Sign up</a>

                <!-- <div class="dropdown">
                    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                      Username
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                      <li><a class="dropdown-item" href="pixelart.html">My Artwork</a></li>
                      <li><a class="dropdown-item" href="login.php">Log Out</a></li>
                    </ul>
                </div> -->
            </div>
        </div>
    </nav>

	<div class="container">
		<div class="row">
			<h1 class="col-12 mt-4 mb-4">Register</h1>
		</div> <!-- .row -->
	</div> <!-- .container -->

	<div class="container">
		<!-- Display any error or success messages -->
		<?php if ( isset($error) && !empty($error) ) : ?>
			<div class="text-danger">
				<?php echo $error; ?>
			</div>
		<?php endif; ?>

		<?php if ( isset($success_message) && !empty($success_message) ) : ?>
			<div class="text-success">
				<?php echo $success_message ?>
			</div>
		<?php endif; ?>

		<form action="register_form.php" method="POST">

			<div class="form-group row login-div">
				<label for="username-id" class="col-sm-3 col-form-label text-sm-right">Username: <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="username-id" name="username">
					<small id="username-error" class="invalid-feedback">Username is required.</small>
				</div>
			</div> <!-- .form-group -->

			<div class="form-group row login-div">
				<label for="password-id" class="col-sm-3 col-form-label text-sm-right">Password: <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<input type="password" class="form-control" id="password-id" name="password">
					<small id="password-error" class="invalid-feedback">Password is required.</small>
				</div>
			</div> <!-- .form-group -->

			<div class="row">
				<div class="ml-auto col-sm-9">
					<span class="text-danger font-italic">* Required</span>
				</div>
			</div> <!-- .form-group -->

			<div class="form-group row">
				<div class="col-sm-3"></div>
				<div class="col-sm-9 mt-3">
					<button type="submit" class="btn btn-primary">Register</button>
					<a href="login.php" role="button" class="btn btn-info">Already have an account</a>
				</div>
			</div> <!-- .form-group -->
		</form>
	</div> <!-- .container -->
	
	<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
	<script>
		//Javascript is the first line of defense. It is checking that user has provided all the required inputs. However, javascript can be disabled or hacked or something so JS cannot be ur ONLY line of defense.
		document.querySelector('form').onsubmit = function(){
			if ( document.querySelector('#username-id').value.trim().length == 0 ) {
				document.querySelector('#username-id').classList.add('is-invalid');
			} else {
				document.querySelector('#username-id').classList.remove('is-invalid');
			}

			if ( document.querySelector('#email-id').value.trim().length == 0 ) {
				document.querySelector('#email-id').classList.add('is-invalid');
			} else {
				document.querySelector('#email-id').classList.remove('is-invalid');
			}

			if ( document.querySelector('#password-id').value.trim().length == 0 ) {
				document.querySelector('#password-id').classList.add('is-invalid');
			} else {
				document.querySelector('#password-id').classList.remove('is-invalid');
			}

			return ( !document.querySelectorAll('.is-invalid').length > 0 );
		}
	</script>
</body>
</html>