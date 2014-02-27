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
     * The static API class pointer
     *
     * @var NI\Api
     * @access public
     * @static
     */
    public static $api;


    /**
     * token
     *
     * (default value: false)
     *
     * @var bool
     * @access public
     * @static
     */
    public static $token = false;
    /**
     * profile
     *
     * @var mixed
     * @access private
     */
    private $profile;

    /**
     * namespace
     *
     * (default value: "niapi")
     *
     * @var string
     * @access public
     */
    public static $namespace = "niapi";

    /**
     * __construct function.
     *
     * @access public
     * @param array $config (default: array())
     * @return void
     */
    public function __construct($config = array())
    {
        // Make sure to start a session
        if ($this->checkSessionStatus() === FALSE) {session_start();}

        self::setApi(new \NI\Api($this));
        $this->readApiConfig($config);

        if(isset($_SESSION[self::$namespace]['token']) && $_SESSION[self::$namespace]['token']->access_token) {
            $this->setToken($_SESSION[self::$namespace]['token']->access_token);
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
        $this->getApi()->readConfig($config);
    }

    /**
     * login function.
     *
     * @access public
     * @param bool $register (default: false)
     * @return void
     */
    public function login($register = false)
    {
        if(!isset($_SESSION[self::$namespace]['token'])) {
            if(!$this->profile && !isset($_GET['code']) && !isset($_GET['error'])) {
                if ($register == true) {
                    $this->getApi()->redirectToRegister();
                } else {
                    $this->getApi()->redirectToLogin();
                }
            } elseif(isset($_GET['error'])) {
                throw new \NI\Oauth\Exception($_GET['error_description']);
            } elseif(isset($_GET['code'])) {
                self::$token = $this->getApi()->getToken();
            }
        }

        return self::$token;
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
     * getNamespace function.
     *
     * @access public
     * @return string
     */
    public function getNamespace()
    {
        return self::$namespace;
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
        $this->getApi()->setToken($token);
    }

    /**
     * checkSessionStatus function.
     *
     * This is a backward compatible function for session_status
     * so we can make our claim that you need php 5.3+ instead
     * of 5.4+
     *
     * @access public
     * @return boolean
     */
    public function checkSessionStatus() {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

    /**
     * getApi function.
     *
     * @access public
     * @static
     * @return \NI\Api
     */
    public static function getApi()
    {
        return self::$api;
    }

    /**
     * setApi function.
     *
     * @access public
     * @static
     * @param \NI\Api $api
     * @return void
     */
    public static function setApi($api)
    {
        self::$api = $api;
    }


}
