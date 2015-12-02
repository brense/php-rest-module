# PHP REST module
This module is a tiny framework to quickly create a REST API in PHP

To use this framework, simply create a new resource and register it with a new service:
    
    $resource = new Resource('account/', new Account(), new AccountController());
    $service = new Service();
    $service->addResource($resource);
    
The `Account` class should implement the `iResourceModel` interface and the `AccountController` should implement the `iResourceController` interface. To resolve a request get the current request parameters and tell the service to resolve it:

    $request = Request::current();
    $service->resolve($request);
    
### Subsets and custom routes
Resources can also contain subsets or custom routes (in theory you can use this framework as a 'normal' router, but that is not the intention of this framework). To register a subset, add a new resource to the existing one:

    $subset = new Resource('orders/', new Order(), new OrderController());
    $resource->addSubset($subset);
    
You can now retrieve subsets for an account using the path `/account/[id]/orders`. Custom routes are added as follows:

    $subset->addCustomRoute('GET', 'custom/', array(new OrderController(), 'customFunctionName'));
    
You can also register anonymous functions.

### Setting a custom bootstrap path
If you use this framework on a subdomain it might be necessary to manually set the `bootstrap path`. For example, if you registered your resource to be found at the path `/account` but your requests actually look like `http://mydomain.com/mysubdomain/rest/account` you should set `/mysubdomain/rest` as your bootstrap path:

    $request = Request::current();
    $request->setBootstrapPath('/mysubdomain/rest');
    
### Mapping of requests to resource controller
Typical REST implementation: https://en.wikipedia.org/wiki/Representational_state_transfer#Example

Request method | Request path | Controller method | Request body
--- | --- | --- | ---
`GET` | `/account/[id]` | `retrieve` | empty
`GET` | `/account` | `listAll` | empty
`PUT` | `/account/[id]` | `replace` | resource model
`PUT` | `/account` | `replaceAll` | array or resource models
`POST` | `/account` | `create` | resource model
`DELETE` | `/account/[id]` | `delete` | empty
`DELETE` | `/account` | `deleteAll` | empty

### Example resource controller

    class AccountController implements iResourceController {

    	public function retrieve($resourceId) {
    		// get account from database
    		return new Account();
    	}
      
    	public function listAll($query = null, $limit = 1000, $page = 1, $sort = null, $desc = 0) {
    		// get all accounts from database
    	}
      
    	public function replace(iResourceModel $resource) {
    		// update the account in the database
    	}
      
    	public function replaceAll(Array $resources) {
    		// update all accounts in the database
    	}
      
    	public function create(iResourceModel $resource) {
    		// create an account in the database
    	}
      
    	public function delete($resourceId) {
    		// delete the account from the database
    	}
      
    	public function deleteAll() {
    		// delete all accounts from the database
    	}
      
    }

###Example resource model:

    class Account implements iResourceModel {
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
    
