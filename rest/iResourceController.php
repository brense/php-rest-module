<?php

namespace rest;

use rest\iResource;
use rest\ResourceCollection;

/**
 * Resource controller interface
 */
interface iResourceController {
    
    public function retrieve($resourceId);
    public function listAll();
    public function replace($resourceId, iResource $resource);
    public function replaceAll(ResourceCollection $resources);
    public function create(iResource $resource);
    public function delete($resourceId);
    public function deleteAll();
    
}