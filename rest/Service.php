<?php

namespace rest;

use rest\Request;
use rest\iResource;

class Service {
    
    private $_resources = array();
    private $_resourceId;
    
    public function __construct(Array $resources = array()){
	$this->addResources($resources);
    }
    
    public function resolve(Request $req){
	$resource = $this->findResource($req->path);
	$ctrl = $resource->getController();
	// list and retrieve
	if($req->method == 'GET' && !empty($this->_resourceId)){
	    return $this->mapToObject($ctrl->retrieve($this->_resourceId)); // TODO: map the resource to plain object
	} else if($req->method == 'GET'){
	    return $this->mapToObjects($ctrl->listAll()); // TODO: map the resources to plain object
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
    
    public function addResource($path, iResource $resource){
	$this->_resources[$path] = $resource;
    }
    
    public function addResources(Array $resources){
	foreach($resources as $path => $resource){
	    $this->addResource($path, $resource);
	}
    }
    
    public function getResource($path){
	if(isset($this->_resources[$path])){
	    return $this->_resources[$path];
	} else {
	    throw new \Exception('No resource found at path \'' . $path . '\'');
	}
    }
    
    public function removeResource($path){
	if(isset($this->_resources[$path])){
	    unset($this->_resources[$path]);
	}
    }
    
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
    
    private function mapToObject(iResource $resource){
	$obj = $resource->toArray();
	return $obj;
    }
    
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