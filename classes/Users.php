<?php

namespace classes;

use classes\Model;

if(file_exists("../autoloader.php")) {
    include_once "../autoloader.php";
}
else if (file_exists("autoloader.php"))
{
    include_once "autoloader.php";
}

/**
 * Class Users
 */
class Users extends Model
{
    protected array $_columns = array();

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


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Users
     */
    public function setId(int $id): Users
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getContract(): array
    {
        return $this->contract;
    }

    /**
     * @param array $contract
     * @return Users
     */
    public function setContract(array $contract): Users
    {
        $this->contract = $contract;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Users
     */
    public function setName(string $name): Users
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getContractStart(): string
    {
        return $this->contractStart;
    }

    /**
     * @param string $contractStart
     * @return Users
     */
    public function setContractStart(string $contractStart): Users
    {
        $this->contractStart = $contractStart;
        return $this;
    }

    /**
     * @return string
     */
    public function getContractEnd(): string
    {
        return $this->contractEnd;
    }

    /**
     * @param string $contractEnd
     * @return Users
     */
    public function setContractEnd(string $contractEnd): Users
    {
        $this->contractEnd = $contractEnd;
        return $this;
    }

    /**
     * @param $contractId
     * @return array
     */
    public function getOffers($contractId): array
    {
        $db = $this->connect();
        $offers = new Offers();
        $table = $offers->getTable();

        $stmt = $db->prepare("SELECT * FROM {$table} o LEFT JOIN contract c
            ON o.offers_id = c.contract_offers_id
            WHERE c.contract_id = ?");

        $stmt->bind_param( 'i', $contractId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows) {
            $results = [];
            while ($row = $this->fetchAssocStatement($stmt)) {
                $results[] = $row;
            }

            $stmt->close();

            return $results;
        }
        else {
            return false;
        }
    }


    public function getNameFromId($userId)
    {
        $query = "SELECT users_name FROM users
                      WHERE users_id = $userId";
        $result = $this->query($query);
        if ($result) {
            return $result[0]['users_name'];
        }
        else
        {
            return false;
        }
    }


    public function getAllUsers()
    {
        $key = $this->config->getConfig()['localDBcredentials']['key'];

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

        $key = $this->config->getConfig()['localDBcredentials']['key'];


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
                header('Location: /dashboard.php?uc=1');
                exit();
            }
            else
            {
                header('Location: /createUser.php?uc=0');
                exit();
            }
        }
        else
        {
            header('Location: /createUser.php?uc=er');
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
                header('Location: /dashboard.php?uo=1');
                exit();
            }
        }
        else
        {
            header('Location: /createUser.php?uc=er');
            exit();
        }
    }
}
    

