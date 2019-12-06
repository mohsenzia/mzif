<?php
class Template {
     
    protected $variables = array();
    protected $_controller;
    protected $_action;
     
    function __construct($controller,$action) {
        $this->_controller = $controller;
        $this->_action = $action;
    }
 
    /** Set Variables **/
    function set($name,$value) {
        $this->variables[$name] = $value;
    }
 
    /** Display Template **/
    function render($action=null,$doNotRenderHeader = false) {

		extract($this->variables);
		
		if (!$doNotRenderHeader) {
			
			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . '_header.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . '_header.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . '_header.php');
			}
		}

		if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $action . '.php')) {
			include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $action . '.php');		 
		}
			
		if (!$doNotRenderHeader) {
			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . '_footer.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . '_footer.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . '_footer.php');
			}
		}

		die;
    }

	function redirect($url){
		header('location:'.BASE_PATH.DS.$url);
		die;
	}
 
}