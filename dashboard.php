<?php
use classes\DateConversion;

require_once('./includes/authenticate.inc.php');
include_once "autoloader.php";

$userClass = new classes\Users();
$users = $userClass->getAllUsers();

$dateClass = new DateConversion();
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
        <h1>Admin dashboard</h1>

        <div class="col-sm-12">
            <article class="account-content">

                <h3>Welcome <span><?= $_SESSION['username'] ?></span></h3>
                <?php if ((isset($_GET['uc'])) && ($_GET['uc'] == 1))
                {
                    echo "<p class='tableHead' style='color: green; background-color: white;font-weight:bold;border-radius:4px;margin-left:30%;'>The user was created successfully</p>";

                }
                if ((isset($_GET['del'])) && ($_GET['del'] == 1))
                {
                    echo "<p class='tableHead' style='color: green; background-color: white;font-weight:bold;border-radius:4px;margin-left:30%;'>The user was deleted</p>";

                }

                if ((isset($_GET['notAdmin'])) && ($_GET['notAdmin'] == 1))
                {
                    echo "<p class='tableHead' style='color: red; background-color: yellow;font-weight:bold;border-radius:4px;margin-left:30%;'>Only admin users are allowed to edit users</p>";

                }
                if ((isset($_GET['uo'])) && ($_GET['uo'] == '1')) //user was updated
                {
                    echo "<p style='color: green; background-color: white;margin-left:30%;'>The user was successfully updated</p>";
                }
                if ((isset($_GET['adminselfdel'])) && ($_GET['adminselfdel'] == '0')) //admin cannot self delete
                {
                    echo "<p style='color: red; background-color: white;margin-left:30%;'>You are admin & cannot delete yourself from this screen!</p>";
                } ?>

                <p>Add, update and delete users and their passwords.</p>
                <div class="icon-nav row">

                    <table class="table table-bordered table-responsive">
                        <thead>
                        <tr><td colspan="6" class="tableHead"><h2>Users</h2></td></tr>
                        <tr><td colspan="6"><a href="/createUser.php" class="btn btn-primary btn-md">Create New User</a></td></tr>
                        <tr>
                            <th class="col-xs-3">Username</th>
                            <th class="col-xs-3">Type</th>
                            <th class="col-xs-3">Password</th>
                            <th class="col-xs-1">Created</th>
                            <th class="col-xs-1 tableHead" colspan="2">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($users)) {
                            foreach ($users as $user) { ?>
                                <tr>
                                    <td><?=$user['users_username']?></td>
                                    <td><?=$user['users_type']?></td>
                                    <td><?=$user['pass']?></td>
                                    <td><?=$dateClass->YYYYMMDDtoDDMMYYYY($user['users_created'])?></td>
                                    <?php
                                    if ((isset($_SESSION['user_type'])) && ($_SESSION['user_type'] == 'admin'))
                                    { ?>
                                        <td>
                                            <a href="/createUser.php?ed=1&uid=<?=$user['users_id']?>"><button  class="btn btn-warning btn-sm">Edit</button></a>
                                        </td>
                                        <td>
                                            <a <?=$user['users_id'] == $_SESSION['custo_id']? "disabled title='You cannot delete yourself'" : '' ?>
                                                    onClick="return confirm('Are you sure you wish to delete this user? This action cannot be undone')"
                                                    href="classes/adminController.php?delu=<?=$user['users_id']?>"
                                                    class="btn btn-danger btn-sm">
                                                Delete
                                            </a>
                                        </td>
                                        <?php
                                    }
                                    else
                                    { ?>
                                        <td>

                                        </td>
                                        <td>
                                        </td>
                                        <?php
                                    } ?>
                                </tr>
                            <?php }
                        } ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

    </div>
    <article id="footer">
        <?php include_once("includes/footer.inc.php"); ?>
        <div class="clearer"></div>
    </article>
</div>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
