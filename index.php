<?php
//every page needing to restrict access only to logged in users must include this file
require_once('./includes/authenticate.inc.php');

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
		<h2>Welcome to your user manager</h2>

		<p>Manage your users' account details</p>
		<p>Create accounts, update or delete them when you so desire </p>
		<p>Login to get started</p>


	</div>


	<article id="footer">
		<?php include_once("includes/footer.inc.php"); ?>
		<div class="clearer"></div>
	</article>


</div>


<script src="js/bootstrap.min.js"></script>


</body>
</html>
