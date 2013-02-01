<?php
/**
 * @package VKAPI
 * @class   VKAPI
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    21 Nov 2012
 **/

class VKAPI
{
	const AUTH_URL  = 'https://oauth.vk.com/authorize';
	const API_URL   = 'https://api.vk.com/method';
	const CACHE_TTL = -1;
	const CACHE_DIR = '../cache/vk-api';
	const APP_ID    = '3302828';

	private static $instance = null;

	private $clientID = null;
	private $token    = null;

	private function __construct() {
		if( isset( $_SESSION['access_token'] ) ) {
			$this->token = $_SESSION['access_token'];
		}
		$this->setClientID( self::APP_ID );
	}

	public static function getInstance() {
		if( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function setClientID( $id ) {
		$this->clientID = $id;
	}

	public function setToken( $token ) {
		$this->token = $token;
	}


	public function getAuthURL( array $settings = null ) {
		$settings = array_merge(
			array(
				'scope'         => 'offline,wall,friends,audio,video,photos,groups',
				'redirect_uri'  => 'http://oauth.vk.com/blank.html',
				'display'       => 'page',
				'response_type' => 'token'
			),
			(array) $settings
		);
		$settings['client_id'] = $this->clientID;

		return self::AUTH_URL . '?' . http_build_query( $settings );
	}

	public function request( $method, array $params = null, $cacheTTL = null ) {
		$url = self::API_URL . '/' . $method . '.json?';
		if(
			is_array( $params )
			&& count( $params ) > 0
		) {
			$url .= http_build_query( $params ) . '&';
		}
		$filename = self::CACHE_DIR . '/' . md5( $url );

		$url .= 'access_token=' . $this->token;

		$cacheTTL = ( $cacheTTL === null ) ? self::CACHE_TTL : $cacheTTL;
		if(
			file_exists( $filename ) == false
			|| ( filemtime( $filename ) + $cacheTTL ) < time()
		) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_URL, $url );
			$data = curl_exec( $ch );
			curl_close( $ch );

			if( $data === false ) {
				throw new Exception( curl_error( $ch ) );
			} else {
				if( $cacheTTL > 0 ) {
					file_put_contents( $filename, $data );
				}

				return json_decode( $data, true );
			}
		}

		return json_decode( file_get_contents( $filename ), true );
	}
}
?>
