<?php
/**
 * Get the default sidebar menu from the NI Api
 * 
 * @author Martijn van Maasakkers
 * @package NIApiPHP
 */
 
namespace NI\Components;

/**
 * Sidebar class.
 */
class Sidebar
{
    /**
     * sidebar
     * 
     * (default value: "")
     * 
     * @var string
     * @access private
     */
    private $sidebar = "";
    
    /**
     * __construct function.
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        $api = \NI::getApi();
        $api->format = false;
        $sidebar = $api->get("/frontend/menu/left/_format/html");
        $this->sidebar = $sidebar->data;
    }
    
    /**
     * __toString function.
     * 
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->sidebar;
    }    
}
