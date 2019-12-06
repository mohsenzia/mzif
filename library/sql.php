<?php

class SQLQuery
{
    private static $pdo_con;

    protected $_dbHandle;
    protected $_result;
    protected $_query;
    protected $_table;

    protected $_describe = array();

    protected $_orderBy;
    protected $_order;
    protected $_extraConditions;
    protected $_hO;
    protected $_hM;
    protected $_hMABTM;
    protected $_page;
    protected $_limit;

    protected $_return = array();

    /** Connects to database **/
    function connect($address, $account, $pwd, $name)
    {
        if (!is_null(self::$pdo_con))
            $this->_dbHandle = self::$pdo_con;
        else {
            try {
                self::$pdo_con = new PDO("mysql:host=$address;dbname=$name;charset=utf8mb4", "$account", "$pwd");
            } catch (PDOException $err) {
                echo $err->getMessage();
                die;
            }
            $this->_dbHandle = self::$pdo_con;
        }
    }

    /** Disconnects from database **/
    function disconnect()
    {
        $this->_dbHandle = null;
        return 1;
    }


    function set()
    {
        $hasRelation = false;
        // If  $this->_result is not an array in selectById, convert to array
        if (!is_array($this->_result)) {
            $_result = array();
            array_push($_result, $this->_result);
        } else {
            $_result = $this->_result;
        }

        foreach ($_result as $result) {

            $temp = new $this->_model;
            $temp->_describe = $this->_describe;
            $temp->_result = $result;

            foreach ($result as $key => $value) {
                $temp->$key = $value;
            }


            if (isset($temp->belongTo)) {
                $hasRelation = true;
                foreach ($temp->belongTo as $key => $value) {
                    $value = explode(':', $value);
                    $tmpModel = new $value[1]();
                    $value = $value[0];
                    $temp->$key = $tmpModel->selectById($temp->$value);
                }
            }

            if (isset($temp->hasOne)) {
                $hasRelation = true;
                foreach ($temp->hasOne as $key => $value) {
                    $temp->$key = $value::model()->selectById($temp->$key);
                }
                $temp->_result = null;
                array_push($this->_return, $temp);
            }

            if (isset($temp->hasMany)) {
                $hasRelation = true;
                foreach ($temp->hasMany as $key => $value) {
                    $value = explode(':', $value);
                    $tmpModel = new $value[1]();
                    $value = $value[0];
                    $tmpModel->where($value, $temp->id);
                    $temp->$key = $tmpModel->selectAll();
                }
            }

            if (!$hasRelation) {
                array_push($this->_return, $temp);
            }
            $temp->_result = null;
        }

    }

    private function setCondition()
    {
        $conditions = " WHERE ";

        if (isset($this->_extraConditions)) {
            $conditions .= $this->_extraConditions;
        }

        if (isset($this->_orderBy)) {
            if (strcmp($conditions, " WHERE ") == 0) $conditions = "";
            if ($this->_orderBy == "rand()")
                $conditions .= ' ORDER BY rand()';
            else
                $conditions .= ' ORDER BY `' . $this->_table . '`.`' . $this->_orderBy . '` ' . $this->_order;
        }

        if (isset($this->_page)) {
            if (strcmp($conditions, " WHERE ") == 0) $conditions = "";
            $offset = ($this->_page - 1) * $this->_limit;
            $conditions .= ' LIMIT ' . $this->_limit . ' OFFSET ' . $offset;

        } else if (isset($this->_limit)) {
            if (strcmp($conditions, " WHERE ") == 0) $conditions = "";
            $conditions .= ' LIMIT ' . $this->_limit;
        }

        if (strcmp($conditions, " WHERE ") == 0) $conditions = "";

        return $conditions;
    }


    function where($field, $value, $operator = "=")
    {
        if (strlen($this->_extraConditions) > 0)
            $this->_extraConditions .= ' AND ';

        if ($value == "NULL")
            $this->_extraConditions .= '`' . $this->_table . '`.`' . $field . '` ' . $operator . ' ' . htmlspecialchars($value);
        else if ($operator == "in")
            $this->_extraConditions .= '`' . $this->_table . '`.`' . $field . '` ' . $operator . ' ' . htmlspecialchars($value);
        else if (is_int($value))
            $this->_extraConditions .= '`' . $this->_table . '`.`' . $field . '`' . $operator . htmlspecialchars($value);
        else
            $this->_extraConditions .= '`' . $this->_table . '`.`' . $field . '`' . $operator . '\'' . htmlspecialchars($value) . '\'';
    }

