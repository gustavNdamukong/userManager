<?php
namespace classes;

use classes\Validator;
use config\Config;
use classes\CheckPassword;
use classes\Messenger;

ob_start();
require_once('Validator.php');
if(file_exists("../autoloader.php")) {
    include_once "../autoloader.php";
}
else if (file_exists("autoloader.php"))
{
    include_once "autoloader.php";
}

$validator = new Validator();
$user = new Users();
$config = new Config();
$messenger = new Messenger();

//-----------------HANDLE REGISTRATION---------------------
if ((isset($_GET['rg'])) && ($_GET['rg'] == 1)) {
    if ((isset($_POST)) && (!empty($_POST))) {
        $admin = new adminController($validator, $user, $config, $messenger);
        $admin->register($_POST);
    }
}
//-----------------END REGISTRATION-----------------------

//-----------------HANDLE LOGIN---------------------------
if ((isset($_GET['lg'])) && ($_GET['lg'] == 1)) {
    if ((isset($_POST)) && (!empty($_POST))) {
        $admin = new adminController($validator, $user, $config, $messenger);
        $admin->login($_POST);
    }
}
//-----------------END LOGIN------------------------------

//-----------------HANDLE LOGOUT--------------------------
if ((isset($_GET['lg'])) && ($_GET['lg'] == 'x'))
{
    $admin = new adminController($validator, $user, $config, $messenger);
    $admin->logout();
}
//-------------------END LOGOUT---------------------------

//-----------------HANDLE USER DELETION-------------------
if (isset($_GET['delu']))
{
    $admin = new adminController($validator, $user, $config, $messenger);
    $admin->deleteUser();
}
//------------------END USER DELETION---------------------

//-----------------VALIDATE USERNAME (AJAX)-------------------
if (($_POST) && (isset($_POST['checkusername'])))
{
    $admin = new adminController($validator, $user, $config, $messenger);
    $admin->checkUsername();
}
//-----------------END VALIDATE USERNAME (AJAX)---------------

//-----------------ACTIVATE NEW USER EMAIL-------------------
if ((isset($_GET['verifyEmail'])) && (isset($_GET['em'])))
{
    $admin = new adminController($validator, $user, $config, $messenger);
    $admin->verifyEmail();
}
//----------------END ACTIVATE NEW USER EMAIL----------------
class adminController  {
    protected ?Validator $validator = null;
    protected ?Users $user = null;
    protected ?Config $config = null;
    protected ?Messenger $messenger = null;

