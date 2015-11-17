<?php

namespace rest;

use rest\iResource;

interface iResourceController {

    public function retrieve($resourceId);

    public function listAll($limit = 1000, $page = 1, $sort = null, $desc = 0);
    
    public function replace(iResource $resource);

    public function replaceAll(Array $resources);

    public function create(iResource $resource);

    public function delete($resourceId);

    public function deleteAll();
}
