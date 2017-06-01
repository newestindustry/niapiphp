<?php


namespace NI\Api;

use \Ginger\Request\Url;
use \Ginger\Routes;
use \Ginger\Request\Parameters;
use \Ginger\Response as GingerResponse;

class Proxy
{
    /**
     * $apiDir is the directory containing the API code.
     *
     * @var string $apiDir
     */
    private $apiDir;

    /**
     * $token is the oauth_token used for authenticating through the API with NICCI Profile. This is needed here
     * because we don't use the headers directly in this way.
     *
     * @var string $token
     */
    private $token;

    /**
     * $apiKey is the api key used for authenticating through the API. This is needed here
     * because we don't use the headers directly in this way.
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * $profileUrl is the url of the nicci profile backend.
     * @var string $profileUrl
     */
    private $profileUrl;

    /**
     * $staticDir is needed for defining the API Static Folder (needed for file handling). Strategy here is now
     * to define STATIC_PATH=$staticDir and define STATIC_FILE_PATH=$staticDir/files (as both are needed in the API.
     * @var string $staticDir
     */
    private $staticDir;

    /**
     * Proxy constructor.
     *
     * @param string $apiDir
     * @param string $token
     * @param string $apiKey
     * @param string $profileUrl
     * @return Proxy
     */
    public function __construct($apiDir, $token, $apiKey, $profileUrl, $staticDir)
    {
        $this->apiDir = $apiDir;
        $this->token = $token;
        $this->apiKey = $apiKey;
        $this->profileUrl = $profileUrl;
        $this->staticDir = $staticDir;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param $apiKey
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Call bootstraps the old API. What it does is load the Ginger API directly and follows the steps in initializing
     * the environment.
     *
     * Steps are:
     *
     * - Parse data if necessary
     * - Load default API config.inc.php
     * - Construct Request(true) so initialization is skipped
     * - Set URL based on given $resource
     * - Set Route based on URL
     * - Set Data params from the data object given.
     * - Also set raw data body
     * - Use the Ginger parseParameterValues and cleanReservedParams methods for the magic typecasting done there
     * - Set \Ginger\Request\Parameters
     * - Get the action used (index, get, post, put, patch, delete, options).
     * - Set the bearer_token, oauth_token (same but used in different places)
     * - Set api_key
     * - Set template (not sure if this is used anywhere anymore but was still in original code.
     * - Create a \Ginger\Response object.
     * - Do the go(true) to do the request and set data in the Response object.
     * - Create a \NI\Api\Response object so the Basement CMS can handle the response in the exact same way as before.
     *
     * @param string $resource
     * @param string $method
     * @param mixed $data
     * @return Response
     * @throws Proxy\Exception
     */
    public function call($resource, $method, $data)
    {
        if ($method != "GET") {
            // Fun stuff. The CMS works with http built queries as well... Ok, to be sure it's not a json "string"
            // we need to try to parse it first to json and if it fails we try the parse_str.
            if (gettype($data) === "string") {
                $postVars = json_decode($data, true);
                if (!$postVars) {
                    parse_str($data, $postVars);
                }
                $data = $postVars;
            }
        }

        $proxy = true;
        // This is needed for the API to properly select the NICCI Profile URL
        putenv("PROFILE_URL=" . $this->profileUrl);
        putenv("STATIC_PATH=" . $this->staticDir);
        putenv("STATIC_FILE_PATH=" . $this->staticDir . "/files");

        $configFile = $this->apiDir . "/public/config.inc.php";
        if (!file_exists($configFile)) {
            throw new \NI\Api\Proxy\Exception("API Config file not found", 501);
        }
        // Use require_once here because it needs to be included in place and this method can be called multiple times.
        require_once $configFile;

        $request = new \Ginger\Request(true);
        // Order of setting is important! Please DON'T change this!
        $request->setURL(new Url($resource));
        $request->setRoute(Routes::detect($request->getUrl()->path));
        $params = new Parameters($request->getUrl(), $request->getRoute(), true);

        // We have to array_merge here because the original getFilterParams (private internal function) uses $_GET
        // directly
        $params->setFilterParameters(array_merge($request->getUrl()->queryParts, $params->getFilterParameters()));
        $params->setDataParams($data);
        // Todo: As the data can also be string should we do this? I think we should as we parse it above. @mvmaasakkers
        $params->setRawData(json_encode($data));
        $params->parseParameterValues();
        $params->cleanReservedParams();
        $request->setParameters($params);

        $action = $request->getAction($method);
        $request->setAction($action);

        \Ginger\System\Parameters::$bearer_token = $this->getToken();
        \Ginger\System\Parameters::$oauth_token = $this->getToken();
        \Ginger\System\Parameters::$api_key = $this->getApiKey();
        if ($action = "options") {
            \Ginger\System\Parameters::$template = $action;
        } else {
            if (!\Ginger\System\Parameters::$template) {
                \Ginger\System\Parameters::$template = $request->getRoute()->{"resource"} . "/" . $action;
            }
        }

        $gingerResponse = new GingerResponse();
        $gingerResponse->setRequest($request);
        $request->setResponse($gingerResponse);

        $response = $request->go(true);

        $apiResponse = new \NI\Api\Response();
        $apiResponse->status = $response->getStatus();
        $apiResponse->content_type = $response->getContentType();
        $apiResponse->data = json_decode(json_encode($response->getData()));

        return $apiResponse;
    }
}
