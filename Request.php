<?php

namespace rest;

class Request {

    private static $_instance;
    private $_method;
    private $_path;
    private $_body;
    private $_parameters = array();
    private $_bootstrap;

    private function __construct() {
	$this->_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
	$this->_path = $this->parsePath();
	$this->_body = $this->parseBody();
	$this->_parameters = $this->parseParameters();
    }

    public static function current() {
	if (empty(self::$_instance)) {
	    self::$_instance = new self();
	}
	return self::$_instance;
    }

    public function setBootstrapPath($path) {
	$this->_bootstrap = $path;
	$this->_path = $this->parsePath();
    }
    
    public function setQueryParameter($parameter, $value){
	if(!isset($this->_parameters['query'])){
	    $this->_parameters['query'] = array();
	}
	$this->_parameters['query'][$parameter] = $value;
    }

    public function __get($property) {
	if (property_exists($this, '_' . $property)) {
	    return $this->{'_' . $property};
	}
    }

    public function __isset($property) {
	if (isset($this->{'_' . $property})) {
	    return true;
	}
	return false;
    }

    private function parsePath() {
	$parsed = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'));
	$path = $parsed['path'];
	if (strpos($parsed['path'], $this->_bootstrap) == 0) {
	    $path = substr($parsed['path'], strlen($this->_bootstrap));
	}
	return trim($path, '/');
    }

    private function parseBody() {
	if ($this->_method == 'PUT' || $this->_method == 'POST') {
	    $contents = file_get_contents('php://input');
	    $json = json_decode($contents);
	    if ($json) {
		return $json;
	    } else {
		return filter_input_array(INPUT_POST);
	    }
	}
    }
    
    private function parseParameters() {
	$parameters = array();
	$parsed = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'));
	if(isset($parsed['query'])){
	    $params = explode('&', $parsed['query']);
	    foreach($params as $param){
		$parts = explode('=', $param);
		if(!isset($parts[1])){
		    $parts[1] = null;
		}
		$parameters[$parts[0]] = $parts[1];
	    }
	}
	return $parameters;
    }

}
