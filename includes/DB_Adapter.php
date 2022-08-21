<?php
require_once('settings.php');


 class DB_Adapter
 {

     protected $salt = '';

     protected $whoCalledMe = '';

     protected $settings;

     private $passwordField = [
         'password',
         'pwd',
         'user_pwd',
         'users_pwd',
         'user_password',
         'users_password',
         'user_pass',
         'users_pass'
     ];
     

     
     /**
     * All models extend from this class (parent) and share its awesome methods. For it to be of use to them, it needs to 
     * know which model called it. Any method here when called by a child object MUST first of all get & instantiate the 
     * caller, so that it can hence reference the methods here in the context of the caller. For example:
     *
     *  $model = new $this->whoCalledMe;
     */
    public function __construct()
    {
        $classThatCalled = get_class($this);

        $this->whoCalledMe = $classThatCalled;

        $settingsClass = new Settings();

        $this->settings = $settingsClass;

        //get DB connection credentials
        $credentials = $this->settings->getSettings()['DBcredentials'];

        $this->salt = $credentials['key'];
    }

     
     
    protected function connect()
    {
        $credentials = $this->settings->getSettings()['DBcredentials'];
        $conn = new mysqli($credentials['host'], $credentials['username'], $credentials['pwd'], $credentials['db']);

        if ($conn->connect_error)
        {
            die('cannot open database');
        }

        return $conn;
    }

     
     public function getSalt()
     {
         $salt = (string) $this->salt;

         return $salt;
     }
     
     

    /**
     * This method is called ONLY by models at run time to map to their tables & initialize
     * vital settings
     */
     public function loadORM($model)
     {
         $table = $model->getTable();
         $db = $this->connect();

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

             $columns = $results;

             if (is_array($columns)) {
                 foreach ($columns as $column) {
                     if (preg_match('/int/', $column['Type'])) {
                         $val = 'i';
                     }
                     if (preg_match('/varchar/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/text/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/timestamp/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/enum/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/blob/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/decimal/', $column['Type'])) {
                         $val = 'd';
                     }
                     if (preg_match('/date/', $column['Type'])) {
                         $val = 's';
                     }
                     if (preg_match('/float/', $column['Type'])) {
                         $val = 'd';
                     }

                     $model->_columns[$column['Field']] = $val;
                 }
             }
         }
         else {
             //check result if Updating/inserting/deleting
             if ((isset($result->affected_rows)) && ($result->affected_rows > 0)) {
                 return true;
             }
         }

         return false;

     }

     
     
     public function __set($member, $value)
     {
         if (array_key_exists($member, $this->_columns)) {
             $this->$member = $value;
         }
     }

     
     
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
         return lcfirst(get_class($this));
     }

     

     public function escapeString4DB($string)
     {
         $db = $this->connect();
         return $db->real_escape_string($string);
     }


     
     public function save()
     {
         $model = new $this->whoCalledMe;

         // Connect to the database
         $db = $this->connect();

         $table = $model->getTable();

         //prepare the data to make up the query
         $data = array();
         $datatypes = '';

         foreach (get_object_vars($this) as $property => $value) {
             //filter values
             if (array_key_exists($property, $model->_columns)) {
                 $data[$property] = $value;
                 if (in_array($property, $this->passwordField)) {
                     //add saltkey & salt datatype xters
                     $data['key'] = $this->getSalt();
                     $datatypes .= 'ss';
                 }
                 else {
                     //set the field datatype
                     $datatypes .= $model->_columns[$property];
                 }
             }
         }

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data);

         array_unshift($values, $datatypes);

         $stmt = $db->stmt_init();

         $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");

         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

         // Check for successful insertion
         if ( $stmt->affected_rows == 1)
         {
             return $stmt->insert_id;
         }
         elseif ( (isset($stmt->errno)) && ($stmt->errno == 1062))
         {
             return '1062';
         }
         else
         {
             return false;
         }
     }



     /**
      * This method takes a 'where' clause array of 'fieldName' => 'value' pairs.
      *
      * @example:
      *         $products = new Products();
      *         $products->products_authorized = 'yes';
      *         $products->products_authorized_date = date("Y-m-d H:i:s");
      *         $products->products_authorized_by = $authorizerId;
      *
      *         $where = ['products_id' => $adId];
      *
      *         $updated = $products->updateObject($where);
      *
      * @param $where
      * @return bool|string
      */
     public function updateObject($where)
     {
         $model = new $this->whoCalledMe;
         $table = $model->getTable();

         $data = array();
         $newData = [];
         $dataTypes = '';

         foreach (get_object_vars($this) as $property => $value) {
             if (array_key_exists($property, $model->_columns)) {
                 $newData[$property] = $value;
                 if (in_array($property, $this->passwordField)) {
                     $newData['key'] = $this->getSalt();
                     $dataTypes .= 'ss';
                 }
                 else {
                     $dataTypes .= $model->_columns[$property];
                 }
             }
         }

         foreach ($where as $field => $val)
         {
             if (array_key_exists($field, $model->_columns)) {
                 $dataTypes .= $model->_columns[$field];
             }
         }

         $db = $this->connect();
         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($newData, 'update');

         $where_clause = '';
         $where_values = [];
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

         array_unshift($values, $dataTypes);
         $values = array_merge($values, $where_values);

         $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");

         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

         if ( $stmt->affected_rows ) {
             return true;
         }

         return false;

     }



     /**
      * delete based on any criteria desired
      *
      * this method prepares the args ($table, $where criteria, and $dataTypes) before passing these args to delete()
      *
      * @param array $criteria the criteria to delete records based on. For example, if we are deleting an album, $criteria will contain
      *   something like ['albums_name' => 'Birthday']
      *
      * @return string
      */
     public function deleteWhere($criteria = array())
     {
         foreach ($criteria as $key => $crits)
         {
             $datatypes = '';
             $where = array();
             //Confirm that field exists in DB table
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



     /**
      *query DB without a prepared stmt
      *
      * @param $query just pass it your SQL query string
      * @return returns true if you are updating of deleting, it returns the last inserted ID if you are inserting, 
      *     returns the result set if you are selecting, returns false if the operation fails.
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
      * Call this function like so:
      *
      *      $blog2cat = new Article2cat();
      *
      *      $blogPost = [
      *          'blog_id' => $_POST['blog_id'],
      *          'blog_cats_id' => $cat_id,
      *      ];
      *
      *      $blog2cat->insert($blogPost);
      *
      * @param $data
      * @return bool|int|string
      */
     public function insert($data)
     {
         $model = new $this->whoCalledMe;
         $db = $this->connect();
         $table = $model->getTable();

         $datatypes = '';
         $dataClean = [];

         foreach ($data as $key => $value) {
             //filter the values
             if (array_key_exists($key, $model->_columns)) {
                 $dataClean[$key] = $value;
                 if (in_array($key, $this->passwordField)) {
                     $dataClean['key'] = $this->getSalt();
                     $datatypes .= 'ss';
                 }
                 else {
                     $datatypes .= $model->_columns[$key];
                 }
             }
         }

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($dataClean);

         array_unshift($values, $datatypes);


         $stmt = $db->stmt_init();

         // Prepare our query for binding
         $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");

         // bind the values
         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

         if ( $stmt->affected_rows == 1)
         {
             return $stmt->insert_id;
         }
         elseif ( (isset($stmt->errno)) && ($stmt->errno == 1062))
         {
             //duplicate entry
             return '1062';
         }
         else
         {
             return false;
         }
     }



     /**
      * Update a record in the DB
      *
      * Prepare to call it like so:
      * $data = ['blog_title' => $_POST['title'],
      *     'blog_article' => $_POST['article'],
      *  ];
      *
      * $where = ['blog_id' => $blog_id];
     $updated = $blog->update($data, $where);
      *
      * @param array $data an array of 'fieldName' => 'value' pairs for the DB table fields to be updated
      * @param array $where. An array of 'key' - 'value' pairs which will be used to build the 'WHERE' clause
      * @return bool
      */
     public function update($data, $where)
     {
         $model = new $this->whoCalledMe;
         $table = $model->getTable();

         $data = (array) $data;
         $newData = [];

         $dataTypes = '';
         $tableDataClues = $model->getColumnDataTypes();

         foreach ($data as $key => $value) {
             if (array_key_exists($key, $model->_columns)) {
                 $newData[$key] = $value;
                 if (in_array($key, $this->passwordField)) {
                     $newData['key'] = $this->getSalt();
                     $dataTypes .= 'ss';
                 }
                 else {
                     $dataTypes .= $model->_columns[$key];
                 }
             }
         }

         foreach ($where as $criteriaKey => $criteria)
         {
             foreach ($tableDataClues as $dataClueKey => $columnDatClue) {
                 if ($dataClueKey == $criteriaKey) {
                     $dataTypes .= $columnDatClue;
                 }
             }
         }

         $db = $this->connect();

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($newData, 'update');

         //build where clause
         $where_clause = '';
         $where_values = [];
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

         array_unshift($values, $dataTypes);
         $values = array_merge($values, $where_values);

         $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");

         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

         if ( $stmt->affected_rows ) {
             return true;
         }

         return false;
     }



     /**
      * You wouldn't typically call this method directly, but rather a method in your model will prepare the args for
      * this method, then call it.
      * @return Bool true or false for whether the deletion was successful or not
      */
     public function delete($table, $where = array(), $dataTypes = '')
     {
         $db = $this->connect();


         if (empty($where)) {
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
             $where = (array) $where;
             $dataTypes = (string) $dataTypes;

             //Build the where clause
             $where_placeholders = '';
             $where_values = [];
             $count = 0;

             foreach ($where as $field => $value) {
                 if ($count > 0) {
                     $where_placeholders .= ' AND ';
                 }

                 $where_placeholders .= $field . '=?';
                 $where_values[] = $value;

                 $count++;
             }

             array_unshift($where_values, $dataTypes);

             $stmt = $db->prepare("DELETE FROM {$table} WHERE {$where_placeholders}");

             call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($where_values));

             $stmt->execute();

             if ($stmt->affected_rows) {
                 return true;
             }

             return true;
         }

     }



    /**
     * Builds the query strings from the data (e.g. arrays) given
     *
     */
    private function insert_update_prep_query($data, $type = 'insert')
    {
        $fields = '';
        $placeholders = '';
        $values = array();

        foreach ( $data as $field => $value )
        {
            if ($field == 'key')
            {
                $values[] = $value;
                continue;
            }

            $fields .= "{$field},";
            $values[] = $value;

            if ($type == 'update')
            {
                if (in_array($field, $this->passwordField))
                {
                    $placeholders .= $field ." = AES_ENCRYPT(?, ?),";
                }
                else {
                    $placeholders .= $field . '=?,';
                }
            }
            elseif (in_array($field, $this->passwordField))
            {
                $placeholders .= " AES_ENCRYPT(?, ?),";
            }
            else
            {
                $placeholders .= '?,';
            }
        }

        //remove blank elements from the values array - this is very important
        $values = array_filter($values);

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



     /**
      * Alternative to fetch_assoc() method of the myslqli object which requires the mysqlnd driver to be installed on your webspace.
      * If it is not, you will have to work with BIND_RESULT() & FETCH() methods. It uses fetch() in the background and returns you
      * the array that you are used to having with $stmt->fetch_assoc(). Call this method passing it your $stmt.  Since it uses
      * $stmt->fetch() internally, you can call it just as you would call mysqli_result::fetch_assoc
      * (just be sure that before you call this method the $stmt object is still open (not closed yet) and the result of your DB query
      * is already stored using $stmt->store_result())
      *
      * @param $stmt
      * @return array|null
      */
     private function fetchAssocStatement($stmt)
     {
         if($stmt->num_rows>0)
         {
             $result = array();
             $md = $stmt->result_metadata();
             $params = array();
             while($field = $md->fetch_field()) {
                 $params[] = &$result[$field->name];
             }
             call_user_func_array(array($stmt, 'bind_result'), $params);
             if($stmt->fetch())
                 return $result;
         }

         return null;
     }



     /**
      * @param $data array containing the username & password to authenticate the user with
      * @return array|bool It returns false if the login fails, or an array of all fields in your users table
      */
     public function authenticateUser($data)
     {
         $model = new $this->whoCalledMe;
         $tableColumns = $model->_columns;

         $connect = $this->connect();
         $dataTypes = '';
         $usernameField = '';
         $usernameValue = '';
         $passwordField = '';
         $passwordValue = '';
         $salt = '';

         foreach ($data as $key => $value) {
             if (array_key_exists($key, $tableColumns)) {
                 if (in_array($key, $this->passwordField)) {
                     $passwordField = $key;
                     $passwordValue = (string) $value;
                     $salt = (string) $this->getSalt();
                     $dataTypes .= 'ss';
                 }
                 else {
                     $usernameField = $key;
                     $usernameValue = (string) $value;
                     $dataTypes .= $tableColumns[$key];
                 }
             }
         }

         $sql = "SELECT * FROM ".$this->getTable()." 
            WHERE ".$usernameField." = ? 
            AND ".$passwordField." = AES_ENCRYPT(?, ?)";

         $stmt = $connect->stmt_init();
         $stmt->prepare($sql);

         $stmt->bind_param($dataTypes, $usernameValue, $passwordValue, $salt);

         $stmt->execute();

         $stmt->store_result();

         if ($stmt->num_rows)
         {
             $row = $this->fetchAssocStatement($stmt);

             $stmt->close();
             return $row;
         }
         else
         {
             return false;
         }
     }



     public function timeNow()
     {
         return date("Y-m-d:H:i:s");
     }
  }


    
 