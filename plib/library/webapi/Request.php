<?php
/**
 * This File is part of the plesk-acronis extension
 * (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by David Jardin <david.jardin@community.joomla.org>
 *
 * Date: 3/13/16
 * Time: 01:39 PM
 *
 * Helper to make the communication with the acronis REST Api easier
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class Modules_AcronisBackup_webapi_Request
 *
 * This is a wrapper class for the acronis webbackup api
 *
 * @category Helper
 * @author   David Jardin <david.jardin@community.joomla.org>
 * @version  Release: 1.0.0
 */
class Modules_AcronisBackup_webapi_Request
{
    /**
     * Endpoint for login-call
     */
    const LOGINENDPOINT = "/api/1/login";

    /**
     * Endpoint for informations about which API to use
     */
    const INFOENDPOINT = "/api/1/groups/self/backupconsole";

    /**
     * @var array Jar storing cookies as told by cURL
     */
    private $cookiejars = [];

    /**
     * @var null|string host of the backup management console API
     */
    private $apiHost = null;

    /**
     * @var null|string token given back by the Account Management Console for further use
     */
    private $apiToken = null;

    /**
     * @var null|string host of the account management console API
     */
    private $loginHost = null;

    /**
     * @var null|string username used for log-in on the account management console
     */
    private $username = null;

    /**
     * @var null|string password used for log-in on the account management console
     */
    private $password = null;

    /**
     * Modules_AcronisBackup_webapi_Request constructor.
     *
     * Set up initial connection
     *
     * @param string $loginHost Host of the account management console API
     * @param string $username  username used for log-in
     * @param string $password  password used for log-in
     */
    public function __construct($loginHost, $username, $password)
    {
        // set host, username and password
        $this->loginHost = (string) $loginHost;
        $this->username = (string) $username;
        $this->password = (string) $password;

        // Login first to start a session and get the correct hostname
        $this->login();

        // Retrieve "final" api endpoint data
        $this->retrieveApiInfo();

        // Establish remote connection to "final" api
        $this->establishConnection();
    }

    /**
     * request
     *
     * Passes a request to the api
     *
     * @param string $method   Method used for the call ("GET" or "POST")
     * @param string $endpoint endpoint of the API-Call
     * @param array  $data     Post-data to send with the call
     *
     * @return array
     */
    public function request($method, $endpoint, $data = [])
    {
        return $this->call($method, $this->apiHost . $endpoint, $data);
    }

    /**
     * retrieveApiInfo
     *
     * Retrieve information on final api host and token
     *
     * @return bool
     */
    protected function retrieveApiInfo()
    {
        $response = $this->call("GET", $this->loginHost . self::INFOENDPOINT);

        // Make sure the response is nicely formed
        if ($response['code'] != 200 || !$response['body']) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        $apiData = json_decode($response['body']);

        // Make sure we don't have a parse error
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        $this->apiHost = $apiData->host;
        $this->apiToken = $apiData->token;

        return true;
    }

    /**
     * establishConnection
     *
     * Establish connection to final remote api host
     *
     * @return bool
     */
    protected function establishConnection()
    {
        $response = $this->call("POST", $this->apiHost . "/api/remote_connection", ["access_token" => $this->apiToken]);

        // Make sure the response is nicely formed
        if ($response['code'] != 200 || !$response['body']) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        json_decode($response['body']);

        // Make sure we don't have a parse error
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        $this->hasConnection = true;

        return true;
    }

    /**
     * login
     *
     * Performs login on initial server
     *
     * @return bool
     */
    protected function login()
    {
        $credentials = [
            "username" => $this->username,
            "password" => $this->password
        ];

        $response = $this->call("POST", $this->loginHost . self::LOGINENDPOINT, $credentials);

        // Check for wrong credentials
        if ($response['code'] == 401) {
            throw new RuntimeException('Could not login into Acronis web api');
        }

        // Make sure the response is nicely formed
        if ($response['code'] != 200 || !$response['body']) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        json_decode($response['body']);

        // Make sure we don't have a parse error
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException('Unexpected API response');
        }

        return true;
    }

    /**
     * call
     *
     * Performs an API-Call
     *
     * @param string     $method Method used for the API-Call
     * @param string     $uri    URI of the API-Call
     * @param array|null $data   Post-Data given to the call
     *
     * @return array
     */
    protected function call($method, $uri, $data = null)
    {
        // Setup the cURL handle.
        $handle = curl_init();

        // Set the request method.
        switch (strtoupper($method)) {
            case 'GET':
                $options[CURLOPT_HTTPGET] = true;
                break;

            case 'POST':
                $options[CURLOPT_POST] = true;
                break;

            default:
                $options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
                break;
        }

        // Don't wait for body when $method is HEAD
        $options[CURLOPT_NOBODY] = ($method === 'HEAD');

        // If data exists let's encode it
        if (isset($data) && $method != 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        // set content type header
        $options[CURLOPT_HTTPHEADER] = ["Content-Type:application/json;charset=utf8"];

        // Set the request URL.
        $options[CURLOPT_URL] = (string) $uri;

        // We want our headers.
        $options[CURLOPT_HEADER] = true;

        // Return it... echoing it would be tacky.
        $options[CURLOPT_RETURNTRANSFER] = true;

        // Override the Expect header to prevent cURL from confusing itself in its own stupidity.
        // Link: http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
        $options[CURLOPT_HTTPHEADER][] = 'Expect:';

        // extract host from uri
        $host = parse_url($uri)['host'];

        // Handle sessions
        if ($this->cookiejars[$host]) {
            $options[CURLOPT_COOKIEFILE] = $this->cookiejars[$host];
        } else {
            $options[CURLOPT_COOKIEJAR] = $this->cookiejars[$host] = tempnam(sys_get_temp_dir(), "plesk-acronis");
        }

        // Set the cURL options.
        curl_setopt_array($handle, $options);
        // Execute the request and close the connection.
        $content = curl_exec($handle);

        // Check if the content is a string. If it is not, it must be an error.
        if (!is_string($content)) {
            $message = curl_error($handle);
            throw new RuntimeException($message);
        }
        // Get the request information.
        $info = curl_getinfo($handle);
        // Close the connection.
        curl_close($handle);

        $response = $this->getResponse($content, $info);

        return $response;
    }

    /**
     * getResponse
     *
     * Parses response and creates a nicely formed array
     *
     * @param string $content JSON-Content got from the API
     * @param array  $info    Curl-Info-Object
     *
     * @return array
     */
    protected function getResponse($content, $info)
    {
        // Create the response array.
        $return = [];

        // Get the number of redirects that occurred.
        $redirects = isset($info['redirect_count']) ? $info['redirect_count'] : 0;

        /*
         * Split the response into headers and body. If cURL encountered redirects, the headers for the redirected requests will
         * also be included. So we split the response into header + body + the number of redirects and only use the last two
         * sections which should be the last set of headers and the actual body.
         */
        $response = explode("\r\n\r\n", $content, 2 + $redirects);
        // Set the body for the response.
        $return['body'] = array_pop($response);

        // Get the last set of response headers as an array.
        $headers = explode("\r\n", array_pop($response));

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);
        $code = count($matches) ? $matches[0] : null;
        if (is_numeric($code)) {
            $return['code'] = (int) $code;
        } else {
            throw new UnexpectedValueException('No HTTP response code found.');
        }

        // Add the response headers to the response object.
        foreach ($headers as $header) {
            $pos = strpos($header, ':');
            $return['headers'][trim(substr($header, 0, $pos))] = trim(substr($header, ($pos + 1)));
        }

        return $return;
    }
}