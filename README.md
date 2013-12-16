# Newest Industry API PHP SDK

This is the Newest Industry API PHP SDK.


## Basic implementation
You can load the class via composer by using
    
    "newestindustry/niapiphp": "dev-master"

in the require part.

After that you can initiate the NI class by giving the client id, client secret and redirect uri to the default object:


    $config = array(
                    "client_id" => "clientid", 
                    "client_secret" => "clientsecret",
                    "redirect_uri" => "http://redirect/uri"
                );
    $ni = new \NI($config);
    
And start the login process by firing the login() function.     
    
    $ni->login();

Make sure you do this after starting your session. The session namespace used is "niapi".