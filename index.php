<?php
include_once "autoloader.php";
session_start();
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
    <link rel="stylesheet" href="assets/css/style.css" type="text/css">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="assets/js/selectivizr-min.js"></script>
    <script src="assets/js/modernizr-2.6.2-respond-1.1.0.min.js"></script>
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
        <?php
        if ((isset($_GET['rs'])) && ($_GET['rs'] == '1')) //registration was successful.
        {
            echo "<p style='color: white; background-color: seagreen;font-weight:bold;margin-left:30%;'>
                    Your registration was successful
                  </p>";
        }
        if ((isset($_GET['ra'])) && ($_GET['ra'] == '0')) //registration not allowed.
        {
            echo "<p style='color: red; background-color: white;font-weight:bold;margin-left:30%;'>
                    Sorry! User registration is not allowed
                  </p>";
        }
        ?>
        <h2>Welcome to your user manager</h2>
        <p>Manage your users' account details</p>
        <p>Create accounts, update or delete them when you so desire </p>
        <p>Login to get started</p>

        <?php
        if ((isset($_GET['rs'])) && ($_GET['rs'] == '1')) //registration was successful.
        { ?>
            <div style='color: white; background-color: seagreen;font-weight:bold;width:100%;'>
                <p>
                    Thank You for registering with us. We have just sent you an email to
                    your email address with a link to activate your account
                </p>
                <p>
                    If you cannot find the email, check in your SPAM folder, it may be in there
                    The activation link is valid for 24 hours. We look forward to seeing you online.
                </p>
                <p>
                    In case of any problems, feel free to
                    contact us and we will be happy to help.
                </p>
            </div>
            <?php
        } ?>
    </div>
    <article id="footer">
        <?php include_once("includes/footer.inc.php"); ?>
        <div class="clearer"></div>
    </article>
</div>
<script src="/userManager/assets/js/bootstrap.min.js"></script>
</body>
</html>
