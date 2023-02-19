<?php

namespace classes;

if(file_exists("../autoloader.php")) {
    include_once "../autoloader.php";
}
else if (file_exists("autoloader.php"))
{
    include_once "autoloader.php";
}

use config\Config;
use mysqli;
use Exception;

#[AllowDynamicProperties]
class Model
{
    protected $config;

    protected $salt = '';

    protected $whoCalledMe = '';

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



    public function __construct()
    {
        $this->whoCalledMe = get_class($this);
        $this->config = new Config();

        //get DB connection credentials
        $credentials = $this->config->getConfig()['localDBcredentials'];
        $this->salt = $credentials['key'];
    }



    protected function connect()
    {
        $credentials = $this->config->getConfig()['localDBcredentials'];

        return new mysqli($credentials['host'], $credentials['username'], $credentials['pwd'], $credentials['db']);
    }



    public function getSalt()
    {
        $salt = (string) $this->salt;

        return $salt;
    }

    public function loadORM($model)
    {
        $table = $this->getTable();
        $db = $this->connect();

        $query = 'DESCRIBE '.strtolower($table);

        $result = $db->query($query);

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
            if ((isset($result->affected_rows)) && ($result->affected_rows > 0)) {
                return true;
            }
        }
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


    /**
     * Returns the name of this model beginning in lowercase (first letter).
     * According to Dorguzen convention, you should name your models after the DB tables they represent and the DB tables should
     * be in lowercase-at least the first letter, while the model being a class should begin with an uppercase.
     * This is to the effect that when you see a model, you should assume its DB table is of the same name beginning in lowercase.
     *
     * @return string
     */
    public function getTable()
    {
        $targetlass = get_class($this);
        $splitClassString = explode('\\', $targetlass);
        $class = $splitClassString[1];
        return lcfirst($class);
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
        $db = $this->connect();

        $table = $model->getTable();
        $data = array();
        $datatypes = '';

        foreach (get_object_vars($this) as $property => $value) {
            if (array_key_exists($property, $model->_columns)) {
                $data[$property] = $value;

                if (in_array($property, $this->passwordField)) {
                    $data['key'] = $this->getSalt();
                    $datatypes .= 'ss';
                }
                else {
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
            header('Location: /dashboard.php?del=1');
            exit();
        }
    }


    /**
     * Delete a record by its field ID when you are not worried about managing fk constraints.
     * If you are worried about fk constraints; use the deleteWhere() method instead.
     * @param int $id the value to match on the table's given ID field
     * @param bool $tablePrefixed if the id field is named with a prefix of the table name, default true
     *     This will be the field name used as the ID field if $fieldName is a blank string
     * @param string $fieldName if the id field is named something else-not 'id' or table name-prefixed,
     *     default '' (blank string). If this is not blank, this is the field that will be used as the ID field. Use it to
     *     override all other choices and specify the exact table field name to match.
     * @return bool
     */
    public function deleteById($id, $tablePrefixed = true, $fieldName = '')
    {
        $table = $this->getTable();
        $db = $this->connect();

        if ($fieldName != '') {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$fieldName} = ?");
        }
        else if ($tablePrefixed)
        {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$table}_id = ?");
        }
        else
        {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE id = ?");
        }
        $stmt->bind_param( 'i', $id );
        $stmt->execute();

        return true;
    }



