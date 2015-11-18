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

    public function addCustomRoute($method, $path, callable $callback, $options) {
	if(!in_array($this->_customRoutes[$method])){
	    $this->_customRoutes[$method] = array();
	}
	$this->_customRoutes[$method][$path] = array(
	    'callback' => $callback,
	    'options' => $options
	);
    }

    public function matchCustomRoute(Request $request) {
	$path = substr($request->path, strpos($request->path, '/'. trim($this->_path, '/')) + strlen('/'. trim($this->_path, '/')));
	if (strlen($path) > 0) {
	    if (isset($this->_customRoutes[$request->method]) && in_array($path, $this->_customRoutes[$request->method])) {
		return $this->_customRoutes[$path];
	    }
	}
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

    public function matchSubset($requestPath) {
	$resourcePath = substr($requestPath, strlen($this->_path . $this->_requestedId . '/'));
	if (strpos($requestPath, $this->_path . $this->_requestedId . '/') === 0 && strlen($resourcePath) > 0) {
	    $resource = ResourceRouter::matchPathToResources($resourcePath, $this->_subsets);
	    if (!is_null($resource)) {
		return $resource;
	    }
	}
	return null;
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
	// If array is associative, map it. Else, loop through it and map it
	if (array_keys($arr) !== range(0, count($arr) - 1)) {
	    $obj = clone $this->_model;
	    foreach ($arr as $k => $v) {
		if (isset($obj->$k)) {
		    $obj->$k = $v;
		}
	    }
	    return $obj;
	} else {
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
