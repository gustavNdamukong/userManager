<?php
ob_start();

require_once('DB_Adapter.php');
require_once('settings.php');
require_once('Validator.php');

/** ############## Properties and Methods all model classes must have to get the full power of the Dorguzen ###############
 * Must extend the parent model DGZ_DB_ADAPTER

##### PROPERTIES ######################
 * protected $_columns = array();
 * private $_hasParent = array();
 * private $_hasChild = array();

##### CONSTRUCTOR ######################
 * Must call the parent constructor
 * Must call loadORM(), which queries its table, then loops through the results and populates its _columns member array

##### METHODS ######################
 * It has access to all its patent's methods, and you can add yours
 *
 */



    /**
     * Class Users
     */
    class Users extends DB_Adapter
    {

        protected $_columns = array();


        private $_validator = null;




        public function __construct()
        {
            parent::__construct();

            $columns = $this->loadORM($this);
        }





        //---------------------SET/RETRIEVE VALIDATOR---------------

        public function setValidator($validator)
        {
            $this->_validator = $validator;
        }

        public function getValidator()
        {
            return $this->_validator;
        }


        //----------------------






        
        public function authenticateUser($username, $password)
        {
            $connect = $this->connect();

            $salt = $this->getSalt();

            $dataTypes = '';
            $getdataTypes = $this->getColumnDataTypes();
            foreach ($getdataTypes as $dataTypeKey => $getDataType)
            {
                if ($dataTypeKey == 'users_username')
                {
                    $dataTypes .= $getDataType;
                }
                if ($dataTypeKey == 'users_pass')
                {
                    $dataTypes .= $getDataType;
                }
            }

            //add one for the password 'key' value which must be represented but does not exist as a column in the users table
            $dataTypes .= 's';

            $sql = "SELECT * FROM ".$this->getTable()." WHERE users_username = ? AND users_pass = AES_ENCRYPT(?, ?)";

            $stmt = $connect->stmt_init();
            $stmt->prepare($sql);

            // bind parameters and insert the details into the database
            $stmt->bind_param($dataTypes, $username, $password, $salt);
            $stmt->bind_result($custo_id, $type, $username, $pass, $updated, $created); 
            $stmt->execute();
            $stmt->store_result();

            $stmt->fetch();

            if ($stmt->num_rows ) 
            {
                session_start();

                if (!session_id()) { session_start(); } // You should start the session only just bf u start assigning the session variables.
                $_SESSION['authenticated'] = 'Let Go'; //this is the secret keyword (token) u'll check to confirm that a user is logged in.
                // get the time the session started
                $_SESSION['start'] = time();
                session_regenerate_id();


                // This is when you grant them access and let them go by redirecting them to the right quarters of the site
                //store the session variables to be used further on your site if the session variable has been set, then redirect
                if (isset($_SESSION['authenticated']))
                {

                    $_SESSION['custo_id'] = $custo_id;
                    $_SESSION['user_type'] = $type;
                    $_SESSION['username'] = $username;
                    $_SESSION['pass'] = $pass;
                    $_SESSION['created'] = $created;

                    //Now user is logged in, redirect them to their appropriate pages
                    session_write_close();
                }

               return true;
            }
            else
            {
                return false;
            }
        }








        public function getAllUsers()
        {
            $settings = $this->settings;
            $connect = $this->connect();

            $key = $this->settings->getSettings()['DBcredentials']['key'];


            $sql = "SELECT users_id, users_type, users_username, AES_DECRYPT(users_pass, '$key') AS pass, users_created FROM users";
            $users = $this->query($sql);

            if ($users)
            {
                return $users;
            }

        }




        public function getUserById($userId)
        {
            $connect = $this->connect();

            $key = $this->settings->getSettings()['DBcredentials']['key'];


            $sql = "SELECT users_id, users_type, users_username, AES_DECRYPT(users_pass, '$key') AS pass, users_created FROM users WHERE users_id = ".$userId;
            $users = $this->query($sql);

            if ($users)
            {
                return $users;
            }

        }




        public function createUser()
        {
            $fail = false;

            //sanitize the submitted values
            if (isset($_POST['user_type']))
            {
                $usertype = $this->_validator->fix_string($_POST['user_type']);
            }
            if (isset($_POST['username']))
            {
                $username = $this->_validator->fix_string($_POST['username']);
            }
            if (isset($_POST['password']))
            {
                $password= $this->_validator->fix_string($_POST['password']);
            }

            //validate the submitted values
            $fail = $this->_validator->validate_username($username);

            $fail .= $this->_validator->validate_password($password);

            if ($usertype == '')
            {
                $fail .= 'no usertype given';
            }

            if ($fail == "")
            {
                //Get ready to save the new user
                $key = $this->settings->getSettings()['DBcredentials']['key'];

                $table = $this->getTable();

                $data = [
                    'users_type' => $usertype,
                    'users_username' => $username,
                    'users_pass' => $password,
                    'key' => $key,
                    'users_created' => ''
                ];

                $datatypes = '';
                $usersDataClues = $this->getColumnDataTypes();

                //prepare the datatypes for the query (a string is needed)
                foreach ($usersDataClues as $dataClueKey => $columnDatClue)
                {
                    if ($dataClueKey == 'users_type') {
                        $datatypes .= $columnDatClue;
                    }

                    if ($dataClueKey == 'users_username') {
                        $datatypes .= $columnDatClue;
                    }

                    if ($dataClueKey == 'users_pass') {
                        $datatypes .= $columnDatClue;
                    }


                    if ($dataClueKey == 'users_created') {
                        $datatypes .= $columnDatClue;
                    }

                }

                $saved = $this->insert($table, $data, $datatypes);

                if ($saved)
                {
                    header('Location: /userManager/dashboard.php?uc=1');
                    exit();
                }
                else
                {
                    header('Location: /userManager/createUser.php?uc=0');
                    exit();
                }
            }
            else
            {
                header('Location: /userManager/createUser.php?uc=er');
                exit();
            }

        }





        public function editUser ($userData)
        {
            //sanitize the submitted values
            if (isset($userData['userId']))
            {
                $userId = $this->_validator->fix_string($userData['userId']);
            }

            if (isset($userData['user_type']))
            {
                $usertype = $this->_validator->fix_string($userData['user_type']);
            }

            if (isset($userData['username']))
            {
                $username = $this->_validator->fix_string($userData['username']);
            }
            if (isset($userData['password']))
            {
                $password= $this->_validator->fix_string($userData['password']);
            }


            //final cleansing
            $fail = $this->_validator->validate_username($username);
            $fail .= $this->_validator->validate_password($password);

            if ($usertype == '')
            {
                $fail .= 'no usertype given';
            }

            if ($fail == "")
            {
                $key = $this->getSalt();

                $table = $this->getTable();

                $data = [
                            'users_type' => $usertype,
                            'users_username' => $username,
                            'users_pass' => $password,
                            'key' => $key,];

                $dataTypes = '';
                $usersDataTypes = $this->getColumnDataTypes();

                //prepare the datatypes for the query (a string is needed)-We only need those of the columns that are affected by our
                // query (as in the $data array above)-notice we leave $key out of it as it's not a column in our 'users' table
                //we also add an extra string character for the case of 'users_pass' because of its associated salt encryption string
                foreach ($usersDataTypes as $dataClueKey => $columnClue) {
                    if ($dataClueKey == 'users_pass') {
                        $dataTypes .= $columnClue;
                        $dataTypes .= 's';
                    }
                    else
                    {
                        if ($dataClueKey == 'users_type')
                        {
                            $dataTypes .= $columnClue;
                        }
                        if ($dataClueKey == 'users_username')
                        {
                            $dataTypes .= $columnClue;
                        }
                    }
                }

                $where = ['users_id' => $userId];

                //Because we are dealing with an update query, we have to add an extra dataType for every where clause used,
                // this is needed by the placeholders of the mysqli prepared statement
                $dataTypes .= 'i'; //i here obviously represents an integer for the user ID

                $updated = $this->update($table, $data, $dataTypes, $where);

                if ($updated)
                {
                    header('Location: /userManager/createUser.php?uo=1');
                    exit();
                }
            }
            else
            {
                header('Location: /userManager/createUser.php?uc=er');
                exit();
            }

        }




        
    }
    

