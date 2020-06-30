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

    $user = new Users();
    $userId = $_GET['delu'];
    $whereClause = ['users_id' => $userId];
    $user->deleteWhere($whereClause);
}


class adminController  {

    protected $validator = null;

    protected $user = null;

    public function __construct(Validator $validator, Users $users)
    {
        //$this->validator = new Validator();
        //$this->user = new Users();
        $this->validator = $validator;
        $this->user = $users;
    }




                                
    public function login()
    {
        //echo '<pre>';
        //var_dump($_REQUEST); die();


        $username = $password = $rem_me = $fail = $email = false;
        $errors = array();


        //die(print_r($_POST));
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
            //connect to DB
            //authenticate the user as the admin, create the session id, and redirect appropriately

            $authenticated = $this->authenticate($username, $password, $rem_me);

        }

    }







    public function authenticate($username, $password, $rem_me = false)
    {
        //this the file where data from the db is retrieved n compared with that from the log in form.
        //n a session is created on success, and the user redirected to where they need to go.
        $login_errors = array();

        if ($authenticated = $this->user->authenticateUser($username, $password))
        {
            //A match was found
            //I will only set a cookie if the user chose to be remembered
            if ($rem_me)
            {
                setcookie('rem_me', $username, time() + 172800); //48 hours
            }

            //log in was successful
            header('Location: /userManager/dashboard.php?lg=1');
            exit();

        }
        else
        {
            // if no match, prepare error message
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

        //This is the cookie i set with rem_me at log in, we delete it coz if the user wants to be logged out.
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
