<?php
/**
 * The NI class
 * 
 * @author Martijn van Maasakkers
 * @package NIApiPHP
 */


/**
 * NI class.
 */
class NI 
{
    /**
     * api
     * 
     * @var mixed
     * @access private
     */
    private $api;
    /**
     * profile
     * 
     * @var mixed
     * @access private
     */
    private $profile;
    /**
     * token
     * 
     * (default value: false)
     * 
     * @var bool
     * @access private
     */
    private $token = false;
    
    /**
     * namespace
     * 
     * (default value: "niapi")
     * 
     * @var string
     * @access public
     */
    public $namespace = "niapi";

    /**
     * __construct function.
     * 
     * @access public
     * @param array $config (default: array())
     * @return void
     */
    public function __construct($config = array())
    {
        $this->api = new \NI\Api($this);
        $this->readApiConfig($config);
        
        if(isset($_SESSION[$this->namespace]['token']) && $_SESSION[$this->namespace]['token']->access_token) {
            $this->setToken($_SESSION[$this->namespace]['token']->access_token);
        }
    }
    
    /**
     * readApiConfig function.
     * 
     * @access public
     * @param array $config (default: array())
     * @return void
     */
    public function readApiConfig($config = array())
    {
        $this->api->readConfig($config);        
    }
    
    /**
     * login function.
     * 
     * @access public
     * @return void
     */
    public function login()
    {
        if(!isset($_SESSION[$this->namespace]['token'])) {
            if(!$this->profile && !isset($_GET['code']) && !isset($_GET['error'])) {
                $this->getApi()->redirectToLogin();
            } elseif(isset($_GET['code'])) {
                $this->token = $this->getApi()->getToken();
            }    
        }
        
        return $this->token;
    }
    
    /**
     * logout function.
     * 
     * @access public
     * @return void
     */
    public function logout()
    {
        $this->getApi()->logout();
    }
    
    /**
     * getApi function.
     * 
     * @access public
     * @return void
     */
    public function getApi()
    {
        return $this->api;
    }
    
    /**
     * getNamespace function.
     * 
     * @access public
     * @return void
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    
    /**
     * setToken function.
     * 
     * @access public
     * @param mixed $token
     * @return void
     */
    public function setToken($token)
    {
        $this->getApi()->setToken($token);
    }
    
}