<?php

class Controller
{

    protected $model;
    protected $_model;
    protected $_controller;
    protected $_action;
    protected $_template;

    function __construct($model, $controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_model = $model;

        if (class_exists($model))
            $this->model = new $model;

        $this->_template = new Template($controller, $action);
    }

    function set($name, $value)
    {
        $this->_template->set($name, $value);
    }

    function _filter($ajax = false)
    {
        $user = User::getAuth();
        if (!$user) {
            if ($ajax)
                die('403');
            else
                $this->_template->redirect('site/index');
        }
        return $user;
    }

}