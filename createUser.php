<?php

require_once('./includes/authenticate.inc.php');

require_once('./includes/adminController.php');
require_once('./includes/Validator.php');
require_once('./includes/Users.php');
require_once('./includes/DB_Adapter.php');


$validator = new Validator();
$user = new Users;

//pass to the User its validator dependency
$user->setValidator($validator);

$adminController = new \adminController($validator, $user);

//only admin users are allowed to be on this page to edit stuff
if ($_SESSION['user_type'] != 'admin')
{
	header('Location: /userManager/dashboard.php?notAdmin=1');
	exit();
}



if (isset($_GET['uid']))
{
	$userId = $_GET['uid'];
	$userForEdit = $user->getUserById($userId);
}

//handle creating a user
if (isset($_POST['createUser'])) {
	$user->createUser($_POST);
}


//handle editing a user
if (isset($_POST['editUser'])) {
	$user->editUser($_POST);
}

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
	<h2><?=((isset($userForEdit))) ? "Update user":"Create a new user"?></h2>

		<div class="row">
			<div class="col-sm-6 col-md-6">
				<div class="signin">
					<div class="row">



							<?php

							if ((isset($_GET['lg'])) && ($_GET['lg'] == 0)) //these are errors coming from grabbing the login details from the db.
							{
								echo "<p style='color: red; background-color: white;margin-left:30%;'>There was an error, check you details and try again</p>";
							}
							if ((isset($_GET['uc'])) && ($_GET['uc'] == 0)) //there were errors creating the user.
							{
								echo "<p style='color: red; background-color: white;margin-left:30%;'>Could not create user, please make sure all fields are entered correctly</p>";
							}
							if ((isset($_GET['uc'])) && ($_GET['uc'] == 'er')) //wrong info was entered into the form.
							{
								echo "<p style='color: red; background-color: white;margin-left:30%;'>Please complete all fields</p>";
							}
							?>


						<div class="col-lg-2"></div>
						<div class="form col-lg-8">
							<form action="" method="post">
								<label for="user_type">User Type</label>
								<select id="user_type" name="user_type" class="form-control" <?=((isset($userForEdit)) && $userForEdit[0]['users_id'] == $_SESSION['custo_id'])?"disabled='true' title='You ADMIN cannot change your own user type'":''?>>
									<option value="">Choose user type</option>
									<option <?=((isset($userForEdit)) && $userForEdit[0]['users_type'] == 'member')?"selected='true'":''?> value="member">Member</option>
									<option <?=((isset($userForEdit)) && $userForEdit[0]['users_type'] == 'admin')?"selected='true'":''?> value="admin">Admin</option>
								</select>

								<label for="username">Username</label>
								<input placeholder="Username" id="username" name="username" class="form-control" type="text" <?php if (isset($userForEdit)) { ?> value="<?=$userForEdit[0]['users_username']?>" <?php } ?> />

								<label for="password">Password</label>
								<input placeholder="Password" id="password" name="password" class="form-control" type="password" <?php if (isset($userForEdit)) { ?> value="<?=$userForEdit[0]['pass']?>" <?php } ?> />
								<input id="userId" name="userId" type="hidden" <?php if (isset($userForEdit)) { ?> value="<?=$userForEdit[0]['users_id']?>" <?php } ?> />
								<input type="hidden" id="createUser" <?php if (isset($_GET['ed'])) { ?> name="editUser" <?php } else { ?> name="createUser" <?php } ?>>
								<br>
								<a href="/userManager/dashboard.php" class="btn btn-warning btn-lg">Cancel</a>
								<button type="submit" class="btn btn-primary btn-lg"><?=isset($_GET['ed'])?'Edit User':'Create User'?></button>
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
