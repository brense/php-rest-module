<?php

namespace rest;

class Request {
    
    private static $_instance;
    private $_bootstrap;
    private $_path;
    private $_resourceId;
    private $_method;
    private $_body;
    
    private function __construct($bootstrap){
	$this->_bootstrap = $bootstrap;
	$this->parseRequestPath();
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
    
    public static function instance($bootstrap){
	if(empty(self::$_instance)){
	    self::$_instance = new self($bootstrap);
	}
	return self::$_instance;
    }
    
    public function __get($property){
	if(property_exists($this, '_' . $property)){
	    return $this->{'_' . $property};
	}
    }
    
    private function parseRequestPath(){
	$scriptName = filter_input(INPUT_SERVER, 'REQUEST_URI');
	if(strpos($scriptName, $this->_bootstrap) == 0){
	    $path = substr($scriptName, strlen($this->_bootstrap));
	}
	(isset($path) && substr($path, -1, 1) == '/' ? $this->_path = substr($path, 0, -1) : $this->_path = $path);
    }
    
}