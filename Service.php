<?php

namespace rest;

class Service {

    private $_resources = array();

    public function addResource(Resource $resource) {
	$this->_resources[$resource->path] = $resource;
    }

    public function getResource($path) {
	if (!in_array($path, $this->_resources)) {
	    throw new \Exception('There is no resource registered at path: \'' . $path . '\'');
	} else {
	    return $this->_resources[$path];
	}
	return null;
    }

    public function removeResource($path) {
	if (isset($this->_resources[$path])) {
	    unset($this->_resources[$path]);
	}
    }

    public function resolve(Request $request) {
	$resource = $this->matchResource($request->path);
	if (!empty($resource)) {
	    try {
		$router = new ResourceRouter($resource);
		$result = $router->resolve($request);
		return json_encode($resource->mapToArray($result));
	    } catch (\Exception $e) {
		header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 400 Bad Request');
		return json_encode(array('error' => $e->getMessage()));
	    }
	} else {
	    header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
	    return json_encode(array('error' => 'Resource not found'));
	}
    }

    private function matchResource($requestPath) {
	$resource = ResourceRouter::matchPathToResources($requestPath, $this->_resources);
	if(!is_null($resource)){
	    return $resource;
	}
	return null;
    }

}
