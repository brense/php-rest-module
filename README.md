# REST-framework
A tiny framework to quickly create a REST API in PHP

Usage example:

	$service = new Service();
	$clientResource = new Resource('/client', new Client(), new ClientController);
	$clientResource->addCustomRoute('GET', '/find', array(new ClientController, 'find'), array('name'));
	$service->addResource($clientResource);

	$request = Request::current();
	$request->setBootstrapPath('/service'); // e.g. when the full path is http://www.mydomain.com/service
	$response = $service->resolve($request);
	
	echo $response;
    
Model example:

    class Client implements iResourceModel {
		private $_name = 'foobar';
	
		public function __construct($name){
			$this->_name = $name;
		}
	
    	public function toArray(){
    		return array('name' => $this->_name);
		}
		
		public function __get($property){
			// implement
		}
		
		public function __set($property, $value){
			// implement
		}
		
		public function __isset($property){
			// implement
		}
    
    }
    
Controller example:

    class ClientController implements iResourceController {

    	public function retrieve($resourceId) {
    		// get client from database
    		return new Client();
    	}
    	
    	public function find($name) {
    		// get client from database
    		return new Client($name);
    	}
      
    	public function listAll() {
    		// get all clients from database
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
