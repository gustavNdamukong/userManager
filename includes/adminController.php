<?php
ob_start();
require_once('Validator.php');
require_once('Users.php');

//every page restricted to logged-in users must include this file
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

//handle user deletion deletion
if (isset($_GET['delu']))
{
    //only admin users allowed here
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
            $authenticated = $this->authenticate($username, $password);

            if ($authenticated)
            {
                session_start();

                if (!session_id()) { session_start(); }
                $_SESSION['authenticated'] = 'Let Go';

                $_SESSION['start'] = time();
                session_regenerate_id();

                //extract the returned vars
                $_SESSION['custo_id'] = $authenticated['users_id'];
                $_SESSION['user_type'] = $authenticated['users_type'];
                $_SESSION['username'] = $authenticated['users_username'];
                $_SESSION['pass'] = $authenticated['users_pass'];
                $_SESSION['created'] = $authenticated['users_created'];

                session_write_close();

                //We only set a cookie if the user chose to be remembered
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
                exit();
            }
        }
    }


    /**
     * @param $username the username to authenticate the user with
     * @param $password the password to authenticate the user with
     * @return array|bool It returns false if the login fails, or an array of all fields in your users table
     */
    public function authenticate($username, $password)
    {
        $user_model = new Users();

        $loginData = ['users_username' => $username, 'users_pass' => $password];
        return $user_model->authenticateUser($loginData);
    }


    public function logout()
    {
        $_SESSION = array();

        if (isset($_COOKIE[session_name()]))
        {
            setcookie(session_name(), '', time() - 86400, '/');
        }

        if (isset($_COOKIE['rem_me']))
        {
            setcookie('rem_me', '', time()-86400);
        }

        session_destroy();

        header('Location: /userManager/index.php?');
        exit();
    }

}
