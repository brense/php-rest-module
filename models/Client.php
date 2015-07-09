<?php

namespace models;

use \rest\iResource;
use \controllers\ClientController;

// dummy model
class Client implements iResource {

    private $_controller;
    private $_name = 'test';

    public function __construct() {
	$this->_controller = new ClientController();
    }

    public function getController() {
	return $this->_controller;
    }
    
    public function toArray(){
	return array('name' => $this->_name);
    }

}
