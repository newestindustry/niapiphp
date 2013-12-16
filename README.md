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

## Config options


<table>
	<tr>
    	<th>Key</th>
	    <th>Description</th>
	    <th>Default value</th>
		<th>Possible value(s)</th>
    </tr>
	<tr>
            <td>base_url</td>
            <td>The API endpoint URL</td>
            <td>"https://api.newestindustry.nl"</td>
            <td>A valid url</td>
        </tr>
        <tr>
            <td>client_id</td>
            <td>Client ID for oauth</td>
            <td>false</td>
            <td>A valid Client ID</td>
        </tr>
        <tr>
            <td>client_secret</td>
            <td>Client Secret for oauth</td>
            <td>false</td>
            <td>A valid Client Secret</td>
        </tr>
        <tr>
            <td>redirect_uri</td>
            <td>The URL to redirect to after logging in</td>
            <td>false</td>
            <td>A valid redirect uri</td>
        </tr>
        <tr>
            <td>scope</td>
            <td>The data scope</td>
            <td>"default"</td>
            <td>"default"</td>
        </tr>
        <tr>
            <td>api_key</td>
            <td>The API Key</td>
            <td>false</td>
            <td>A valid api key</td>
        </tr>
</table>
