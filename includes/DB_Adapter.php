<?php
require_once('settings.php');


 class DB_Adapter
 {

     protected $salt = '';

     protected $whoCalledMe = '';

     protected $settings;


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
     * This method is called ONLY by models at run time to map to their tables n initialize
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





     /**
      * It it recommended to assign values to all fields on a model class after having initialized them with NULLs
      * to avoid errors of number of parameters provided not matching the number of fields on the table.
      * Obviously, make sure those NULL fields can actually accept a NULL in the DB
      *
      * @return bool|string
      */
     public function save()
     {
         $model = new $this->whoCalledMe;
         $table = $model->getTable();

         $data = array();
         $datatypes = array();

         foreach (get_object_vars($this) as $property => $value) {
             //filter out any properties that are not in ur columns array
             if (array_key_exists($property, $model->_columns)) {
                 //set the field n value
                 $data[$property] = $value;

                 //set the field datatype
                 array_push($datatypes, $model->_columns[$property]);
             }

         }

         //Convert datatypes into a string
         $datatypes = implode($datatypes);

         // Connect to the database
         $db = $this->connect();
         $key = $this->getSalt();

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data);

         array_unshift($values, $datatypes);

         $stmt = $db->stmt_init();

         $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");


         //Bind values
         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

         if ( $stmt->affected_rows == 1)
         {
             return $stmt->insert_id;
         }
         elseif ( (isset($stmt->errno)) && ($stmt->errno == 1062))
         {
             return 'duplicate';
         }
         else
         {
             return false;
         }
     }







     /**
      * Instead of preparing all the data needed to be passed to the update()
      * method ($table, $data, $datatypes, $whereClause), updateObject just takes
      * a 'where' clause array of 'fieldName' => 'value' pairs and does all the rest for you.
      *
      * @example:
      *         $products->products_authorized = 'yes';
      *         $products->products_authorized_date = date("Y-m-d H:i:s");
      *         $products->products_authorized_by = $authorizerId;
      *         $where = ['products_id' => $adId];
      *
      *         $updated = $products->updateObject($where);
      *
      *
      * @param $where array of 'field name' => 'criteria value'
      * @return bool|string
      */
     public function updateObject($where)
     {
         $model = new $this->whoCalledMe;
         $table = $model->getTable();

         //prepare the data to make up the query
         $data = array();
         $dataTypes = array();


         foreach (get_object_vars($this) as $property => $value) {
             //filter out any properties that are not in ur columns array
             if (array_key_exists($property, $model->_columns)) {
                 //set the field n value
                 if ($property == 'users_pass')
                 {
                     //store the 2 pieces of data needed for passwords ('users_pass' and 'key')
                     $key = $this->getSalt();
                     $data[$property] = $value;
                     $data['key'] = $key;

                     array_push($dataTypes, $model->_columns[$property]);
                     //we add an extra string character for the case of 'users_pass' coz of its associated salt encryption string
                     array_push($dataTypes, 's');
                 }
                 else {
                     $data[$property] = $value;

                     //set field datatype
                     array_push($dataTypes, $model->_columns[$property]);
                 }
             }

         }

         //The 'Where' clause also needs to have its own matched datatypes separately from the data
         // for the placeholders of the mysqli prepared statement
         foreach ($where as $field => $val)
         {
             if (array_key_exists($field, $model->_columns)) {
                 array_push($dataTypes, $model->_columns[$field]);
             }
         }

         //Convert datatypes to string
         $datatypes = implode($dataTypes);

         // Connect to the database
         $db = $this->connect();

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data, 'update');

         //Format where clause
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

         // Prepend $format onto $values
         array_unshift($values, $datatypes);
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
      * @param array $criteria which is the criteria to delete reocords in this model based on. For example, if we are deleting an album, $criteria will contain
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
         $table = $model->getTable();

         $db = $this->connect();
         $key = $this->getSalt();

         $data = (array) $data;

         $dataTypes = '';
         $usersDataClues = $model->getColumnDataTypes();

         foreach ($data as $dataKey => $dat) {
             foreach ($usersDataClues as $dataClueKey => $columnDatClue) {
                 if ($dataClueKey == $dataKey) {
                     $dataTypes .= $columnDatClue;
                     if ($dataKey == 'users_pass')
                     {
                         //additional parameters for the password field
                         $data['key'] = $key;
                     }
                 }
             }
         }

         list( $fields, $placeholders, $values ) = $this->insert_update_prep_query($data);

         array_unshift($values, $dataTypes);


         $stmt = $db->stmt_init();

         $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");


         // Bind values
         call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

         $stmt->execute();

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
      * Update a record in the DB
      *
      * Prepare to call it like so:
      * @example
      *     $data = ['blog_title' => $_POST['title'],
      *     'blog_article' => $_POST['article'],
      *     ];
      *
      * $where = ['blog_id' => $blog_id];
     $updated = $blog->update($data, $where);
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
     public function update($data, $where)
     {
         $model = new $this->whoCalledMe;
         $table = $model->getTable();

         // Cast $data to an array
         $data = (array) $data;
         $newData = [];

         $dataTypes = '';
         $tableDataClues = $model->getColumnDataTypes();

         foreach ($data as $dataKey => $dat) {
             foreach ($tableDataClues as $dataClueKey => $columnDatClue) {
                 if ($dataClueKey == $dataKey) {
                     $dataTypes .= $columnDatClue;

                     //move the data into the new array because we need to maintain the order as passed in by the developer
                     $newData[$dataKey] = $dat;
                     if ($dataClueKey == 'users_pass')
                     {
                         $key = $this->getSalt();
                         //additional parameters for the password field
                         $newData['key'] = $key;
                         $dataTypes .= 's';
                     }
                 }
             }
         }

         //prepare the datatype string for the where clause too
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

         //Format where clause
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















     public function delete($table, $where = array(), $dataTypes = '')
     {
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
             $where = (array) $where;
             $dataTypes = (string) $dataTypes;

             //Format where clause
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

             // Prepend $format onto $values
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
                //salt (the key) still needs to be bound to the values
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


    
 