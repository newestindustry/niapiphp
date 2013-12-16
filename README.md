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

After this, the oauth token is stored in session and is now automatically used in any future calls. If you want to get the logged in users profile, you can use the predefined profile call or use the API calls directly.
    
    $profile = $ni->getApi()->get("/me/");
    
This returns the \NI\Api\Response object. The actual user profile data would be in

    $profile->data->me;
    
But as a helper getProfile is available:

	$profile = $ni->getApi()->getProfile();
	
	
And is either false, or the users object.