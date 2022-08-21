<?php
ob_start();

require_once('DB_Adapter.php');
require_once('settings.php');
require_once('Validator.php');

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



        public function setValidator($validator)
        {
            $this->_validator = $validator;
        }

        public function getValidator()
        {
            return $this->_validator;
        }



        public function getAllUsers()
        {
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

            $fail = $this->_validator->validate_username($username);

            $fail .= $this->_validator->validate_password($password);

            if ($usertype == '')
            {
                $fail .= 'no usertype given';
            }

            if ($fail == "")
            {

                $data = [
                    'users_type' => $usertype,
                    'users_username' => $username,
                    'users_pass' => $password,
                    'users_created' => $this->timeNow()
                ];

                $saved = $this->insert($data);

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

            if ((isset($userData['user_type'])) && ($userData['user_type'] != ""))
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

            if ($fail == "")
            {
                if ($usertype == '')
                {
                    $data = [
                        'users_username' => $username,
                        'users_pass' => $password,
                    ];
                }
                else {
                    $data =
                        ['users_type' => $usertype,
                            'users_username' => $username,
                            'users_pass' => $password,
                        ];
                }

                $where = ['users_id' => $userId];

                $updated = $this->update($data, $where);

                if ($updated)
                {
                    header('Location: /userManager/dashboard.php?uo=1');
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
    

