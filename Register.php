<?php

include_once "autoloader.php";
//make the form sticky
session_start();

if (!(isset($_SESSION['postBack'])))
{
    $_SESSION['postBack'] = [];
}
$firstname = $_SESSION['postBack']['first_name'] ?? '';
$lastname = $_SESSION['postBack']['last_name'] ?? '';
$username = $_SESSION['postBack']['username'] ?? '';
$phone = $_SESSION['postBack']['contact_tel'] ?? '';
$email = $_SESSION['postBack']['email'] ?? '';
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

    <div class="main">
        <section class="content account">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <?php
                        if ((isset($_GET['tc'])) && ($_GET['tc'] == '0')) //user did not agree to the terms &conditions.
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>Please accept our Terms and Conditions</p>";
                        }
                        if ((isset($_GET['st'])) && ($_GET['st'] == '0')) //the hidden captcha was filled in.
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>Something went wrong, try again</p>";
                        }
                        if ((isset($_GET['ca'])) && ($_GET['ca'] == '0')) //the captcha addition was entered wrongly.
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>Prove you are not a bot by answering the question correctly</p>";
                        }
                        if ((isset($_GET['pm'])) && ($_GET['pm'] == '0')) //the passwords don't match.
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>Your passwords do not match</p>";
                        }
                        if ((isset($_GET['ee'])) && ($_GET['ee'] == '1')) //email exists (email address already exists).
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>That email address already exists</p>";
                        }
                        if ((isset($_GET['rs'])) && ($_GET['rs'] == '0')) //Registration not successful.
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>
                                                Something went wrong. Please make sure all fields are completed, then try again or contact us for help. Thanks
                                                </p>";
                        }
                        if ((isset($_GET['fe'])) && ($_GET['fe'] == '1')) //Form empty (the form was submitted blank).
                        {
                            echo "<p style='color: red; background-color: white;margin-left:30%;'>
                                                Something went wrong. Please make sure all fields are completed, then try again or contact us for help. Thanks
                                                </p>";
                        }
                        ?>
                        <article class="account-content" style="height: auto;padding-bottom: 50%;">
                            <div style="margin-top: 100px;">
                                <form id="regis_form" method="post"
                                      action="classes/adminController.php?rg=1">

                                    <div id="regis_panel" class="panel panel-primary">
                                        <a href="login.php" class="btn btn-success btn-lg pull-right">Already a member? Login</a>

                                        <div class="panel-heading">

                                            <h3 class="panel-title text-center"
                                                style="color: #FFFFFF;">Register</h3>

                                        </div>

                                        <div class="panel-body">
                                            <p>
                                                <label for="firstname">Enter your first name</label>
                                                <input type="text" class="form-control regisforminput"
                                                       placeholder="First name"
                                                       maxlength="32"
                                                       name="firstname" id="firstname"
                                                       value="<?=htmlentities($firstname, ENT_COMPAT, 'UTF-8')?>"/>
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="surname">Enter your last name</label>
                                                <input type="text" class="form-control regisforminput"
                                                       placeholder="Surname"
                                                       maxlength="32"
                                                       name="surname" id="surname"
                                                       value="<?=htmlentities($lastname, ENT_COMPAT, 'UTF-8')?>"/>
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="username">Enter a username</label>
                                                <span id='info'></span></p><input type="text"
                                                                                  class="form-control regisforminput"
                                                                                  placeholder="Username"
                                                                                  maxlength="20"
                                                                                  name="username"
                                                                                  id="username"
                                                                                  value="<?=htmlentities($username, ENT_COMPAT, 'UTF-8')?>" />
                                            <hr/>

                                            <p>
                                                <label for="password">Enter password (Max 12 characters)</label>
                                                <input type="password"
                                                       class="form-control regisforminput"
                                                       placeholder="Password"
                                                       maxlength="12"
                                                       name="pwd"
                                                       id="pwd" />
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="conf_pwd">Confirm your password</label>
                                                <input type="password"
                                                       class="form-control regisforminput"
                                                       maxlength="12" name="conf_pwd"
                                                       placeholder="Confirm password"
                                                       id="conf_pwd" required>
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="phone">Phone number</label>
                                                <input type="text"
                                                       class="form-control regisforminput"
                                                       maxlength="16"
                                                       name="phone"
                                                       placeholder="Phone number"
                                                       id="phone"
                                                       value="<?=htmlentities($phone, ENT_COMPAT, 'UTF-8')?>"
                                                />
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="email">Your email address</label>
                                                <input type="text" class="form-control regisforminput"
                                                       maxlength="64"
                                                       name="email"
                                                       placeholder="Email"
                                                       id="email" value="<?=htmlentities($email, ENT_COMPAT, 'UTF-8')?>"/>
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="captcha_addition">
                                                    If you are not a robot, what is 3 + 1?</label>
                                                <input type="number"
                                                       class="form-control regisforminput"
                                                       name="captcha_addition"
                                                       id="captcha_addition"
                                                       min="0" />
                                            </p>
                                            <hr/>

                                            <p>
                                                <label for="agreeToTerms">
                                                    Terms & Conditions
                                                </label>
                                                <input type="checkbox"
                                                       class="form-control regisforminput"
                                                       name="agreeToTerms"
                                                       id="agreeToTerms"
                                                       required
                                                /><a href="#" target="_blank">
                                                    Agree to our Terms & Conditions
                                                </a>&nbsp;(Opens TC page in a different tab)
                                            </p>
                                            <hr/>

                                            <input type="hidden" name="captcha_hidden" />
                                        </div>

                                        <div class="panel-footer clearfix">

                                            <div class="pull-right">
                                                <input type="reset" class="btn btn-warning"
                                                       role="button"
                                                       value="Reset"/>
                                                <input type="submit"
                                                       class="btn btn-primary"
                                                       value="Register"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script src="/userManager/assets/js/bootstrap.min.js"></script>
<script type="text/javascript">

    $(document).on('blur', '#regis_form #username', function()
    {
        checkUsername(this);
    });


    /**
     * Checks in the DB if that username is already in use.
     * It returns some text like 'username available' or 'username already taken' which you can display in a span
     *    next to the username input field
     * @param username
     */
    function checkUsername(username) {
        if (username.value == '') {
            document.getElementById('info').innerHTML = '';
            return
        }

        params = "checkusername=" + username.value
        request = new ajaxRequest()
        request.open("POST", "classes/adminController.php", true)
        request.setRequestHeader("Content-type",
            "application/x-www-form-urlencoded")

        request.onreadystatechange = function () {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    if (this.responseText != null) {
                        document.getElementById('info').innerHTML =
                            this.responseText
                    }
                    else alert("Ajax error: No data received")
                }
                else alert("Ajax error: " + this.statusText)
            }
        }
        request.send(params)
    }


    function ajaxRequest() {
        try {
            var request = new XMLHttpRequest()
        }
        catch (e1) {
            try {
                request = new ActiveXObject("Msxml2.XMLHTTP")
            }
            catch (e2) {
                try {
                    request = new ActiveXObject("Microsoft.XMLHTTP")
                }
                catch (e3) {
                    request = false
                }
            }
        }
        return request
    }
</script>
</body>
</html>
