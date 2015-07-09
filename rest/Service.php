<?php

namespace rest;

use rest\Request;
use rest\iResource;
use rest\ResourceCollection;

/**
 * The service class handles the requests and calls the corresponding methods
 * on the registered resource controllers
 */
class Service {
    
    private $_resources = array();
    private $_resourceId;
    
    /**
     * Initializes the service
     * 
     * @param array $resources
     */
    public function __construct(Array $resources = array()){
	$this->addResources($resources);
    }
    
    /**
     * Resolves the current request and returns the result of the
     * corresponsing method on the controller for the registered resource
     * 
     * @param Request $req
     * @return mixed
     */
    public function resolve(Request $req){
	$resource = $this->findResource($req->path);
	$ctrl = $resource->getController();
	// list and retrieve
	if($req->method == 'GET' && !empty($this->_resourceId)){
	    return $this->mapToObject($ctrl->retrieve($this->_resourceId));
	} else if($req->method == 'GET'){
	    return $this->mapToObjects($ctrl->listAll());
	}
	// replace (all) or create
	if($req->method == 'PUT' && !empty($this->_resourceId)){
	    $resource = $ctrl->retrieve($this->_resourceId);
	    if(!empty($resource)){
		return $ctrl->replace($resource, $req->body);
	    } else {
		return $ctrl->create($req->body);
	    }
	} else if($req->method == 'PUT'){
	    return $ctrl->replaceAll($req->body);
	}
	// create
	if($req->method == 'POST'){
	    return $ctrl->create($req->body);
	}
	// delete (all)
	if($req->method == 'DELETE' && !empty($this->_resourceId)){
	    return $ctrl->delete($this->_resourceId);
	} else if($req->method == 'DELETE'){
	    return $ctrl->deleteAll();
	}
    }
    
    /**
     * Binds a new resource to a request path
     * 
     * @param string $path
     * @param iResource $resource
     */
    public function addResource($path, iResource $resource){
	$this->_resources[$path] = $resource;
    }
    
    /**
     * Binds several new resources to corresponding request paths
     * 
     * @param array $resources
     */
    public function addResources(Array $resources){
	foreach($resources as $path => $resource){
	    $this->addResource($path, $resource);
	}
    }
    
    /**
     * Returns the resource corresponding to the request path
     * or throws an exception if the resource cannot be found
     * 
     * @param string $path
     * @return iResource
     * @throws \Exception
     */
    public function getResource($path){
	if(isset($this->_resources[$path])){
	    return $this->_resources[$path];
	} else {
	    throw new \Exception('No resource found at path \'' . $path . '\'');
	}
    }
    
    /**
     * Removes the resource registered at the given request path
     * 
     * @param string $path
     */
    public function removeResource($path){
	if(isset($this->_resources[$path])){
	    unset($this->_resources[$path]);
	}
    }
    
    /**
     * Finds the best resource match for a given request path
     * or throws an exception if no suitable resource can be found
     * 
     * @param string $fullPath
     * @return iResource
     * @throws \Exception
     */
    private function findResource($fullPath){
	$parts = explode('/', $fullPath);
	if(strlen(trim($parts[count($parts)-1])) == 0){
	    unset($parts[count($parts)-1]);
	}
	if(strlen(trim($parts[0])) == 0){
	    unset($parts[0]);
	}
	while(count($parts) > 0){
	    $path = implode('/', $parts) . '/';
	    if(isset($this->_resources[$path])){
		return $this->_resources[$path];
	    }
	    $this->_resourceId = array_pop($parts);
	}
	throw new \Exception('No resource matched the path: \'' . $fullPath . '\'');
    }
    
    /**
     * Map a resource to an array
     * 
     * @param iResource $resource
     * @return array
     */
    private function mapToObject(iResource $resource){
	$obj = $resource->toArray();
	return $obj;
    }
    
    /**
     * Map a resource collection to an array
     * 
     * @param ResourceCollection $resources
     * @return array
     */
    private function mapToObjects(ResourceCollection $resources){
	$arr = array();
	while($resources->valid()){
	    $resource = $resources->current();
	    $arr[] = $this->mapToObject($resource);
	    $resources->next();
	}
	return $arr;
    }
    
}