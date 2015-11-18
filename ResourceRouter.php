<?php

namespace rest;

class ResourceRouter {

    private $_resource;
    private $_listQueryOptions = array('query', 'limit', 'page', 'sort', 'desc');

    public function __construct(Resource $resource) {
	$this->_resource = $resource;
    }

    public function resolve(Request $request) {
	$match = $this->_resource->matchCustomRoute($request);
	if ($match) {
	    $callback = $match['callback'];
	    $parameters = $this->parseOptions($match['options'], $request->parameters);
	} else {
	    $function = $this->translateRequestMethod($request->method, empty($this->_resource->requestedId));
	    $callback = array($this->_resource->controller, $function);
	    if($this->_resource->hasParent()){
		$className = $this->getClassName($this->_resource->parent->model);
		$request->setQueryParameter($className, $this->_resource->parent->requestedId);
	    }
	    $parameters = array();
	    switch ($function) {
		case 'listAll':
		    $parameters = $this->parseOptions($this->_listQueryOptions, $request->parameters);
		    break;
		case 'retrieve':
		case 'replace':
		case 'delete':
		    $parameters = array($this->_resource->requestedId);
		    break;
	    }
	    if (!empty($request->body)) {
		$parameters = array($this->_resource->mapToModel($request->body, $this->_resource));
	    }
	}
	return call_user_func_array($callback, $parameters);
    }
    
    public static function matchPathToResources($resourcePath, Array $resources) {
	$parts = self::trimArray(explode('/', $resourcePath));
	$last = null;
	$resource = null;
	while (count($parts) > 0) {
	    $path = implode('/', $parts) . '/';
	    if (isset($resources[$path])) {
		$resource = $resources[$path];
		$resource->setRequestedId($last);
		break;
	    }
	    $last = array_pop($parts);
	}
	$subset = null;
	if(!is_null($resource) && $resource->hasSubsets()){
	    $subset = $resource->matchSubset($resourcePath);
	}
	if (!is_null($subset)) {
	    return $subset;
	} else if (!is_null($resource)) {
	    return $resource;
	} else {
	    return null;
	}
    }

    private function translateRequestMethod($method, $isBulkRequest = false) {
	$single = array(
	    'GET' => 'retrieve',
	    'PUT' => 'replace',
	    'DELETE' => 'delete'
	);
	$bulk = array(
	    'GET' => 'listAll',
	    'PUT' => 'replaceAll',
	    'POST' => 'create',
	    'DELETE' => 'delete'
	);
	if (!$isBulkRequest) {
	    if ($method == 'POST') {
		throw new \Exception('Cannot create a resource with an existing resource ID');
	    } else {
		return $single[$method];
	    }
	} else {
	    return $bulk[$method];
	}
    }

    private function parseOptions(Array $names, Array $values) {
	
	$options = array();
	foreach ($names as $name) {
	    if (isset($values[$name])) {
		$options[] = $values[$name];
	    } else {
		$options[] = null;
	    }
	}
	return $options;
    }
    
    private function getClassName($class){
	$arr = explode('\\', get_class($class));
	return strtolower(array_pop($arr));
    }
    
    private static function trimArray(Array $array){
	if (strlen(trim($array[count($array) - 1])) == 0) {
	    unset($array[count($array) - 1]);
	}
	if (strlen(trim($array[0])) == 0) {
	    unset($array[0]);
	}
	return $array;
    }

}
