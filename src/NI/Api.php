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
class Api {
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
	 * auth_url
	 *
	 * (default value: "https://auth.newestindustry.nl")
	 *
	 * @var string
	 * @access public
	 */
	public $auth_url = "https://api.newestindustry.nl";

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

	public $header_name = "oauth_token";

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
	public function __construct($ni = false) {
		if (get_class($ni) === "NI") {
			$this->ni = $ni;
		} elseif (is_array($ni)) {
			$this->readConfig($ni);
		}
	}

	/**
	 * getProfile function.
	 *
	 * @access public
	 * @return void
	 */
	public function getProfile() {
		if (!$this->me) {
			$me = $this->get("/me", array(), true);
			if (\NI::$token && $me->isSuccess()) {
                $ame = new \stdClass();
                $ame->emailaddress = $me->data->EmailAddress;
                $ame->id = $me->data->ID;
                $ame->firstname = $me->data->Firstname;
                $ame->lastname = $me->data->Lastname;
                $this->me = $ame;

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
	public function logout() {
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
	public function readConfig($config = array()) {
		$vars = array("api_key", "base_url", "auth_url", "prefix", "client_id", "client_secret", "redirect_uri", "scope", "locale", "header_name");

		foreach ($vars as $var) {
			if (isset($config[$var])) {
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
	public function redirectToLogin() {
		if ($this->client_id == "" || $this->redirect_uri == "") {
			throw new \NI\Oauth\Exception("No client id or redirect uri given");
		}

		$params = array(
			"response_type" => "code",
			"client_id" => $this->client_id,
			"redirect_uri" => $this->redirect_uri,
			"scope" => $this->scope,
			"locale" => $this->locale,
		);

		header("Location: " . $this->auth_url . $this->prefix . "/auth?" . http_build_query($params));
		die();
	}

	/**
	 * redirectToRegister function.
	 *
	 * @access public
	 * @return void
	 */
	public function redirectToRegister() {
		if ($this->client_id == "" || $this->redirect_uri == "") {
			throw new \NI\Oauth\Exception("No client id or redirect uri given");
		}

		$params = array(
			"response_type" => "code",
			"client_id" => $this->client_id,
			"redirect_uri" => $this->redirect_uri,
			"scope" => $this->scope,
			"locale" => $this->locale,
		);

		header("Location: " . $this->auth_url . $this->prefix . "/register/?" . http_build_query($params));
		die();
	}

	/**
	 * getLinkToSocialNetwork function.
	 *
	 * @access public
	 * @param string $name (default: "")
	 * @return void
	 */
	public function getLinkToSocialNetwork($name = "") {
		$supported = array("facebook", "google", "linkedin", "twitter");
		if ($name == "" || !in_array($name, $supported)) {
			throw new \NI\Oauth\Exception("Social network " . $name . " not supported");
		}

		if ($this->client_id == "" || $this->redirect_uri == "") {
			throw new \NI\Oauth\Exception("No client id or redirect uri given");
		}

		$params = array(
			"response_type" => "code",
			"client_id" => $this->client_id,
			"redirect_uri" => $this->redirect_uri,
			"scope" => $this->scope,
			"locale" => $this->locale,
		);

		return $this->auth_url . $this->prefix . "/connect/" . $name . "?" . http_build_query($params);
	}

	/**
	 * getToken function.
	 *
	 * @access public
	 * @return object
	 */
	public function getToken() {
		if ($this->client_id == "" || $this->client_secret == "") {
			throw new \NI\Oauth\Exception("No client id or client secret");
		}

		$params = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->client_id,
			"client_secret" => $this->client_secret,
			"code" => $_GET['code'],
			"scope" => $this->scope,
			"redirect_uri" => $this->redirect_uri,
		);

		$token = $this->post("/token", $params, true);

		if ($this->ni && $token->isSuccess()) {

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
	public function get($uri, $data = array(), $auth = false) {
		return $this->call($uri, "GET", $data, $auth);
	}

	/**
	 * post function.
	 *
	 * @access public
	 * @param string $uri
	 * @param array $data
	 * @return \NI\Api\Response
	 */
	public function post($uri, $data, $auth = false) {

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
	public function put($uri, $data) {
		if (is_array($data)) {
			$data = http_build_query($data);
		}

		return $this->call($uri, "PUT", $data);
	}

	/**
	 * delete function.
	 *
	 * @access public
	 * @param string $uri
	 * @return \NI\Api\Response
	 */
	public function delete($uri, $auth = false) {
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
	private function call($resource, $method, $data = array(), $auth = false) {
		$headers = array('Expect:');
		if ($auth) {
			$url = $this->auth_url . $this->prefix . $resource;
		} else {
			$url = $this->base_url . $resource;
		}

		if (\NI::$token) {
			$headers[] = 'Authorization: ' . $this->header_name . " " . \NI::$token;
		}

		$skipContentType = false;

		$jsonType = "application/json";
		$jsonCall = false;
		if (substr($_SERVER['CONTENT_TYPE'], 0, strlen($jsonType)) === $jsonType) {
			$jsonCall = true;
		}

		if ($this->header_name === "Bearer") {

			if (is_array($data) && isset($data['file'])) {
				$classType = gettype($data['file']);
				if ($classType === "object") {
					$className = get_class($data['file']);
					if ($className === "CURLFile") {
						$skipContentType = true;
					}
				}
			}

			if (!$skipContentType && $jsonCall === false) {
				$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			}
		}

		// Don't build query on json
		if ($jsonCall === true) {
			$skipContentType = true;
			$headers[] = 'Content-Type: application/json';
		}

		if ($this->api_key) {
			$headers[] = 'X-API-Key: ' . $this->api_key;
		}
		
		$headers[] = "X-ORIGIN-HOSTNAME: ".$_SERVER['HTTP_HOST'];

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		switch ($method) {
			case "GET":

				break;

			case "POST":
				if ($skipContentType) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				} else {
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				}
				break;

			case "PUT":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;

			case "DELETE":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
		}

		$response = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$this->status = $http_status;
		/*
		if($e === false) {
		throw new \NI\Api\Exception();
		}
		 */

		if (!curl_errno($ch)) {
			$info = curl_getinfo($ch);
			$content_type = $info['content_type'];
		} else {
			$content_type = 'application/json; charset=utf-8';
		}

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$e = substr($response, $header_size);
		$contentDisposition = preg_match('/Content-Disposition: .*filename=[\'\"]([^\'\"]+)/', $header, $matches);

		curl_close($ch);

		$jsonContentType = "application/json";
		if (substr(strtolower($content_type), 0, strlen($jsonContentType)) === $jsonContentType) {
			$this->format = true;
		}

		if ($e && $this->format) {
			$e = json_decode($e);
			$this->format = false;
		}

		if ($contentDisposition && isset($matches[1])) {
			header('Content-Disposition: attachment; filename="' . $matches[1] . '"');
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
	public function getNI() {
		return $this->ni;
	}

	/**
	 * setToken function.
	 *
	 * @access public
	 * @param string $token
	 * @return void
	 */
	public function setToken($token) {
		\NI::$token = $token;
	}

}
