<?php
require 'config/config.php';

//Using session variables, we can check if user is already logged in or not. If logged in, kick them out of this page
if(!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {

	//If we get $_POST["username"], it means user tried to submit the login form (as opposed to user just getting to the page (GET request))
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
			//Check if username and password combo is correct.

			//Hash user's input and compare this hashed version to the hashed password in the database.
			$passwordInput = hash("sha256", $_POST["password"]);

			$statement = $mysqli->prepare("SELECT * FROM users WHERE username=? AND password=?");
			$statement->bind_param("ss", $_POST["username"], $passwordInput);
			$executed = $statement->execute();
			if(!$executed){
				echo $mysqli->error;
			}

			//Get result
			$result = $statement->get_result();
			if(!$result) {
				echo $mysqli->error;
				exit();
			}
			$statement->close();

			// If we get a result back, that means there was a username/pw combo match!
			if($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				//echo "logged in!";
				//Use session to store some simple information that we want to persist througout the web application
				$_SESSION["logged_in"] = true;
				$_SESSION["username"] = $_POST["username"];
				$_SESSION["id"] = $row["id"];
				$_SESSION["isadmin"] = $row["isadmin"];

				//Redirect the user to the home page
				header("Location: menu.php");
			}
			else {
				$error = "Invalid username or password.";
			}
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
	<title>Login</title>
	<!-- Bootstrap CSS -->
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
    
                <a class="p-2 text-right current_page" href="login.php">Login</a>
                <a class="p-2 text-right" href="register_form.php">Sign up</a>

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
			<h1 class="col-12 mt-4 mb-4">Login</h1>
		</div> <!-- .row -->
	</div> <!-- .container -->


	<div class="container">
		<!-- Show any error messages -->
		<?php if ( isset($error) && !empty($error) ) : ?>
				<div class="text-danger">
					<?php echo $error; ?>
				</div>
		<?php endif; ?>

		<form action="login.php" method="POST">

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

			<div class="form-group row login-div">
				<div class="col-sm-3"></div>
				<div class="col-sm-9 mt-3">
					<button type="submit" class="btn btn-primary">Login</button>
					<a href="register_form.php" role="button" class="btn btn-info">Create an account</a>
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
				document.querySelector('#username-id').classList.add('is-invalid'); //what does is-invalid do?
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

			return ( !document.querySelectorAll('.is-invalid').length > 0 ); //what happens if this returns false? stops it from submitting?
		}
	</script>

</body>
</html>