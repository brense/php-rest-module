<?php

namespace rest;

/**
 * Singleton class intended to make the current request parameters
 * available throughout the application
 */
class Request {
    
    private static $_instance;
    private $_path;
    private $_resourceId;
    private $_method;
    private $_body;
    
    /**
     * Substracts the request parameters from the $_SERVER global variable
     * and fills the request body with the contents in php://input or $_POST
     *
     * @param string $bootstrap
     */
    private function __construct($bootstrap){
	$this->parseRequestPath($bootstrap);
	$this->_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
	if($this->_method == 'PUT' || $this->_method == 'POST'){
	    $contents = file_get_contents('php://input');
	    $json = json_decode($contents);
	    if($json){
		$this->_body = $json;
	    } else {
		$this->_body = filter_input_array(INPUT_POST);
	    }
	}
    }
    
    /**
     * exposes Request instance to the rest of the application
     * 
     * @param string $bootstrap
     * @return Request
     */
    public static function instance($bootstrap){
	if(empty(self::$_instance)){
	    self::$_instance = new self($bootstrap);
	}
	return self::$_instance;
    }
    
    /**
     * Getter
     * 
     * @param string $property
     * @return mixed
     */
    public function __get($property){
	if(property_exists($this, '_' . $property)){
	    return $this->{'_' . $property};
	}
    }
    
    /**
     * substracts the base/bootstrap from the curret request path
     * e.g. if the request is http://mydomain.com/bootstrap/index.php
     * this method will set the path to "index.php"
     * 
     * @param string $bootstrap
     */
    private function parseRequestPath($bootstrap){
	$scriptName = filter_input(INPUT_SERVER, 'REQUEST_URI');
	if(strpos($scriptName, $bootstrap) == 0){
	    $path = substr($scriptName, strlen($bootstrap));
	}
	(isset($path) && substr($path, -1, 1) == '/' ? $this->_path = substr($path, 0, -1) : $this->_path = $path);
    }
    
}