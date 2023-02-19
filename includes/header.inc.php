<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="index.php">UserManager</a>
    <a href="index.php" type="button" class="btn btn-primary">Home</a>
    <?php
    if (isset($_SESSION['authenticated'])) { ?>
        <a href="/userManager/dashboard.php" type="button" class="btn btn-primary pull-right">Dashboard</a>
        <a href="/userManager/classes/adminController.php?lg=x" type="button" class="btn btn-primary pull-right">Logout</a>
        <?php
    }
    else
    { ?>
        <a href="/userManager/login.php" type="button" class="btn btn-primary pull-right">Login</a>
        <?php
        require_once('config\Config.php');
        $config = new \config\Config();
        if ($config->getConfig()['allow_registration'] === true)
        { ?>
            <a href="/userManager/register.php" type="button" class="btn btn-primary pull-right">Register</a>
            <?php
        } ?>
        <?php
    } ?>
</nav>






