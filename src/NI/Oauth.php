<?php
/**
 * The Oauth class
 * 
 * @author Martijn van Maasakkers
 * @package NIApiPHP
 */
 
namespace NI;

/**
 * Oauth class.
 */
class Oauth
{
    
    /**
     * api
     * 
     * @var mixed
     * @access private
     */
    private $api;
    
    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $api
     * @return void
     */
    public function __construct($api)
    {
        $this->api = $api;
    }
    
    
    
    
}
