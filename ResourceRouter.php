<?php

namespace rest;

class ResourceRouter {

    private $_resource;
    private $_listQueryOptions = array('query', 'limit', 'page', 'sort', 'desc');
    
    private function __construct(Resource $resource) {
	$this->_resource = $resource;
    }
    
    public static function matchResource($path, Array $resources) {
        $last = null;
        $resource = null;
        $parts = self::trimArray(explode('/', $path));
        while (count($parts) > 0) {
            $fragment = implode('/', $parts) . '/';
            if (isset($resources[$fragment])) {
                $resource = $resources[$fragment];
                break;
            }
            $last = array_pop($parts);
        }
        if (!empty($last)) {
            $resource->setRequestedId($last); // TODO: set the id on the resource model!
        }
        if (!empty($resource) && $resource->hasSubsets()) {
            $router = self::matchSubset($resource, $path);
            if (!empty($router)) {
                return $router;
            }
        }
        if (!empty($resource)) {
            return new self($resource);
        }
	throw new ResourceNotFoundException('There is no resource at \'' . $path . '\'');
    }

    public function resolve(Request $request) {
	$match = $this->matchCustomRoute($request);
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
    
    public function getResource(){
	return $this->_resource;
    }
    
    private function matchCustomRoute(Request $request) {
	$path = substr($request->path, strpos($request->path, '/'. trim($this->_resource->path, '/')) + strlen('/'. trim($this->_resource->path, '/')));
	if (strlen($path) > 0) {
	    if (isset($this->_resource->customRoutes[$request->method]) && in_array($path, $this->_resource->customRoutes[$request->method])) {
		return $this->_resource->customRoutes[$path];
	    }
	}
        return false;
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
    
    private static function matchSubset($resource, $requestPath){
        $resourcePath = substr($requestPath, strlen($resource->path . $resource->requestedId . '/'));
        if (strpos($requestPath, $resource->path . $resource->requestedId . '/') === 0 && strlen($resourcePath) > 0) {
            $router = self::matchResource($resourcePath, $resource->subsets);
            if (!empty($router)) {
                return $router;
            }
        }
        return null;
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

class ResourceNotFoundException extends \Exception {}