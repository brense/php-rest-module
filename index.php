<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

require_once('rest/Request.php');
require_once('rest/Service.php');
require_once('rest/iResource.php');
require_once('rest/ResourceCollection.php');
require_once('rest/iResourceController.php');
require_once('controllers/ClientController.php');
require_once('models/Client.php');

use rest\Service;
use rest\Request;
use models\Client;

$service = new Service(array(
    'index.php/' => new Client()
));

$bootstrapPath = '/rest_framework'; // e.g. when the full path is http://www.mydomain.com/rest_framework
$req = Request::instance($bootstrapPath);
$response = $service->resolve($req);
echo json_encode($response);
