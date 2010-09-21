<?php

require_once("HTTP/Request.php");
require_once("HTTP/Exception.php");

class OAuth_SimpleRequest extends HTTP_Request {
	private $providerUrl;
	private $parameters = array();
	
	const ERR_BAD_REQUEST = 400;
	const ERR_UNAUTHORIZED = 401;
	
	public function __construct($providerUrl, $consumerKey, $consumerSecret, $tokenSecret = null) {
		parent::__construct($providerUrl);
		$this->providerUrl = $providerUrl;
		$this->setParameter('oauth_consumer_key', $consumerKey);
		$signature = $consumerSecret . "&";
		if (!empty($tokenSecret))
			$signature .= $tokenSecret;
		$this->setParameter("oauth_signature", $signature);
		$this->setParameter("oauth_signature_method", "PLAINTEXT");
		$this->setParameter("oauth_timestamp", mktime());
		$this->setParameter("oauth_nonce", md5(rand(10, 10)));
		$this->setParameter("oauth_version", "1.0");
	}
	
	public function setParameters(array $params) {
		$this->parameters = $params;
	}
	
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}
	
	public function getParameters() {
		return $this->parameters;
	}
	
	public function encode() {
		$encoded = $this->providerUrl;
		$q = "";
		foreach ($this->parameters as $param => $value) {
			$q .= $param . "=" . rawurlencode($value) . "&";
		}
		$q = rtrim($q, "&");
		if (! strpos($this->providerUrl, "?")) 
			$encoded .= "?";
		else
			$encoded .= "&";
		$encoded .= $q;
		return $encoded;
	}
	
	public function getResponseStruct() {
		$response = parent::getResponseBody();
		if (empty($response))
			return null;
		$params = explode("&", $response);
		$response = array();
		foreach ($params as $param) {
			$pairs = explode("=", $param);
			if (!empty($pairs)) {
				$response[$pairs[0]] = (count($pairs)) ? implode("=", array_slice($pairs, 1)) : null;
			}
		}
		return $response;
	}
	
	public function sendRequest() {
		$this->setUrl($this->encode());
		$ok = parent::sendRequest();
		if (PEAR::isError($ok)) {
			throw new HTTP_Exception("Service is unavailable");
		}
		if (intval($this->getResponseCode()) == self::ERR_BAD_REQUEST) {
			throw new HTTP_Exception("Bad request", array($this->getResponseReason()), $this->getResponseCode());
		} else if (intval($this->getResponseCode()) == self::ERR_UNAUTHORIZED) {
			throw new HTTP_Exception("Unauthorized request", array($this->getResponseReason()), $this->getResponseCode());
		} else if (intval($this->getResponseCode() != 200)) {
			throw new HTTP_Exception("Unknown error", array($this->getResponseReason()), $this->getResponseCode());
		}
	}
}

?>