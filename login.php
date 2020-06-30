<?php
require_once('./includes/adminController.php');
require_once('./includes/Validator.php');
require_once('./includes/Users.php');
require_once('./includes/DB_Adapter.php');

$validator = new Validator();
$user = new Users;
$adminController = new \adminController($validator, $user);
?>

<!DOCTYPE HTML>
<html lang="en-gb">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<title>User manager</title>

	<link rel="stylesheet" href="css/style.css" type="text/css">
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/selectivizr-min.js"></script>
	<script src="js/modernizr-2.6.2-respond-1.1.0.min.js"></script>

	<!--[if Lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js">
	</script>
	<![endif]-->

</head>

<body>
<div id="mainwrapper" class="container">

	<section id="header">
		<?php include_once("includes/header.inc.php"); ?>
	</section>

	<div id="dataContent">
	<h1>Admin Login</h1>

		<div class="row">
			<div class="col-sm-6 col-md-6">
				<div class="signin">
					<div class="row">



							<?php

							if ((isset($_GET['lg'])) && ($_GET['lg'] == 0)) //these are errors coming from grabbing the login details from the db.
							{
								echo "<p style='color: red; background-color: white;margin-left:30%;'>There was an error, check you details and try again</p>";
							} ?>


						<div class="col-lg-2"></div>
						<div class="form col-lg-8">
							<form action="./includes/adminController.php" method="post">
								<input placeholder="Username" name="username" class="form-control" type="text" />
								<input placeholder="Password" name="login_pwd" class="form-control" type="password">
								<div class="forgot">
									<div class="checkbox">
										<label class="">
											<input type="checkbox" id="signin-remember" name="rem_me">
											Remember me </label>
									</div>
									<br />
								</div>
								<button type="submit" name="login_submit" class="btn btn-primary btn-lg">Sign in</button>
							</form>

						</div>
						<div class="col-lg-2"></div>
					</div>
				</div>
			</div>
		</div>

	</div>


	<article id="footer">
		<?php include_once("includes/footer.inc.php"); ?>
		<div class="clearer"></div>
	</article>


</div>


<script src="js/bootstrap.min.js"></script>


</body>
</html>
