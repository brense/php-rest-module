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
        try {
            $router = ResourceRouter::matchResource($request->path, $this->_resources);
	    $resource = $router->getResource();
            $result = $router->resolve($request);
            return json_encode($resource->mapToArray($result));
        } catch (ResourceNotFoundException $e) {
            header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
            return json_encode(array('error' => 'Resource not found'));
        } catch (\Exception $e) {
            header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 400 Bad Request');
            return json_encode(array('error' => $e->getMessage()));
        }
    }

}
