<?php

namespace rest;

class ResourceCollection implements \Iterator {
    
    private $_position = 0;
    private $_resources = array();
    
    public function __construct($resources){
	$this->_resources = $resources;
    }
    
    public function rewind(){
	$this->_position = 0;
    }
    
    public function current(){
	return $this->_resources[$this->_position];
    }
    
    public function key(){
	return $this->_position;
    }
    
    public function next(){
	++$this->_position;
    }
    
    public function valid(){
	return isset($this->_resources[$this->_position]);
    }
    
}