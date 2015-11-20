<?php

namespace rest;

class Service {

    private $_resources = array();

    public function addResource(Resource $resource) {
	$this->_resources[$resource->path] = $resource;
    }

    public function removeResource(Resource $resource) {
	if (isset($this->_resources[$resource->path])) {
	    unset($this->_resources[$resource->path]);
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