    function like($field, $value)
    {
        if (strlen($this->_extraConditions) > 0) $this->_extraConditions .= " AND ";
        $this->_extraConditions .= ' `' . $this->_table . '`.`' . $field . '` LIKE \'%' . htmlspecialchars($value) . '%\' ';
    }

    function setLimit($limit)
    {
        $this->_limit = $limit;
    }

    function setPage($page)
    {
        $this->_page = $page;
    }

    function orderBy($orderBy, $order = 'ASC')
    {
        $this->_orderBy = $orderBy;
        $this->_order = $order;
    }

    function In($field, $values)
    {
        if (strlen($this->_extraConditions) > 0) $this->_extraConditions .= " AND ";
        $this->_extraConditions .= ' `' . $this->_table . '`.`' . $field . '` in  (' . htmlspecialchars($values) . ')';
    }

    function notIn($field, $values)
    {
        if (strlen($this->_extraConditions) > 0) $this->_extraConditions .= " AND ";
        $this->_extraConditions .= ' `' . $this->_table . '`.`' . $field . '` not in  (' . htmlspecialchars($values) . ')';
    }


    function showHasOne()
    {
        $this->_hO = 1;
    }

    function showHasMany()
    {
        $this->_hM = 1;
    }

    function showHMABTM()
    {
        $this->_hMABTM = 1;
    }


    /** Describes a Table **/
    protected function _describe()
    {
        global $cache;

        $this->_describe = $cache->get('describe' . $this->_table);

        if (!$this->_describe) {

            $this->_describe = array();

            $this->_result = $this->_dbHandle->prepare('DESCRIBE `' . $this->_table . '`');
            $this->_result->execute();

            while ($row = $this->_result->fetch(PDO::FETCH_OBJ)) {
                array_push($this->_describe, $row->Field);
            }
            $cache->set('describe' . $this->_table, $this->_describe);
        }

        foreach ($this->_describe as $field) {
            $this->$field = null;
        }
    }

    /** Custom SQL Query **/
    function _query($query)
    {

        $this->_result = $this->_dbHandle->query($query);
        if (!$this->_result)
            return $this->_dbHandle->errorInfo();

        if (substr_count(strtoupper($query), "SELECT") > 0) {
            if ($this->_result->rowCount($this->_result) > 0) {
                return $this->_result->fetchAll(PDO::FETCH_OBJ);
            }
        } else {
            return true;
        }

    }

    /** Select Query **/
    function select()
    {
        $this->_return = array();
        $stmt = $this->_dbHandle->prepare("SELECT * FROM `$this->_table` " . $this->setCondition());

        $stmt->execute();
        $this->_result = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$this->_result) return null;

        $this->set();

