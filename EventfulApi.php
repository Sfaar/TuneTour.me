<?PHP
/**
 * This file holds the EventfulApi class.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   Eventful
 * @author    Jesse Forrest <jesseforrest@gmail.com>
 * @copyright 2013 EventfulApi
 * @license   https://github.com/jesseforrest/EventfulApi License 1.0
 * @link      https://github.com/jesseforrest/EventfulApi/wiki
 */

/**
 * This class is a PHP client for the REST-based Eventful API web service.
 *
 * @category  PHP
 * @package   Eventful
 * @author    Jesse Forrest <jesseforrest@gmail.com>
 * @copyright 2013 EventfulApi
 * @license   https://github.com/jesseforrest/EventfulApi License 1.0
 * @link      https://github.com/jesseforrest/EventfulApi/wiki
 */
class EventfulApi
{
   /**
    * The URI of the API
    *
    * @var string
    */
   const API_URL = 'http://api.eventful.com';

   /**
    * The application key. This will be provided by http://api.eventful.com
    *
    * @var string|null
    */
   protected $appKey = null;

   /**
    * The username to login to the API
    *
    * @var string|null
    */
   protected $user = null;

   /**
    * The password to login to the API
    *
    * @var string|null
    */
   protected $password = null;

   /**
    * The user authentication key
    *
    * @var string|null
    */
   protected $userKey = null;

   /**
    * The latest response as unserialized data
    *
    * @var string|null
    */
   protected $response = null;

   /**
    * The class constructor which is used to create a new EventfulApi client.
    *
    * @param string $appKey The Eventful application key that was provided by
    * Eventful to you.
    *
    * @return void
    */
   public function __construct($appKey)
   {
      $this->appKey = $appKey;
   }

   /**
    * Returns the application key that was passed into the constructor
    *
    * @return string Returns the Eventful application key.
    */
   public function getAppKey()
   {
      return $this->appKey;
   }

   /**
    * Returns the latest response as unserialized data
    *
    * @return string|null Returns a string if a call was made previously or
    * null otherwise.
    */
   public function getResponseAsString()
   {
      return $this->response;
   }

   /**
    * Returns the latest response as a PHP associative array
    *
    * @return array|null Returns am array if a call was made previously or
    * null otherwise.
    */
  public function getResponseAsArray()
  {
    $json = json_decode(json_encode((array)simplexml_load_string($this->response)), 1);
    if (is_array($json) && array_key_exists("events", $json)) {
      foreach ($json["events"]["event"] as $event) {
        if ($event != null && is_array($event) && array_key_exists("url", $event) && array_key_exists("title", $event))
          echo "<span class='nearby'><a href='" . $event["url"] . "'>" . $event["title"] . "</a></span>";
      }
    }
  }

   /**
    * Attempt to login with a specific user to get user specific API responses
    * from Eventful. You do not need to use this function prior to using the
    * <var>call()</var> method. However, if you want specific user related
    * responses you will.
    *
    * @param string $user     The Eventful username
    * @param string $password The Eventful password
    *
    * @return boolean Returns <var>true</var> on successful login or
    * <var>false</var> otherwise.
    */
   public function login($user, $password)
   {
      $this->user = $user;
      $this->password = $password;

      // Call login to receive a nonce (an arbitrary number used only one time)
      // The nonce is stored in an error structure.
      $isSuccess = $this->call('users/login');
      if (!$isSuccess)
      {
         return false;
      }

      // Get response
      $response = $this->getResponseAsArray();
      if (!isset($response['nonce']))
      {
         return false;
      }

      // Get nonce
      $nonce = $response['nonce'];

      // Generate the digested password response.
      $passwordResponse = md5($nonce . ':' . md5($password));

      // Send back the nonce and response.
      $args = array(
         'nonce' => $nonce,
         'response' => $passwordResponse,
      );
      $isSuccess = $this->call('users/login', $args);
      if (!$isSuccess)
      {
         return false;
      }

      // Get response
      $response = $this->getResponseAsArray();
      if (!isset($response['user_key']))
      {
         return false;
      }

      // Store the provided user key
      $this->userKey = (string) $response['user_key'];

      return true;
   }

   /**
    * Call a method on the Eventful API.  To get the actual response from an
    * API call you will need to call the function
    * <var>getResponseAsArray()</var> or <var>getResponseAsString()</var>
    * after calling this function.
    *
    * @param string $method The API method (e.g. "events/search")
    * @param array  $args   An optional associative array of arguments to pass
    *                       to the API.
    *
    * @return boolean Returns <var>true</var> on success or <var>false</var>
    * otherwise.
    */
   public function call($method, $args = array())
   {
      // Methods may or may not have a leading slash.
      $method = trim($method, '/ ');

      // Construct the URL that corresponds to the method.
      $url = self::API_URL . '/rest/' . $method;

      // Add items to the arguments array
      $args['app_key'] = $this->appKey;
      $args['user'] = $this->user;
      $args['user_key'] = $this->userKey;

      // Make web request
      $this->response = $this->curl($url, $args);

      // Invalid response
      if ($this->response === false)
      {
         return false;
      }

      // Process the response XML through SimpleXML
      $xmlElement = new SimpleXMLElement($this->response);

      // Check for call-specific error messages
      if ($xmlElement->getName() === 'error')
      {
         return false;
      }

      return true;
   }

   /**
    * Attempts to make a POST cURL to the specified URL with the payload being
    * the params passed in.
    *
    * @param string $url  The URL to make a web request to.
    * @param array  $args An optional array of key/value pairs to pass to the
    *                     API.
    *
    * @return string|false Returns the content returned from the web request
    * on success or <var>false</var> on failure.
    */
   protected function curl($url, $args = array())
   {
      // Open connection
      $ch = curl_init();

      // Set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, count($args));
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));

      // Execute POST
      $response = curl_exec($ch);

      // Close connection
      curl_close($ch);

      return $response;
   }
}
