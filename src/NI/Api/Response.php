<?php
/**
 * The API Response class
 * 
 * @author Martijn van Maasakkers
 * @package NIApiPHP
 */
namespace NI\Api;

/**
 * Response class.
 */
class Response
{
    /**
     * status
     * 
     * (default value: 0)
     * 
     * @var int
     * @access public
     */
    public $status = 0;
    /**
     * data
     * 
     * @var mixed
     * @access public
     */
    public $data;
    /**
     * format
     * 
     * @var mixed
     * @access public
     */
    public $format;
    
    /**
     * isSuccess function.
     * 
     * @access public
     * @return void
     */
    public function isSuccess()
    {
        if(in_array($this->status, array(200, 201, 204) )) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * isError function.
     * 
     * @access public
     * @return void
     */
    public function isError()
    {
        if(!in_array($this->status, array(200, 201, 204) )) {
            return true;
        } else {
            return false;
        }
    }
}