        return $this->_return[0];
    }

    function selectAll()
    {
        $this->_return = array();
        $stmt = $this->_dbHandle->prepare("SELECT * FROM `$this->_table` " . $this->setCondition());

        $stmt->execute();
        $this->_result = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!$this->_result) return null;

        $this->set();

        return $this->_return;
    }

    function selectById($id)
    {
        $this->_return = array();
        $stmt = $this->_dbHandle->prepare("SELECT * FROM `$this->_table` WHERE `id` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $this->_result = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$this->_result) return null;

        $this->set();

        foreach ($this->_return as $r)
            return $r;
    }

    function selectByField($params = array(), $fetchAll = false)
    {
        $this->_return = array();

        $where = "";
        foreach ($params as $key => $value) {
            $where .= " `$key`='$value' AND";
        }
        $where = rtrim($where, 'AND');
        if ($where == "")
            $where = 1;

        $stmt = $this->_dbHandle->prepare("SELECT * FROM `$this->_table` WHERE $where");
        $stmt->execute();
        $this->_result = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!$this->_result) return null;

        $this->set();

        if ($fetchAll) {
            return $this->_return;
        } else
            foreach ($this->_return as $r)
                return $r;
    }


    /** Delete an Object **/
    function delete()
    {
        $this->_result = $this->_dbHandle->prepare('DELETE FROM `' . $this->_table . '` WHERE `id`=:id');
        $this->_result->bindParam(':id', $this->id);
        if ($this->_result->execute())
            return true;

        return false;
    }

    /** Saves an Object i.e. Updates/Inserts Query **/
    function save()
    {
        if (method_exists($this, 'beforeSave')) {
            $before = $this->beforeSave();
            if ($before !== false) {
                return $before;
            }
        }

        if (isset($this->id)) {
            $updates = '';
            foreach ($this->_describe as $field) {

                if ($this->$field) {

                    if (is_object($this->$field))
                        $this->$field = $this->$field->id;

                    $this->$field = str_replace("'", "\'", $this->$field);

                    $updates .= '`' . $field . "`='" . htmlspecialchars_decode($this->$field) . "',";
                }
            }
            $updates = substr($updates, 0, -1);

            $query = 'UPDATE `' . $this->_table . '` SET ' . $updates . ' WHERE `id`=\'' . htmlspecialchars($this->id) . '\'';

        } else {
            $fields = '';
            $values = '';
            foreach ($this->_describe as $field) {
                if ($this->$field) {
                    $fields .= '`' . $field . '`,';
                    $values .= '"' . htmlspecialchars($this->$field) . '",';
                }
            }

            $values = substr($values, 0, -1);
            $fields = substr($fields, 0, -1);

            $query = 'INSERT INTO `' . $this->_table . '` (' . $fields . ') VALUES (' . $values . ')';

        }

        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
        }

        $this->_result = $this->_dbHandle->query($query);

        if ($this->_result === false) {
            //return $this->_dbHandle->errorInfo();
            Mzif::logging($this->_dbHandle->errorInfo());
            return false;
        }

        if (isset($this->id))
            return true;
        else
            return $this->_dbHandle->lastInsertId();
    }

    function exists($params)
    {
        $where = "";
        foreach ($params as $key => $value) {
            $where .= " `$key`='$value' AND";
        }
        $where = rtrim($where, 'AND');
        if ($where == "")
            $where = 1;

        $stmt = $this->_dbHandle->prepare("SELECT * FROM `$this->_table` WHERE $where");
        $stmt->execute();
        $this->_result = $stmt->fetch(PDO::FETCH_OBJ);
        if ($this->_result)
            return $this->_result;

        return false;
    }

    function counts()
    {
        $stmt = $this->_dbHandle->prepare("SELECT count(*) as counts FROM `$this->_table` " . $this->setCondition());
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        if ($count)
            return $count->counts;

        return -1;
    }

    /** Clear All Variables **/
    function clear()
    {
        foreach ($this->_describe as $field) {
            $this->$field = null;
        }

        $this->_orderby = null;
        $this->_extraConditions = null;
        $this->_hO = null;
        $this->_hM = null;
        $this->_hMABTM = null;
        $this->_page = null;
        $this->_order = null;
    }

    /** Pagination Count **/
    function totalPages()
    {
        if ($this->_query && $this->_limit) {
            $pattern = '/SELECT (.*?) FROM (.*)LIMIT(.*)/i';
            $replacement = 'SELECT COUNT(*) FROM `$2` ';
            $countQuery = preg_replace($pattern, $replacement, $this->_query);
            $this->_result = mysql_query($countQuery, $this->_dbHandle);
            $count = mysql_fetch_row($this->_result);
            $totalPages = ceil($count[0] / $this->_limit);
            return $totalPages;
        } else {
            /* Error Generation Code Here */
            return -1;
        }
    }

    /** Get error string **/
    function getError()
    {
        return $this->_dbHandle->errorInfo();
    }


    function search()
    {

        global $inflect;

        $from = '`' . $this->_table . '` as `' . $this->_model . '` ';
        $conditions = '\'1\'=\'1\' AND ';
        $conditionsChild = '';
        $fromChild = '';

        if ($this->_hO == 1 && isset($this->hasOne)) {

            foreach ($this->hasOne as $alias => $model) {
                $table = strtolower($inflect->pluralize($model));
                $singularAlias = strtolower($alias);
                $from .= 'LEFT JOIN `' . $table . '` as `' . $alias . '` ';
                $from .= 'ON `' . $this->_model . '`.`' . $singularAlias . '_id` = `' . $alias . '`.`id`  ';
            }
        }

        if ($this->id) {
            $conditions .= '`' . $this->_model . '`.`id` = \'' . $this->_dbHandle->quote($this->id) . '\' AND ';
        }

        if ($this->_extraConditions) {
            $conditions .= $this->_extraConditions;
        }

        $conditions = substr($conditions, 0, -4);

        if (isset($this->_orderBy)) {
            $conditions .= ' ORDER BY `' . $this->_model . '`.`' . $this->_orderBy . '` ' . $this->_order;
        }

        if (isset($this->_page)) {
            $offset = ($this->_page - 1) * $this->_limit;
            $conditions .= ' LIMIT ' . $this->_limit . ' OFFSET ' . $offset;
        }

        $this->_query = 'SELECT * FROM ' . $from . ' WHERE ' . $conditions;
        #echo '<!--'.$this->_query.'-->';
        $stmt = $this->_dbHandle->prepare("SELECT * FROM $this->_table");
        $stmt->execute();
        $this->_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        $table = array();
        $field = array();
        $tempResults = array();
        $numOfFields = $stmt->rowCount();
        for ($i = 0; $i < $numOfFields; ++$i) {
            array_push($table, mysql_field_table($this->_result, $i));
            array_push($field, mysql_field_name($this->_result, $i));
        }
        if ($numOfFields > 0) {
            while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                for ($i = 0; $i < $numOfFields; ++$i) {
                    $tempResults[$table[$i]][$field[$i]] = $row[$i];
                }

                if ($this->_hM == 1 && isset($this->hasMany)) {
                    foreach ($this->hasMany as $aliasChild => $modelChild) {
                        $queryChild = '';
                        $conditionsChild = '';
                        $fromChild = '';

                        $tableChild = strtolower($inflect->pluralize($modelChild));
                        $pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
                        $singularAliasChild = strtolower($aliasChild);

                        $fromChild .= '`' . $tableChild . '` as `' . $aliasChild . '`';

                        $conditionsChild .= '`' . $aliasChild . '`.`' . strtolower($this->_model) . '_id` = \'' . $tempResults[$this->_model]['id'] . '\'';

                        $queryChild = 'SELECT * FROM ' . $fromChild . ' WHERE ' . $conditionsChild;
                        #echo '<!--'.$queryChild.'-->';
                        $resultChild = mysql_query($queryChild, $this->_dbHandle);

                        $tableChild = array();
                        $fieldChild = array();
                        $tempResultsChild = array();
                        $resultsChild = array();

                        if (mysql_num_rows($resultChild) > 0) {
                            $numOfFieldsChild = mysql_num_fields($resultChild);
                            for ($j = 0; $j < $numOfFieldsChild; ++$j) {
                                array_push($tableChild, mysql_field_table($resultChild, $j));
                                array_push($fieldChild, mysql_field_name($resultChild, $j));
                            }

                            while ($rowChild = mysql_fetch_row($resultChild)) {
                                for ($j = 0; $j < $numOfFieldsChild; ++$j) {
                                    $tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
                                }
                                array_push($resultsChild, $tempResultsChild);
                            }
                        }

                        $tempResults[$aliasChild] = $resultsChild;

                        mysql_free_result($resultChild);
                    }
                }


                if ($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany)) {
                    foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
                        $queryChild = '';
                        $conditionsChild = '';
                        $fromChild = '';

                        $tableChild = strtolower($inflect->pluralize($tableChild));
                        $pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
                        $singularAliasChild = strtolower($aliasChild);

                        $sortTables = array($this->_table, $pluralAliasChild);
                        sort($sortTables);
                        $joinTable = implode('_', $sortTables);

                        $fromChild .= '`' . $tableChild . '` as `' . $aliasChild . '`,';
                        $fromChild .= '`' . $joinTable . '`,';

                        $conditionsChild .= '`' . $joinTable . '`.`' . $singularAliasChild . '_id` = `' . $aliasChild . '`.`id` AND ';
                        $conditionsChild .= '`' . $joinTable . '`.`' . strtolower($this->_model) . '_id` = \'' . $tempResults[$this->_model]['id'] . '\'';
                        $fromChild = substr($fromChild, 0, -1);

                        $queryChild = 'SELECT * FROM ' . $fromChild . ' WHERE ' . $conditionsChild;
                        #echo '<!--'.$queryChild.'-->';
                        $resultChild = mysql_query($queryChild, $this->_dbHandle);

                        $tableChild = array();
                        $fieldChild = array();
                        $tempResultsChild = array();
                        $resultsChild = array();

                        if (mysql_num_rows($resultChild) > 0) {
                            $numOfFieldsChild = mysql_num_fields($resultChild);
                            for ($j = 0; $j < $numOfFieldsChild; ++$j) {
                                array_push($tableChild, mysql_field_table($resultChild, $j));
                                array_push($fieldChild, mysql_field_name($resultChild, $j));
                            }

                            while ($rowChild = mysql_fetch_row($resultChild)) {
                                for ($j = 0; $j < $numOfFieldsChild; ++$j) {
                                    $tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
                                }
                                array_push($resultsChild, $tempResultsChild);
                            }
                        }

                        $tempResults[$aliasChild] = $resultsChild;
                        mysql_free_result($resultChild);
                    }
                }

                array_push($result, $tempResults);
            }

            if (mysql_num_rows($this->_result) == 1 && $this->id != null) {
                mysql_free_result($this->_result);
                $this->clear();
                return ($result[0]);
            } else {
                mysql_free_result($this->_result);
                $this->clear();
                return ($result);
            }
        } else {
            mysql_free_result($this->_result);
            $this->clear();
            return $result;
        }

    }

}