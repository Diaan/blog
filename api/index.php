<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, X-HTTP-Method-Override, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Credentials:false');
header('Access-Control-Max-Age: 86400');

require 'vendor/autoload.php';
require_once './DbHandler.php';
require_once './processor.php';
require_once './PassHash.php';


$app = new \Slim\Slim();
$app->add(new \SlimJson\Middleware(array(
  'json.status' => true,
  'json.override_error' => true,
  'json.override_notfound' => true
)));

date_default_timezone_set('Europe/Amsterdam');
$app->map('/:x+', function($x) {
    http_response_code(200);
})->via('OPTIONS');


/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post(
    '/register', 
    function() use ($app) {
        // check for required params
        verifyRequiredParams(array('name', 'email', 'password'));

        $response = array();

        // reading post params
        $name = $app->request->post('name');
        $email = $app->request->post('email');
        $password = $app->request->post('password');

        // validating email address
        validateEmail($email);

        $db = new DbHandler();
        $res = $db->createUser($name, $email, $password);
        if ($res == 0) {
            $response["error"] = false;
            $response["message"] = "You are successfully registered";
        } else if ($res == 1) {
            $response["error"] = true;
            $response["message"] = "Oops! An error occurred while registereing";
        } else if ($res == 2) {
            $response["error"] = true;
            $response["message"] = "Sorry, this email already existed";
        }
	    $app->render( 200, $response);
    }
);
$app->post(
    '/login', 
    function() use ($app) {
        // check for required params
        verifyRequiredParams(array('email', 'password'));

        // reading post params
        $email = $app->request()->post('email');
        $password = $app->request()->post('password');
        $response = array();

        $db = new DbHandler();
        // check for correct email and password
        if ($db->checkLogin($email, $password)) {
            // get the user by email
            $user = $db->getUserByEmail($email);

            if ($user != NULL) {
                $response["error"] = false;
                $response['id'] = $user['id'];
                $response['name'] = $user['name'];
                $response['email'] = $user['email'];
                $response['apiKey'] = $user['api_key'];
                $response['createdAt'] = $user['created_at'];
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
        } else {
            // user credentials are wrong
            $response['error'] = true;
            $response['message'] = 'Login failed. Incorrect credentials';
        }

	    $app->render( 200, $response);
    }
);

$app->post(
	'/queue', 
	function () use ($app) {
        $json = $app->request->getBody();
        $jobj = json_decode($json, true); 
	    $type = $jobj['type'];
	    $url  = $jobj['url'];
	   print_r($jobj); 
	    $db = new DbHandler();
	    $res = $db->createIncoming($type, $url);
	    if ($res != NULL) {
	    	
            $response["error"] = false;
            $response["data"] = $res;
        
	        if($type=="location"){
	    		$loc = $db->saveLocation($res, $jobj["data"]);
	    	}else if($type=="twitter"||$type=="instagram"||$type=="flickr"||$type=="soundcloud"||$type=="youtube"||$type=="blogger"){
	    		$p = new Processor();
	    		$processedresult = $p->process($res,$jobj["type"],$jobj["url"]);
	    	}	
    	} else {
            $response["error"] = true;
            $response["data"] = "Failed to create. Please try again";
        }
	}
);

$app->get(
	'/process/:id', 
    'authenticate', 
	function ($id) use ($app) {
		$db = new DbHandler();
	    $res = $db->getEntryToProcess($id)->fetch_assoc();

	    $p = new Processor();
	    $processedresult = array('changedRows' => $p->process($res["id"],$res["type"],trim($res["url"], '/')));
		$app->render( 200,$processedresult);
	}
);
$app->get(
	'/info/:id', 
    'authenticate', 
	function ($id) use ($app) {
		$db = new DbHandler();
	    $res = $db->getEntryToProcess($id)->fetch_assoc();

	    $p = new Processor();
	    $processedresult = array('info' => $p->info($res["id"],$res["type"],trim($res["url"], '/')));
		$app->render( 200,$processedresult);
	}
);

$app->get(
	'/queue', 
    'authenticate', 
	function ()  use ($app){
		$db = new DbHandler();
	    $res = $db->getQueue();
	    $response["error"] = false;
        $response["data"] = array();

        while ($queue = $res->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $queue["id"];
            $tmp["type"] = $queue["type"];
            $tmp["url"] = $queue["url"];
            $tmp["timestamp"] = $queue["timestamp"];
            array_push($response["data"], $tmp);
        }

	    $app->render( 200, $response);
	}
);

$app->get(
	'/queue/:id', 
    'authenticate', 
	function ($id)  use ($app){
		$db = new DbHandler();
	    $res = $db->getQueue($id);
	    $response["error"] = false;
        $response["data"] = array();

        while ($queue = $res->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $queue["id"];
            $tmp["type"] = $queue["type"];
            $tmp["url"] = $queue["url"];
            $tmp["timestamp"] = $queue["timestamp"];
            array_push($response["data"], $tmp);
        }

	    $app->render( 200, $response);
	}
);

$app->delete(
	'/queue/:id', 
    'authenticate', 
	function ($id)  use ($app){
		$db = new DbHandler();
	    $res = $db->deleteEntry($id);
	    $response["error"] = false;
        $response["data"] = $res;

	    $app->render( 200, $response);
	}
);

$app->delete(
	'/feed/:id', 
    'authenticate', 
	function ($id)  use ($app){
		$db = new DbHandler();
	    $res = $db->deleteEntry($id);
	    $response["error"] = false;
        $response["data"] = $res;

	    $app->render( 200, $response);
	}
);


$app->get(
	'/feed', 
    'authenticate', 
	function ()  use ($app){
		$db = new DbHandler();
	    $res = $db->getFeed();
	    $response["error"] = false;
        $response["data"] = array();

        while ($data = $res->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $data["id"];
            $tmp["type"] = $data["type"];
            if($data["title"]) $tmp["title"] = $data["title"];
            if($data["text"]) $tmp["text"] = $data["text"];
            if($data["imageurl"]) $tmp["imageurl"] = $data["imageurl"];
            $tmp["location"] = json_decode($data["location"],true);
            $tmp["timestamp"] = $data["timestamp"];
            array_push($response["data"], $tmp);
        }

	    $app->render( 200, $response);
	}
);

$app->run();

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();
 
        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            $app->render( 401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user = $db->getUserId($api_key);
            if ($user != NULL)
                $user_id = $user["id"];
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        $app->render( 400, $response);
        $app->stop();
    }
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $app = \Slim\Slim::getInstance();
        $response = array();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        $app->render( 400, $response);
        $app->stop();
    }
}
/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        $app->render( 400, $response);
        $app->stop();
    }
}
?>