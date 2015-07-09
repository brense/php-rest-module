# REST-framework
A tiny framework to quickly create a REST API in PHP

Usage example:

    $service = new Service(array(
        'client/' => new Client(),
        'project/' => new Project(),
        'invoice/' => new Invoice()
    ));

    $req = Request::instance();
    $response = $service->resolve($req);
    
    echo json_encode($response);
    
Client:

    class Client implements iResource {
    
		private $_controller;
		private $_name = 'foobar';
    
    	public function __construct() {
    		$this->_controller = new ClientController();
    	}

		public function getController() {
			return $this->_controller;
		}
	
    	public function toArray(){
    		return array('name' => $this->_name);
		}
    
    }
    
ClientController:

    class ClientController implements iResourceController {

    	public function retrieve($resourceId) {
    		// get client from database
    		return new Client();
    	}
      
    	public function listAll() {
    		// get all clients from database
    		return new ResourceCollection(array(new Client()));
    	}
      
    	public function replace($resourceId, iResource $resource) {
    		// update the client in the database
    	}
      
    	public function replaceAll(ResourceCollection $resources) {
    		// update all clients in the database
    	}
      
    	public function create(iResource $resource) {
    		// create a client in the database
    	}
      
    	public function delete($resourceId) {
    		// delete the client from the database
    	}
      
    	public function deleteAll() {
    		// delete all clients from the database
    	}
      
    }
