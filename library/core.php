<?php

function error_handler($errno, $errstr, $errfile, $errline){
	if(DEVELOPMENT_ENVIRONMENT === true)
		echo "<br/>#$errno: $errstr in $errfile on line $errline<br/>";
    else {
		Mzif::logging("#$errno: $errstr in $errfile on line $errline",'error');
		header('location:'.ERROR_404_PATH);
	}
		
	
}
function exception_handler($exception) {
    if(DEVELOPMENT_ENVIRONMENT === true)
		echo "<br/>Exception: " , $exception->getMessage(), " in ".$exception->getFile(), " on line ".$exception->getLine(), "\n";
    else {
		Mzif::logging($exception->getMessage(). " in ".$exception->getFile(). " on line ".$exception->getLine(),'error');
		header('location:'.ERROR_404_PATH);
	}
}

function setReporting() {

	set_error_handler("error_handler");
	set_exception_handler('exception_handler');

	if (DEVELOPMENT_ENVIRONMENT === true) {
		error_reporting(E_ALL);
		ini_set('display_errors','On');

	} else {
		error_reporting(E_ALL);
		ini_set('display_errors','Off');
		ini_set('log_errors','on');
	}
}

function routeURL($url) {
	global $routing;

	foreach ($routing as $pattern => $result) {
            if ( preg_match($pattern, $url) ) {
				return preg_replace($pattern, $result, $url);
			}
	}

	return ($url);
}

/** Main Call Function **/
function MainCall($url=null) {

    if(is_null($url)) {
        if (!isset($_GET['url']))
            $url = DEFAULT_CONTROLLER . '/' . DEFAULT_ACTION;
        else{
            $url = htmlspecialchars($_GET['url']);
			$url=routeURL($url);
			$url=ltrim($url,'/');
		}
	}
	
    $urlArray = explode("/",$url);   

	if(!isset($urlArray[0]) || strlen($urlArray[0])==0)
		$controller=DEFAULT_CONTROLLER;
	else
		$controller = $urlArray[0];
    
	array_shift($urlArray);
	
	// If no action set, set default as INDEX
	if(!isset($urlArray[0]) || strlen($urlArray[0])==0)
		$action=DEFAULT_ACTION;
	else
		$action = $urlArray[0];

	array_shift($urlArray);
	
    $queryString = $urlArray;
 
    $controllerName = $controller;
    $controller = ucwords($controller); // Capital first letter
    $model = $controller; // Model name must Capital form of Controller
    $controller .= 'Controller';

	if($controllerName=='403')
		die;
	
    //IF not exist controller in url, fatal error
    try{
		if(class_exists($controller)) {
			$dispatch = new $controller($model,$controllerName,$action);
		}
    }catch (Exception $e){
        throw new Exception("$controller Class not found!");
    }

    if ((int)method_exists($controller, $action)) {
        call_user_func_array(array($dispatch,$action),$queryString);
    } else {
        throw new Exception("$controller class or $action method not found!");
    }
}

/** Autoload any classes that are required **/
function loadController($className) {
    if (file_exists(ROOT . DS . 'library' . DS . strtolower($className) . '.class.php')) {
        require_once(ROOT . DS . 'library' . DS . strtolower($className) . '.class.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'Controllers' . DS . strtolower($className) . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($className) . '.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php');
    }
}

spl_autoload_register('loadController', true, true);
spl_autoload_register('PHPMailerAutoload', true, true);
setReporting();
MainCall();