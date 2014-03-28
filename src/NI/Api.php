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
     * (default value: "https://api.mycollectiv.es")
     * 
     * @var string
     * @access public
     */
    public $base_url = "https://api.mycollectiv.es";
    
    /**
     * auth_url
     * 
     * (default value: "https://auth.newestindustry.nl")
     * 
     * @var string
     * @access public
     */
    public $auth_url = "https://api.mycollectiv.es";
    
    
    /**
     * prefix
     * 
     * (default value: "/oauth")
     * 
     * @var string
     * @access public
     */
    public $prefix = "/oauth";
    
    
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
     * locale
     * 
     * (default value: "nl_NL")
     * 
     * @var string
     * @access public
     */
    public $locale = "nl_NL";

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
     * api_key
     * 
     * @var string
     * @access private
     */
    private $api_key;

    /**
     * format
     * 
     * (default value: false)
     * 
     * @var bool
     * @access public
     */
    public $format = false;
    
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
            if(\NI::$token && $me->isSuccess()) {
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
     * logout function.
     * 
     * @access public
     * @return void
     */
    public function logout()
    {
        $a = $this->delete("/token/", true);
        unset($_SESSION[\NI::$namespace]);
        \NI::$token = null;
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
        $vars = array("api_key", "base_url", "auth_url", "prefix", "client_id", "client_secret", "redirect_uri", "scope", "locale");
        
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
        if($this->client_id == "" || $this->redirect_uri == "") {
            throw new \NI\Oauth\Exception("No client id or redirect uri given");
        }
    
    
        $params = array(
            "response_type" => "code",
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_uri,
            "scope" => $this->scope,
            "locale" => $this->locale
        );

        header("Location: ".$this->auth_url.$this->prefix."/auth?".http_build_query($params));
        die();
    }
    
    /**
     * redirectToRegister function.
     * 
     * @access public
     * @return void
     */
    public function redirectToRegister()
    {
        if($this->client_id == "" || $this->redirect_uri == "") {
            throw new \NI\Oauth\Exception("No client id or redirect uri given");
        }
    
    
        $params = array(
            "response_type" => "code",
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_uri,
            "scope" => $this->scope,
            "_locale" => $this->locale
        );

        header("Location: ".$this->auth_url.$this->prefix."/register/?".http_build_query($params));
        die();
    }
    
    /**
     * getLinkToSocialNetwork function.
     * 
     * @access public
     * @param string $name (default: "")
     * @return void
     */
    public function getLinkToSocialNetwork($name = "")
    {
        $supported = array("facebook", "google", "linkedin", "twitter");
        if($name == "" || !in_array($name, $supported)) {
            throw new \NI\Oauth\Exception("Social network ".$name." not supported");
        }
        
        if($this->client_id == "" || $this->redirect_uri == "") {
            throw new \NI\Oauth\Exception("No client id or redirect uri given");
        }
        
        $params = array(
            "response_type" => "code",
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_uri,
            "scope" => $this->scope,
            "locale" => $this->locale
        );

        return $this->auth_url.$this->prefix."/connect/".$name."?".http_build_query($params);
    }
    
    /**
     * getToken function.
     * 
     * @access public
     * @return object
     */
    public function getToken()
    {
        if($this->client_id == "" || $this->client_secret == "") {
            throw new \NI\Oauth\Exception("No client id or client secret");
        }
        
        $params = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "code" => $_GET['code'],
            "scope" => $this->scope
        );
        
        $token = $this->post("/token", $params, true);

        if($this->ni && $token->isSuccess()) {
            $_SESSION[\NI::$namespace]['token'] = $token->data;
            \NI::$token = $token->data->access_token;
        } else {
         	\NI::$token = null;
            /* throw new \NI\Oauth\Exception($_GET['error_description']); */
        }

        return \NI::$token;
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
    public function post($uri, $data, $auth = false)
    {
        return $this->call($uri, "POST", $data, $auth);
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
    public function delete($uri, $auth = false)
    {
        return $this->call($uri, "DELETE", array(), $auth);
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
    private function call($resource, $method, $data = array(), $auth = false)
    {
        $headers = array();
        if($auth) {
            $url = $this->auth_url.$this->prefix.$resource;
        } else {
            $url = $this->base_url.$resource;
        }

        

        if(\NI::$token) {
                $headers[] = 'Authorization: oauth_token '.\NI::$token;
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
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        break;
                        
                case "PUT":
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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
        
        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            $content_type = $info['content_type'];
        } else {
        	$content_type = 'application/json; charset=utf-8';
        }
        curl_close($ch);
        
        $jsonContentType = "application/json";
        if(substr(strtolower($content_type), 0, strlen($jsonContentType)) === $jsonContentType) {
            $this->format = true;

        }

        if($e && $this->format) {
            $e = json_decode($e);
            $this->format = false;
        }
        
        $response = new \NI\Api\Response();
        $response->status = $http_status;
        $response->content_type = $content_type;
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
        \NI::$token = $token;
    }
    
}
