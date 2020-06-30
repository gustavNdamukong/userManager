<?php
ob_start();

require_once('DB_Adapter.php');
require_once('settings.php');
require_once('Validator.php');


//if (isset($_POST['createUser'])) {
 //   $user = new Users;
 //   $user->createUser($_POST);
//}


    /** ############## Properties and Methods all model classes must have to get the full power of the Dorguzen ###############
     * Must extend DB_ADAPTER

    ##### PROPERTIES ######################
     * private $_columns = array();
     * private $_hasParent = array();
     * private $_hasChild = array();

    ##### CONSTRUCTOR ######################
     * Must call the parent constructor
     * Must call loadORM(), then loop through its results and populate its _columns array property

    ##### METHODS ######################
     * getColumnDataTypes()
     * __set($member, $value)
     * __get($member)
     * getTable()
     * save()
     * updateObject($where)
     * deleteWhere()
     *
     */



    /**
     * Class Users
     */
    class Users extends DB_Adapter
    {

        private $_columns = array();


        private $_adapter = null;


        private $_validator = null;




        public function __construct()
        {
            parent::__construct();

            //build the map of the table columns and datatypes. Note we have created before hand a private member called '_columns' wh will hold column names n datatypes
            //only your model class will write to n read from this member
            $columns = $this->loadORM($this);
            //echo '<pre>'; print_r($columns); die();//////////////

            if (is_array($columns)) {
                foreach ($columns as $column) {
                    if (preg_match('/int/', $column['Type'])) {
                        $val = 'i';
                    }
                    if (preg_match('/varchar/', $column['Type'])) {
                        $val = 's';
                    }
                    if (preg_match('/enum/', $column['Type'])) {
                        $val = 's';
                    }
                    if (preg_match('/text/', $column['Type'])) {
                        $val = 's';
                    }
                    if (preg_match('/blob/', $column['Type'])) {
                        $val = 's';
                    }
                    if (preg_match('/timestamp/', $column['Type'])) {
                        $val = 's';
                    }

                    $this->_columns[$column['Field']] = $val;
                }
            }
        }






        public function __set($member, $value)
        {
            if (array_key_exists($member, $this->_columns)) {
                $this->$member = $value;
            }
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







        /**
         * This member being retrieved must have been created already using __set() above
         */
        public function __get($member)
        {
            if (array_key_exists($member, $this->_columns)) {
                return $this->$member;
            }
        }








        public function getColumnDataTypes()
        {
            return $this->_columns;
        }








        public function getTable()
        {
            return strtolower(get_class($this));
        }



        


        //This is the only model that uses this method, as it's specific to user authentication
        public function getSalt()
        {
            $salt = (string) $this->salt;

            return $salt;
        }



        
        
        public function get_dataTypes($purpose)
        {
            //purposes are:
            // regis
            // login
            //create_user
            return $this->{$purpose.'DataTypes'};
            
        }


        
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
            /////$dataTypes = $this->get_dataTypes('login');
            //die($dataTypes);///////////////////////////////////////

            $sql = "SELECT * FROM ".$this->getTable()." WHERE users_username = ? AND users_pass = AES_ENCRYPT(?, ?)";
            //die($sql);///////////////////////////////////////

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

                    //echo '<pre>';
                    //die(print_r($_SESSION));
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
            /////$settings = new settings();
            $settings = $this->settings;
            $connect = $this->connect();

            $key = $this->settings->getSettings()['DBcredentials']['key'];
            /////$key = $settings->getSettings()['DBcredentials']['key'];


            $sql = "SELECT users_id, users_type, users_username, AES_DECRYPT(users_pass, '$key') AS pass, users_created FROM users";
            $users = $this->query($sql);

            if ($users)
            {
                return $users;
            }

        }


        public function getUserById($userId)
        {
            /////$settings = new settings();
            $connect = $this->connect();

            /////$key = $settings->getSettings()['DBcredentials']['key'];
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
            //echo '<pre>'.' from CREATEUSER()';
            //var_dump($_REQUEST); die();
            /////$val = new Validator();
            /////$settings = new settings();
            $fail = false;

            //sanitize the submitted values
            if (isset($_POST['user_type']))
            {
                /////$usertype = $val->fix_string($_POST['user_type']);
                $usertype = $this->_validator->fix_string($_POST['user_type']);
            }
            if (isset($_POST['username']))
            {
                /////$username = $val->fix_string($_POST['username']);
                $username = $this->_validator->fix_string($_POST['username']);
            }
            if (isset($_POST['password']))
            {
                /////$password= $val->fix_string($_POST['password']);
                $password= $this->_validator->fix_string($_POST['password']);
            }




            //validate the submitted values
            /////$fail = $val->validate_username($username);
            $fail = $this->_validator->validate_username($username);
            /////$fail .= $val->validate_password($password);
            $fail .= $this->_validator->validate_password($password);

            if ($usertype == '')
            {
                $fail .= 'no usertype given';
            }

            if ($fail == "")
            {
                //Get ready to save the new user
                /////$key = $settings->getSettings()['DBcredentials']['key'];
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

                //echo '<pre>'; var_dump($usersDataClues); die();//////////////////////

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
            //echo '<pre>'; die(print_r($userData)); ///////////
            //$val = new Validator();

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












        public function updateObject($where)
        {
            //prepare the data to make up the query
            $data = array();
            $datatypes = array();


            foreach (get_object_vars($this) as $property => $value) {
                //filter out any properties that are not in ur columns array
                if (array_key_exists($property, $this->_columns)) {
                    //set the field n value
                    $data[$property] = $value;

                    //set the field datatype
                    array_push($datatypes, $this->_columns[$property]);
                }

            }

            //The 'Where' clause also needs to have its own matched datatypes separately from the data itself, so let's work it out
            //-----------------------------------------------------------
            foreach ($where as $field => $val)
            {
                if (array_key_exists($field, $this->_columns)) {
                    //add to the field datatypes
                    array_push($datatypes, $this->_columns[$field]);
                }
            }
            //------------------------------------------------------------

            //Convert datatypes into a string
            $datatypes = implode($datatypes);


            //get this model's tablename
            $table = $this->getTable();

            /////echo '<pre>'; die(print_r($data)); ///////////

            //do the update
            $updated = $this->update($table, $data, $datatypes, $where);
            //if ($saved == 'saved') { (we have changed it to return true as below)
            if ($updated) {
                return $updated;
                //if ($saved == 'saved') {
                //return 'saved';
            }
            elseif ($updated == 1062) {
                return 'duplicate';
            }
            else {
                return 'failed';
            }

        }







        /**
         * delete based on any criteria desired
         *
         * this method prepares the args ($table, $where criteria, and $dataTypes) before passing these args to delete()
         *
         * @param array $criteria which is the criteria to delete reocords in this model based on. For example, if we are deleting an album, $criteria will contain
         *   something like ['albums_name' => 'Birthday']
         *
         * @return string
         */
        public function deleteWhere($criteria = array())
        {
            //echo '<pre>'; var_dump($criteria); die('FROM deleteWhere()');//////
            foreach ($criteria as $key => $crits)
            {
                $datatypes = '';
                $where = array();
                //securely check that that field exists n DB table
                if (!array_key_exists($key, $this->_columns)) {
                    return 'The field ' . $key . ' does not exist in the ' . strtolower($this->getTable() . ' table');
                }
                else {
                    $where[$key] = $crits;
                    $datatypes .= $this->_columns[$key];
                }
            }

            $table = $this->getTable();

            $deleted = $this->delete($table, $where, $datatypes);

            if ($deleted)
            {
                header('Location: /userManager/dashboard.php?del=1');
                exit();
            }
        }


        
    }
    

