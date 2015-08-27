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
	$resource = $this->matchResource($request);
	if (!empty($resource)) {
	    try {
		$router = new ResourceRouter($resource);
		$result = $router->resolve($request);
		if ($result instanceof iResourceModel) {
		    $result = $resource->matchSubset($request, $result);
		}
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

    private function matchResource(Request $request) {
	$parts = explode('/', $request->path);
	if (strlen(trim($parts[count($parts) - 1])) == 0) {
	    unset($parts[count($parts) - 1]);
	}
	if (strlen(trim($parts[0])) == 0) {
	    unset($parts[0]);
	}
	$last = null;
	while (count($parts) > 0) {
	    $path = implode('/', $parts) . '/';
	    if (isset($this->_resources[$path])) {
		$resource = $this->_resources[$path];
		$resource->setRequestedId($last);
		return $resource;
	    }
	    $last = array_pop($parts);
	}
	return null;
    }

}
