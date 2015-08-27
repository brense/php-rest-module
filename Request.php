<?php

namespace rest;

class Request {

    private static $_instance;
    private $_method;
    private $_path;
    private $_body;
    private $_bootstrap;

    private function __construct() {
	$this->_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
	$this->_path = $this->parsePath();
	$this->_body = $this->parseBody();
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
	$scriptName = filter_input(INPUT_SERVER, 'REQUEST_URI');
	if (strpos($scriptName, $this->_bootstrap) == 0) {
	    $path = substr($scriptName, strlen($this->_bootstrap));
	}
	(isset($path) && substr($path, -1, 1) == '/' ? $this->_path = substr($path, 0, -1) : $this->_path = $path);
    }

    private function parseBody() {
	if ($this->_method == 'PUT' || $this->_method == 'POST') {
	    $contents = file_get_contents('php://input');
	    $json = json_decode($contents);
	    if ($json) {
		$this->_body = $json;
	    } else {
		$this->_body = filter_input_array(INPUT_POST);
	    }
	}
    }

}
