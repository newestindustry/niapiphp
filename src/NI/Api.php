<?php
/**
 * The API class enables users to call our API directly.
 * 
 * @author Martijn van Maasakkers
 * @package NIApiPHP
 */
 
namespace NI;

/**
 * Api class.
 */
class Api
{
    /**
     * ni object
     * 
     * @var \NI
     * @access private
     */
    private $ni;
    
    /**
     * base_url
     * 
     * (default value: "https://api.newestindustry.nl")
     * 
     * @var string
     * @access public
     */
    public $base_url = "https://api.newestindustry.nl";
    /**
     * client_id
     * 
     * (default value: "")
     * 
     * @var string
     * @access public
     */
    public $client_id = "";
    /**
     * client_secret
     * 
     * (default value: "")
     * 
     * @var string
     * @access public
     */
    public $client_secret = "";
    /**
     * scope
     * 
     * (default value: "default")
     * 
     * @var string
     * @access public
     */
    public $scope = "default";
    /**
     * redirect_uri
     * 
     * (default value: "")
     * 
     * @var string
     * @access public
     */
    public $redirect_uri = "";


    /**
     * me
     * 
     * (default value: false)
     * 
     * @var mixed
     * @access private
     */
    private $me = false;

    /**
     * oauth_token
     * 
     * @var string
     * @access private
     */
    private $oauth_token;
    /**
     * api_key
     * 
     * @var string
     * @access private
     */
    private $api_key;

    /**
     * format
     * 
     * (default value: true)
     * 
     * @var bool
     * @access private
     */
    private $format = true;
    
    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $ni (default: false)
     * @return void
     */
    public function __construct($ni = false)
    {
        if(get_class($ni) === "NI") {
            $this->ni = $ni;
        } elseif(is_array($ni)) {
            $this->readConfig($ni);
        }
    }
    
    /**
     * getProfile function.
     * 
     * @access public
     * @return void
     */
    public function getProfile()
    {
        if(!$this->me) {
            $me = $this->get("/me/");
            if($this->oauth_token && $me->isSuccess()) {
                $this->me = $me->data->me;
                return $this->me;
            } else {
                return false;
            }    
        } else {
            return $this->me;
        }
        
    }
    
    
    /**
     * readConfig function.
     * 
     * @access public
     * @param array $config (default: array())
     * @return void
     */
    public function readConfig($config = array())
    {
        $vars = array("api_key", "base_url", "client_id", "client_secret", "redirect_uri", "scope");
        
        foreach($vars as $var) {
            if(isset($config[$var])) {
                $this->{$var} = $config[$var];    
            }
        }
    }
    
    /**
     * redirectToLogin function.
     * 
     * @access public
     * @return void
     */
    public function redirectToLogin()
    {
        $params = array(
            "response_type" => "code",
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_uri,
            "scope" => $this->scope
        );

        header("Location: ".$this->base_url."/oauth/auth/?".http_build_query($params));
        die();
    }
    
    /**
     * getToken function.
     * 
     * @access public
     * @return object
     */
    public function getToken()
    {
        $params = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "code" => $_GET['code'],
            "scope" => $this->scope
        );
        
        $token = $this->post("/oauth/token/", $params);
        
        if($this->ni && $token->isSuccess()) {
            $_SESSION[$this->getNI()->namespace]['token'] = $token->data;
            $this->oauth_token = $token->data->access_token;
        } else {
            
        }

        return $token;
    }

    /**
     * get function.
     * 
     * @access public
     * @param string $uri
     * @return \NI\Api\Response
     */
    public function get($uri)
    {
        return $this->call($uri, "GET");
    }
    
    /**
     * post function.
     * 
     * @access public
     * @param string $uri
     * @param array $data
     * @return \NI\Api\Response
     */
    public function post($uri, $data)
    {
        return $this->call($uri, "POST", $data);
    }
    
    /**
     * put function.
     * 
     * @access public
     * @param string $uri
     * @param array $data
     * @return \NI\Api\Response
     */
    public function put($uri, $data)
    {
        return $this->call($uri, "PUT", $data);
    }
    
    /**
     * delete function.
     * 
     * @access public
     * @param string $uri
     * @return \NI\Api\Response
     */
    public function delete($uri)
    {
        return $this->call($uri, "DELETE");
    }
    
    /**
     * call function.
     * 
     * @access private
     * @param string $resource
     * @param string $method
     * @param array $data (default: array())
     * @return \NI\Api\Response
     */
    private function call($resource, $method, $data = array())
    {
        $headers = array();
        $url = $this->base_url.$resource;

        if($this->oauth_token) {
                $headers[] = 'Authorization: oauth_token '.$this->oauth_token;
        }
        
        if($this->api_key) {
                $headers[] = 'X-API-Key: '.$this->api_key;
        }
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        switch($method) {
                case "GET":
                        
                        break;
                        
                case "POST":
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        break;
                        
                case "PUT":
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        break;
                        
                case "DELETE":
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                        break;
        }
                        
        $e = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->status = $http_status;
        if($e === false) {
            throw new \NI\Api\Exception();
        }

        curl_close($ch);

        if($e && $this->format) {
            $e = json_decode($e);
        }
        
        $response = new \NI\Api\Response();
        $response->status = $http_status;
        $response->data = $e;
        
        return $response;
        
    }
    
    /**
     * getNI function.
     * 
     * @access public
     * @return \NI
     */
    public function getNI()
    {
        return $this->ni;
    }
    
    /**
     * setToken function.
     * 
     * @access public
     * @param string $token
     * @return void
     */
    public function setToken($token)
    {
        $this->oauth_token = $token;
    }
    
}
