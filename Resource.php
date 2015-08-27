<?php

namespace rest;

class Resource {

    private $_path;
    private $_model;
    private $_controller;
    private $_customRoutes = array();
    private $_subsets = array();
    private $_requestedId;

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
	$path = substr($request->path, strlen($this->_path));
	if (strlen($path) > 0) {
	    if (isset($this->_customRoutes[$request->method]) && in_array($path, $this->_customRoutes[$request->method])) {
		return $this->_customRoutes[$path];
	    } else {
		throw new \Exception('No custom route was found for \'' . $path . '\'');
	    }
	}
    }

    public function addSubset($path, $property) {
	$this->_subsets[$path] = $property;
    }

    public function matchSubset(Request $request, $model) {
	$path = substr($request->path, strlen($this->_path . '/' . $model->id));
	if (strlen($path) == 0) {
	    // if no specific subset is requested, make sure the model will contain all subsets
	    foreach ($this->_subsets as $property) {
		$model->$property; // trigger the getter on the subset property
	    }
	    return $model;
	} else {
	    if (in_array($path, $this->_subsets)) {
		return $this->_subsets[$path];
	    } else {
		throw new \Exception('No subset was found for \'' . $path . '\'');
	    }
	}
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
