<?php

namespace rest;

class ResourceRouter {

    private $_resource;
    private $_listAllOptions = array('limit', 'sort', 'desc');

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
	    $parameters = array();
	    switch ($function) {
		case 'listAll':
		    $parameters = $this->parseOptions($this->_listAllOptions, $request->parameters);
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

}
