<?php
 
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 */
class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
 
    /* ------------- `users` table method ------------------ */
    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $email, $password) {
        $response = array();
 
        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
 
            // Generating API key
            $api_key = $this->generateApiKey();
 
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(name, email, password_hash, api_key, status) values(?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);
 
            $result = $stmt->execute();
 
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return 0;
            } else {
                // Failed to create user
                return 1;
            }
        } else {
            // User with same email already existed in the db
            return 2;
        }
 
        //return $response;
    }
 
    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->bind_result($password_hash);
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
 
            $stmt->fetch();
 
            $stmt->close();
 
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
 
            // user not existed with the email
            return FALSE;
        }
    }
 
    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
 
    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id,name, email, api_key, status, created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }
 
    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $api_key = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }
 
    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }
 
    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
 
    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* - queue methods --------*/
    public function createIncoming($type, $url) {
        $stmt = $this->conn->prepare("INSERT INTO feed(type,url) VALUES (?,?)");
        $stmt->bind_param("ss", $type,$url);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $new_id = $this->conn->insert_id;
            if ($new_id) {
                return $new_id;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    public function saveLocation($id, $input) {
      $location = json_encode($input["location"]);
      $date = DateTime::createFromFormat('F d, Y \a\t g:iA', $input["timestamp"]);
      $timestamp = $date->format('Y-m-d H:i:s');

      $stmt = $this->conn->prepare("UPDATE feed SET location=?,timestamp=?, processed=1 WHERE id=?");
      $stmt->bind_param("ssi",$location, $timestamp, $id);

      $result = $stmt->execute();
      $num_affected_rows = $stmt->affected_rows;
      $stmt->close();

      return $num_affected_rows;
    }

    public function savePost($id, $input) {
      $now = new DateTime("now");
      $now = $now->format('Y-m-d H:i:s');

      if(isset($input["location"])){
        $location = json_encode($input["location"]);
      }else{
        $location = null;
      }
      if(isset($input["text"])){
        $text = $input["text"];
      }else{
        $text = "";
      }

      if(isset($input["imageurl"])){
        $imageurl = $input["imageurl"];
      }else{
        $imageurl = null;
      }

      if(isset($input["timestamp"])){
        $timestamp = $input["timestamp"];
      }else{
        $timestamp = null;
      }

      if(isset($input["embedcode"])){
        $embedcode = $input["embedcode"];
      }else{
        $embedcode = null;
      }

      if(isset($input["title"])){
        $title = $input["title"];
      }else{
        $title = null;
      }

      $stmt = $this->conn->prepare("UPDATE feed SET location=?,timestamp=?, text=?, imageurl=?, title=?, embedcode=?,processed=1 WHERE id=?");
      $stmt->bind_param("ssssssi", $location, $timestamp, $text, $imageurl, $title, $embedcode, $id);

      $result = $stmt->execute();
      $num_affected_rows = $stmt->affected_rows;
      $stmt->close();

      return $num_affected_rows;
    }

    public function getQueue($id = false) {

      if($id){
        $stmt = $this->conn->prepare("SELECT * FROM feed WHERE processed = 0 AND id=? ORDER BY timestamp DESC");
        $stmt->bind_param("i", $id);

      }else{
        $stmt = $this->conn->prepare("SELECT * FROM feed WHERE processed = 0 ORDER BY timestamp DESC");
      }
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      
      if ($result) {
          return $result;
      } else {
          return NULL;
      }
    }

    public function deleteEntry($id) {
      $stmt = $this->conn->prepare("DELETE FROM feed WHERE id = ?");
      $stmt->bind_param("s", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      
      if ($result) {
          return $result;
      } else {
          return NULL;
      }
    }

    public function getEntryToProcess($id) {
      $stmt = $this->conn->prepare("SELECT * FROM feed WHERE id = ?");
      $stmt->bind_param("s", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      
      if ($result) {
          return $result;
      } else {
          return NULL;
      }
    }

    public function getFeed() {
      $stmt = $this->conn->prepare("SELECT * FROM feed WHERE processed = 1 ORDER BY timestamp DESC");
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();
      
      if ($result) {
          return $result;
      } else {
          return NULL;
      }
    }
 
}
 
?>