    public function __construct(Validator $validator, Users $users, Config  $config, Messenger $messenger)
    {
        $this->validator = $validator;
        $this->user = $users;
        $this->config = $config;
        $this->messenger = $messenger;
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
     * @param string $username the username to authenticate the user with
     * @param string $password the password to authenticate the user with
     * @return array|bool It returns false if the login fails, or an array of all fields in your users table
     */
    public function authenticate(string $username, string $password)
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


    public function deleteUser() {
        //only admin users allowed here
        session_start();
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


    public function register()
    {
        if ($this->config->getConfig()['allow_registration']) {
            $words = array('chokochohilarious', 'jammijamjim', 'tolambomanulo', 'kilabakula', 'jamborayla', 'kingkong', 'bayofbiscay', 'camprocol', 'tuxedo', 'camgas', 'manyolo', 'geomasso', 'ndipakem', 'nolimit', 'chopman', 'builders', 'jackstraw', 'colgate', 'jimreeves', 'popol', 'bamenda', 'buea', 'bafoussam', 'nkongsamba', 'ahidjo', 'douala', 'yaounde', 'bertoua', 'ebolowa', 'ngaoundere', 'maroua', 'foumban', 'bafang', 'lavoir', 'brancher', 'sicia', 'achana', 'francais', 'anglais', 'french', 'english', 'business', 'bosco', 'shokoloko', 'bangoshay', 'papou', 'wembley', 'hausa');

            $randomnumber = rand(0, 53);
            $randword = rand() . rand(0, 32000);
            $randCode = "$words[$randomnumber]" . "$randword";
            $activationCode = md5(trim($randCode));

            $firstname = $surname = $username = $password = $phone = $email = $fail = $success = $error = $mailresult = false;

            $googleId = 'null';
            if ((isset($_POST)) && ($_POST != '')) {
                session_start();
                $_SESSION['postBack'] = [];

                $_SESSION['postBack']['first_name'] = $_POST['firstname'];
                $_SESSION['postBack']['last_name'] = $_POST['surname'];
                $_SESSION['postBack']['username'] = $_POST['username'];
                $_SESSION['postBack']['contact_tel'] = $_POST['phone'];
                $_SESSION['postBack']['email'] = $_POST['email'];

                //They must agree to our terms & conditions
                if (!(isset($_POST['agreeToTerms']))) {
                    header('Location: /userManager/register.php?tc=0');
                    exit();
                }

                //reject spam bots
                if (isset($_POST['captcha_hidden'])) {
                    if (trim($_POST['captcha_hidden']) != '') {
                        header('Location: /userManager/register.php?st=0');
                        exit();
                    }
                }

                if (isset($_POST['captcha_addition']) && ($_POST['captcha_addition'] != 4)) {
                    header('Location: /userManager/register.php?ca=0');
                    exit();
                }

                $user_type = "member";

                $emailverified = "no";

                //sanitize the submitted values
                if (isset($_POST['firstname'])) {
                    $firstname = $this->validator->fix_string($_POST['firstname']);
                }
                if (isset($_POST['surname'])) {
                    $surname = $this->validator->fix_string($_POST['surname']);
                }
                if (isset($_POST['username'])) {
                    $username = $this->validator->fix_string($_POST['username']);
                }
                if (isset($_POST['pwd'])) {
                    $password = $this->validator->fix_string($_POST['pwd']);
                    $retyped = $this->validator->fix_string($_POST['conf_pwd']);
                }

                if (isset($_POST['phone'])) {
                    $phone = $this->validator->fix_string($_POST['phone']);
                }

                if (isset($_POST['email'])) {
                    $email = $this->validator->fix_string($_POST['email']);
                }

                //validate the submitted values
                $fail = $this->validator->validate_firstname($firstname);
                $fail .= $this->validator->validate_surname($surname);
                $fail .= $this->validator->validate_username($username);
                $fail .= $this->validator->validate_password($password);
                $fail .= $this->validator->validate_phonenumber($phone);
                $fail .= $this->validator->validate_email($email);

                if ($fail == "") {
                    $checkPwd = new CheckPassword($password, 6);
                    //IF WE WANT TO MAKE THE PASSWORD STRONGER, WE WILL UNCOMMENT THE FOLLOWING 3 LINES SO THAT THE THE PW WILL ONLY BE
                    //ALLOWED IF IT HAS MIXED LETTER CASES, OR CONTAINS NUMBERS, OR CONTAINS SYMBOLS, OR CONTAINS ALL THE ABOVE, DEPENDING
                    //ON UR CHOICE
                    //$checkPwd->requireMixedCase();
                    //$checkPwd->requireNumbers(2);
                    //$checkPwd->requireSymbols();
                    $passwordOK = $checkPwd->check();
                    if (!$passwordOK) {
                        //$errors = array_merge($errors, $checkPwd->getErrors());
                        //$fail .= array_merge($fail, $checkPwd->getErrors());
                        foreach ($checkPwd->getErrors() as $error) {
                            //$fail .= $fail . $checkPwd->getErrors(); THIS LINE LEAVES THE WORD 'ARRAY' IN THE VARIABLE $fail; To fix
                            //the problem, THIS LOOP WILL EMPTY THE CONTENTS OF THE errors property (which is an array) as loose strings
                            ////into $fail rather than the whole array itself. This is because if the whole array gets it, $fail will
                            //still contain something even though it's empty, and the insertion into the database will not happen, as
                            //$fail has to be empty (free of all errors for that to happen.

                            $fail .= $error;
                        }
                    }
                    if ($password != $retyped) {
                        header('Location: /userManager/register.php?pm=0');
                        exit();
                    }

                    if (!$fail) {
                        $this->user->users_type = $user_type;
                        $this->user->users_username = $username;
                        $this->user->users_email = $email;
                        $this->user->users_pass = $password;
                        $this->user->users_first_name = $firstname;
                        $this->user->users_last_name = $surname;
                        $this->user->users_phone_number = $phone;
                        $this->user->users_emailverified = $emailverified;
                        $this->user->users_created = $this->user->timeNow();
                        $this->user->users_eactivationcode = $activationCode;
                        $saved = $this->user->save();

                        if ($saved == 1062) {
                            header('Location: /userManager/register.php?ee=1');
                            exit();
                        } else if ($saved) {
                            unset($_SESSION['postBack']);

                            // Add your own subject below
                            $subject = "Activate your account";

                            $appName = $this->config->getConfig()['appName'];

                            $message = "<h1>Congratulations</h1>
                                    <h2>Your account has been created on " . $appName . "</h2>
                                    <br />
	                                <p>Click on this link to activate your account</p>
                                    <p><a href='" . $this->config->getConfig()['appURL'] . "classes/adminController.php?verifyEmail=1&em=" . $activationCode . "'>Activate account</a> 
                                    If the above link does not work, copy and paste this in your browser:</p>
                                    <p>" . $this->config->getConfig()['appURL'] . "adminController.php?verifyEmail=1&em=" . $activationCode . "</p>
                                    <br />
                                    
                                    <p><img width='100' height='100' src='" . $this->config->getConfig()['appURL'] . "assets/images/logos/logo.svg' /></p>";

                            $this->messenger->sendEmailActivationEmail($username, $email, $subject, $message);
                            $_SESSION['activationCode'] = $activationCode;

                            header('Location: /userManager/index.php?rs=1');
                            exit();
                        } else {
                            header('Location: /userManager/register.php?rs=0');
                            exit();
                        }
                    } else {
                        header('Location: /userManager/register.php?rs=0');
                        exit();
                    }
                } else {
                    header('Location: /userManager/register.php?rs=0');
                    exit();
                }
            } else {
                header('Location: /userManager/register.php?fe=1');
                exit();
            }
        }
        else
        {
            header('Location: /userManager/index.php?ra=0');
            exit();
        }
    }



    public function checkUsername()
    {
        if (isset($_POST['checkusername']))
        {
            $username = $this->validator->fix_string($_POST['checkusername']);
        }

        $fail = $this->validator->validate_surname($username);

        if ($fail == "")
        {
            {
                $query = "SELECT * FROM users WHERE users_username = '$username'";

                $user = $this->user->query($query);

                if ($user)
                {
                    die("<b style='color:red'>&nbsp;&larr;
			 	        That username is already taken</b>");
                }
                else
                {
                    die("<b  style='color:green'>&nbsp;&larr;
			 		    Username available</b>");
                }
            }
        }
        else
        {
            die("<b  style='color:red'>&nbsp;&larr;
			    Username invalid</b>");
        }
    }



    public function verifyEmail()
    {
        if (isset($_GET['em'])) {
            $user_model = new Users();

            $code = $this->validator->fix_string($_GET['em']);
            $yes = 'yes';

            //we need to get their id here
            $selectCriteria = ["users_eactivationcode" => $code];
            $fields = ['users_id', 'users_first_name', 'users_email'];
            $user = $user_model->selectWhere($fields, $selectCriteria);

            if ($user) {
                $userId = $user[0]['users_id'];

                $user_model->users_emailverified = $yes;
                $user_model->users_eactivationcode = NULL;
                $updateCriteria = ['users_eactivationcode' => $code];
                $updated = $user_model->updateObject($updateCriteria);

                if ($updated) {
                    header('Location: /userManager/login.php?ev=1');
                    exit();
                }
                else
                {
                    header('Location: /userManager/login.php?ev=0');
                    exit();
                }
            }
            else
            {
                header('Location: /userManager/login.php?dt=0');
                exit();
            }
        }
    }
}