    /**
     * query DB without a prepared stmt
     *
     * @param $query just pass it your SQL query string
     * @return returns true if you are updating of deleting, it returns the last inserted ID if you are inserting,
     *     returns the result set if you are selecting, returns false if the operation fails.
     */
    public function query($query)
    {
        $db = $this->connect();

        $res = $db->query($query);

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

            if ((isset($db->insert_id)) && ($db->insert_id != 0)) {
                return $db->insert_id;
            }
            else
            {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $columns
     * @param $criteria
     * @param $orderBy
     * @return array|false|string
     */
    public function selectWhere($columns = array(), $criteria = array(), $orderBy = '')
    {
        $model = new $this->whoCalledMe;
        $fields_to_select = array();
        $datatypes = '';
        $criterion = array();
        $table = strtolower($model->getTable());


        if ((!empty($columns)) && (!empty($criteria))) {
            foreach ($columns as $column) {
                if (!array_key_exists($column, $model->getColumnDatatypes())) {
                    return 'The field ' . $column . ' does not exist in the ' . strtolower($model->getTable() . ' table');
                }
                else {
                    $fields_to_select[] = $column;
                }
            }

            //check criteria
            foreach ($criteria as $key => $crits)
            {
                //securely check that that field exists n DB table
                if (!array_key_exists($key, $model->getColumnDatatypes())) {
                    return 'The field ' . $key . ' does not exist in the ' . strtolower($model->getTable() . ' table');
                }
                else {
                    $criterion[$key] = $crits;
                    $datatypes .= $model->getColumnDatatypes()[$key];
                }
            }
        }

        if ((empty($columns)) && (!empty($criteria))) {
            foreach ($model->getColumnDatatypes() as $fieldName => $datatype)
            {
                $fields_to_select[] = $fieldName;
            }

            foreach ($criteria as $key => $crits)
            {
                if (!array_key_exists($key, $model->getColumnDatatypes())) {
                    return 'The field ' . $key . ' does not exist in the ' . strtolower($model->getTable() . ' table');
                }
                else {
                    $criterion[$key] = $crits;
                    $datatypes .= $model->getColumnDatatypes()[$key];
                }
            }
        }

        if ((!empty($columns)) && (empty($criteria))) {
            foreach ($columns as $column) {
                //securely check that that field exists n DB table
                if (!array_key_exists($column, $model->getColumnDatatypes())) {
                    return 'The field ' . $column . ' does not exist in the ' . strtolower($model->getTable() . ' table');
                }
                else {
                    $fields_to_select[] = $column;
                }
            }
        }

        if ((empty($columns)) && (empty($criteria))) {
            foreach ($model->getColumnDatatypes() as $fieldName => $datatype)
            {
                $fields_to_select[] = $fieldName;
            }
        }

        $db = $this->connect();
        $columns = (array)$fields_to_select;
        $where = (array)$criterion;

        $where_placeholders = '';
        $where_values = [];
        $count = 0;

        $columns_as_string = implode(',', $columns);

        if (!empty($where)) {
            foreach ($where as $field => $value) { //album_name => 'holidays'
                if ($count > 0) {
                    $where_placeholders .= ' AND ';
                }

                $where_placeholders .= $field . '=?';
                $where_values[] = $value;

                $count++;
            }

            array_unshift($where_values, $datatypes);

            if ($orderBy != '') {
                $stmt = $db->prepare("SELECT {$columns_as_string} FROM {$table} WHERE {$where_placeholders} {$orderBy}");
            }
            else {
                $stmt = $db->prepare("SELECT {$columns_as_string} FROM {$table} WHERE {$where_placeholders}");
            }

            call_user_func_array(array($stmt, 'bind_param'), $this->ref_values($where_values));
        }
        else {
            if ($orderBy != '') {
                $stmt = $db->prepare("SELECT {$columns_as_string} FROM {$table} {$orderBy}");
            }
            else {
                $stmt = $db->prepare("SELECT {$columns_as_string} FROM {$table}");
            }
        }

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows) {
            $results_basket = [];

            while ($row = $this->fetchAssocStatement($stmt)) {
                $results_basket[] = $row;
            }

            $stmt->close();

            return $results_basket;
        }
        else {
            return false;
        }
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

        $stmt->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");

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

        // Cast $data to an array
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
     * Builds the query strings from the data array given
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

            elseif ($field == 'users_created')
            {
                if ($value === '') {
                    $placeholders .= "NOW(),";
                }
                else {
                    $placeholders .= '?,';
                }
            }
            else
            {
                $placeholders .= '?,';
            }
        }

        $fields = substr($fields, 0, -1);
        $placeholders = substr($placeholders, 0, -1);


        return array( $fields, $placeholders, $values );

    }





    /**
     * @param $stmt
     * @return array|null
     */
    protected function fetchAssocStatement($stmt)
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
     * Creates an optimized array to be used by bind_param() to bind
     * values to the query placeholders
     *
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
     * @param int $id the value to match on the table's given ID field
     * @param bool $tablePrefixed if the id field is named with a prefix of the table name, default true
     *     This will be the field name used as the ID field if $fieldName is a blank string
     * @param string $fieldName if the id field is named something else-not 'id' or table name-prefixed,
     *     default '' (blank string). If this is not blank, this is the field that will be used as the ID field. Use it to
     *     override all other choices and specify the exact table field name to match.
     * @return array
     */
    public function getById($id, $tablePrefixed = true, $fieldName = '')
    {
        $table = $this->getTable();
        $db = $this->connect();
        $model = new $this->whoCalledMe;

        if ($fieldName != '') {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$fieldName} = ?");
        }
        else if ($tablePrefixed)
        {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$table}_id = ?");
        }
        else
        {
            if (property_exists($model, 'idField'))
            {
                $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$model->idField} = ?");
            }
            else
            {
                $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
            }
        }
        $stmt->bind_param( 'i', $id );
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
            AND ".$passwordField." = AES_ENCRYPT(?, ?)
            AND users_emailverified = 'yes'";

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


    
 