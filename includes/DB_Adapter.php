<?php
require_once('settings.php');


 class DB_Adapter
 {
     protected $settings;


     protected $host = '';


     protected $username = '';


     protected $pwd = '';

     
     protected $db = '';
     
     
     protected $salt = '';
     
     
     protected $connectionType = '';


     protected $whoCalledMe = '';



    /**
     * All models extend from this class and share its awesome methods. For it to be of use to them, it needs to know which model called it
     *  Then any method in this class (parent) that is shared by all models MUST first of all get n instantiate the active model before carrying on so
     *  that they can call their own members in things that relate specifically to them whilst operating inside of this (parent) class like so:
     *  $model = new $this->whoCalledMe;
     */
    public function __construct()
    {
        $classThatCalled = get_class($this);
        $this->whoCalledMe = $classThatCalled;

        $settingsClass = new settings();

        $this->settings = $settingsClass;

        //get DB connection credentials
        $credentials = $this->settings->getSettings()['DBcredentials'];

        $this->username = $credentials['username'];
        $this->pwd = $credentials['pwd'];
        $this->db = $credentials['db'];
        $this->host = $credentials['host'];
        $this->connectionType = $credentials['connectionType'];
        $this->salt = $credentials['key'];

    }







    protected function connect()
    {

        if ($this->connectionType  == 'mysqli')
        {
            $conn = new mysqli($this->host, $this->username, $this->pwd, $this->db);

            if ($conn->connect_error)
            {
                die('cannot open database');
            }


            return $conn;
        }
        elseif ($this->connectionType  == 'pdo')
        {
            try
            {
                return new PDO("mysql:host=$this->host;dbname=$this->db", $this->username, $this->pwd);
            }
            catch (PDOException $e)
            {
                echo 'Cannot connect to database';
                exit;
            }
        }
    }








    /**
     * This method is called ONLY by models at run time to map to their tables n initialize
     * vital settings
     */
     public function loadORM($model)
     {
         $table = $model->getTable();
         $db = $this->connect();

         //build the map of the table columns and datatypes. Note we have created before hand an private member called 'columns' wh will hold column names n datatypes
         //only your model class will write to n read from this member
         $query = 'DESCRIBE '.strtolower($table);

         $result = $db->query($query);

         //check result if SELECTING
         if ((isset($result->num_rows)) && ($result->num_rows > 0))
         {
             $results = array();
             while ($row = $result->fetch_assoc())
             {
                 $results[] = $row;
             }

             return $results;
         }
         else
         //check result if Updating/inserting/deleting
         if ((isset($result->affected_rows)) && ($result->affected_rows > 0))
         {
             return true;
         }

         return false;
     }









     /**
      *
      *query DB without a prepared stmt
      *
      * Works just fine
      */
     public function query($query)
     {
         $db = $this->connect();

         $res = $db->query($query);

         //check result if SELECTING
         if ((isset($res->num_rows)) && ($res->num_rows > 0))
         {
             $results = array();
             while ($row = $res->fetch_assoc())
             {
                 $results[] = $row;
             }

             return $results;
         }


         //check result if INSERTING/UPDATING/DELETING
         if ((isset($db->affected_rows)) && ($db->affected_rows > 0))
         {
             //if we are inserting, return the last insert ID
             if ((isset($db->insert_id)) && ($db->insert_id != 0)) {
                 return $db->insert_id;
             }
             else
             {
                 //we either deleted or updated
                 return true;
             }
         }

         return false;
     }







     /**
     * @param $table
     * @param $data
     * @param $dataTypes
     * @return bool|int|string
     */
     public function insert($table, $data, $dataTypes)
     {
         // Check for $table or $data not set
         if ( empty( $table ) || empty( $data ) ) {
             return false;
         }

         // Connect to the database
         $db = $this->connect();

         // Cast $data to an array
         $data = (array) $data;


         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data);

         // Prepend the $dataTypes string onto the $values array (The bind_param() meth needs it like this-1st param is string of datatype xters to rep the fields,
         // followed by as many params (vars) as there are values to rep the placeholders (? xters))
         array_unshift($values, $dataTypes);


         $stmt = $db->stmt_init();

         // Prepare our query for binding
         $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");


         // Dynamically bind values
         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         // Execute the query
         $stmt->execute();

         // Check for successful insertion
         if ( $stmt->affected_rows == 1)
         {
             //return true;
             return $stmt->insert_id;
         }
         elseif ( (isset($stmt->errno)) && ($stmt->errno == 1062))
         {
             return '1062';
         }
     }







     /**
      *
      * @param string $table the table to update in
      * @param array $data a ready-made array of 'fieldName' => 'value' elements
      * @param string $dataTypes a string of datatype characters to match the prepared statement placeholders this query needs
      * @param array $where. An also ready-made array of 'fieldName' => 'value' which will be used for the 'WHERE' 'fieldName' = 'value' clause
      *     Note very well that you should add one more character type to the $dataTypes string for each element in the 'where' clause, as this method will use prepared statements for each one,
      *     otherwise the DB query will not work. Also, make sure the data type character you pass in matches the data type of the field the 'WHERE' clause is referring to.
      *
      * @return bool
      */
     public function update($table, $data, $dataTypes, $where)
     {
         // Check for $table or $data not set
         if (empty( $table ) || empty($data)) {
             return false;
         }

         // Connect to the database
         $db = $this->connect();

         // Cast $data and $format to arrays
         $data = (array) $data;

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data, 'update');

         //Format where clause
         $where_clause = '';
         $where_values = '';
         $count = 0;

         foreach ( $where as $field => $value )
         {
             if ( $count > 0 ) {
                 $where_clause .= ' AND ';
             }

             $where_clause .= $field . '=?';
             $where_values[] = $value;

             $count++;
         }


         // Prepend $format onto $values
         array_unshift($values, $dataTypes);
         $values = array_merge($values, $where_values);

         $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");

         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         // Execute the query
         $stmt->execute();

         // Check for successful insertion
         if ( $stmt->affected_rows ) {
             return true;
         }

         return false;
     }















     public function delete($table, $where = array(), $dataTypes = '')
     {
         // Connect to the database
         $db = $this->connect();


         if (empty($where)) {
             //They haven't specified a column, so we'll just delete everything
             $sql = $db->prepare("DELETE FROM {$table}");

             $result = $this->query($sql);

             if ($result) {
                 return true;
             }
             else {
                 return false;
             }
         }
         elseif (!empty($where)) {

             // Cast all data to arrays
             $where = (array) $where;
             $dataTypes = (string) $dataTypes;

             //Format where clause
             $where_placeholders = '';
             $where_values = '';
             $count = 0;

             foreach ($where as $field => $value) {
                 if ($count > 0) {
                     $where_placeholders .= ' AND ';
                 }

                 $where_placeholders .= $field . '=?';
                 $where_values[] = $value;

                 $count++;
             }


             // Prepend $format onto $values
             array_unshift($where_values, $dataTypes);

             $stmt = $db->prepare("DELETE FROM {$table} WHERE {$where_placeholders}");

             call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($where_values));

             // Execute the query
             $stmt->execute();

             // Check for successful deletion
             if ($stmt->affected_rows) {
                 return true;
             }
             //if there was no record in the DB no msg will be returned,
             // so we put another return line here below
             return true;
         }

     }







        


    /**
     * Builds the query strings from the data (e.g. arrays) given
     *
     */
    private function insert_update_prep_query($data, $type = 'insert')
    {
        // Instantiate $fields and $placeholders for looping
        $fields = '';
        $placeholders = '';
        $values = array();

        // Loop through $data and build $fields, $placeholders, and $values
        foreach ( $data as $field => $value )
        {
            //added this to stop 'key' from being inserted as a table field, which is wrong
            if ($field == 'key')
            {
                //coz salt (the key) still needs to be bound to the values
                $values[] = $value;
                continue;
            }

            $fields .= "{$field},";
            $values[] = $value;

            if ( $type == 'update')
            {
                if ($field == 'users_pass')
                {
                    $placeholders .= $field ." = AES_ENCRYPT(?, ?),";
                }
                else
                {
                    $placeholders .= $field . '=?,';
                }
            }
            elseif ($field == 'users_pass')
            {
                $placeholders .= "AES_ENCRYPT(?, ?),";
            }
            elseif ($field == 'users_created')
            {
                $placeholders .= "NOW(),";
            }
            else
            {
               $placeholders .= '?,';
            }
        }

        //remove blank elements from the values array - this is very important
        $values = array_filter($values);

        // Normalize $fields and $placeholders for inserting
        $fields = substr($fields, 0, -1);
        $placeholders = substr($placeholders, 0, -1);


        return array( $fields, $placeholders, $values );
    }
        





        

    /**
     * Creates an optimized array to be used by bind_param() to bin
     * values to the query placeholders
     *
     * Works fine
     */
    private function ref_values($array)
    {
        $refs = array();
        foreach ($array as $key => $value) {
                $refs[$key] = &$array[$key];
        }
        return $refs;
    }





  }


    
 