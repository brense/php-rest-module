<?php

namespace controllers;

use \rest\iResource;
use \rest\ResourceCollection;
use \rest\iResourceController;
use \models\Client;

// dummy controller
class ClientController implements iResourceController {

    public function retrieve($resourceId) {
	return new Client();
    }

    public function listAll() {
	return new ResourceCollection(array(new Client()));
    }

    public function replace($resourceId, iResource $resource) {

    }

    public function replaceAll(ResourceCollection $resources) {

    }

    public function create(iResource $resource) {

    }

    public function delete($resourceId) {

    }

    public function deleteAll() {

    }

}