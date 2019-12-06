<?php
class Model extends SQLQuery {

    protected $_model;
    private static $_models=array();

    function __construct() {
 
        $this->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        $this->_model = get_class($this);
        $this->_table = strtolower($this->_model);
        if (!isset($this->abstract)) {
            $this->_describe();
        }
    }
 
    function __destruct() {
    }

    public static function model($className=__CLASS__)
    {
        if(isset(self::$_models[$className]))
            return self::$_models[$className];
        else
        {
            $model=self::$_models[$className]=new $className(null);
            return $model;
        }
    }
}