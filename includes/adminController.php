<?php
ob_start();
require_once('Validator.php');
require_once('Users.php');

//every page needing to restrict access only to logged in users must include this file
require_once('authenticate.inc.php');

$validator = new Validator();
$user = new Users();
if ((isset($_POST)) && (!empty($_POST))) {
    $admin = new adminController($validator, $user);
    $admin->login($_POST);
}

//the user wants to be logged out
if ((isset($_GET['lg'])) && ($_GET['lg'] == 'x'))
{
    $admin = new adminController($validator, $user);
    $admin->logout();
}



//process deletion of a user
if (isset($_GET['delu']))
{
    //only admin users are allowed to be on this page to edit stuff, so make sure they have admin rights
    if ($_SESSION['user_type'] != 'admin')
    {
        header('Location: /userManager/dashboard.php?notAdmin=1');
        exit();
    }
    else if(($_SESSION['user_type'] == 'admin') && ($_SESSION['custo_id'] == $_GET['delu']))
    {
        //admin users cannot delete themselves
        header('Location: /userManager/dashboard.php?adminselfdel=0');
        exit();
    }
    else
    {
        $user = new Users();
        $userId = $_GET['delu'];
        $whereClause = ['users_id' => $userId];
        $user->deleteWhere($whereClause);
    }
}


class adminController  {

    protected $validator = null;

    protected $user = null;

    public function __construct(Validator $validator, Users $users)
    {
        $this->validator = $validator;
        $this->user = $users;
    }




                                
    public function login()
    {
        $username = $password = $rem_me = $fail = $email = false;
        $errors = array();

        if(isset($_POST['username']))
        {
            $username = $this->validator->fix_string($_POST['username']);
        }

        if (isset($_POST['login_pwd']))
        {
            $password = $this->validator->fix_string($_POST['login_pwd']);
        }


        if (isset($_POST['rem_me']))
        {

            $rem_me = ($_POST['rem_me']);
        }


        if($username)
        {
            $fail .= $this->validator->validate_username($username);
        }

        $fail .= $this->validator->validate_password($password);


        if ($fail == "")
        {
            $authenticated = $this->authenticate($username, $password, $rem_me);
        }

    }







    public function authenticate($username, $password, $rem_me = false)
    {
        $login_errors = array();

        if ($authenticated = $this->user->authenticateUser($username, $password))
        {
            //set a cookie if the user chose to be remembered
            if ($rem_me)
            {
                setcookie('rem_me', $username, time() + 172800); //48 hours
            }

            header('Location: /userManager/dashboard.php?lg=1');
            exit();

        }
        else
        {
            header('Location: /userManager/login.php?lg=0');
        }

    }




    public function logout()
    {
        $_SESSION = array();

        // invalidate the session cookie
        if (isset($_COOKIE[session_name()]))
        {
            setcookie(session_name(), '', time() - 86400, '/');
        }

        //Delete 'remember' cookie if any.
        if (isset($_COOKIE['rem_me']))
        {
            setcookie('rem_me', '', time()-86400);
        }

        //end session and redirect
        session_destroy();

        //throw them back to the home page
        header('Location: /userManager/index.php?');
        exit();

    }








}
