<?php

namespace rest;

class Resource {

    private $_path;
    private $_model;
    private $_controller;
    private $_customRoutes = array();
    private $_subsets = array();
    private $_requestedId;
    private $_parent;

    public function __construct($path, iResourceModel $model, iResourceController $controller) {
	$this->_path = $path;
	$this->_model = $model;
	$this->_controller = $controller;
    }

    public function addCustomRoute($method, $path, callable $callback, Array $options = array()) {
	if(!array_key_exists($method, $this->_customRoutes)){
	    $this->_customRoutes[$method] = array();
	}
	$this->_customRoutes[$method][$path] = array(
	    'callback' => $callback,
	    'options' => $options
	);
    }

    public function addSubset(Resource $resource) {
	$resource->setParent($this);
	$this->_subsets[$resource->path] = $resource;
    }
    
    public function hasSubsets(){
	if(count($this->_subsets) > 0){
	    return true;
	}
	return false;
    }
    
    public function setParent(Resource $resource){
	$this->_parent = $resource;
    }
    
    public function hasParent(){
	if(!empty($this->_parent) && !empty($this->_parent->requestedId)){
	    return true;
	}
	return false;
    }

    public function setRequestedId($resourceModelId) {
	$this->_requestedId = $resourceModelId;
    }

    public function mapToModel($arr) {
	if (is_object($arr) || (is_array($arr) && array_keys($arr) !== range(0, count($arr) - 1))) {
	    $obj = clone $this->_model;
	    foreach ($arr as $k => $v) {
		if(property_exists($obj, '_' . $k)){
		    $obj->$k = $v;
		}
	    }
	    return $obj;
	} else if(is_array($arr)) {
	    foreach ($arr as &$item) {
		$item = $this->mapToModel($item);
	    }
	}
	return $arr;
    }

    public function mapToArray($mixed) {
	if ($mixed instanceof iResourceModel) {
	    return $mixed->toArray();
	} else if (is_array($mixed)) {
	    foreach ($mixed as &$item) {
		if ($item instanceof iResourceModel) {
		    $item = array(
			'url' => $this->_path . '/' . $item->id,
			'resource' => $item->toArray()
		    );
		}
	    }
	    return $mixed;
	} else {
	    return (array) $mixed;
	}
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

}
