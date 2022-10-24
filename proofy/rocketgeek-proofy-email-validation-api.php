<?php
/**
 * Proofy Email Verification API wrapper.
 *
 * Proofy API:         https://proofy.io/using-api/
 * WordPress HTTP API: https://developer.wordpress.org/plugins/http-api/
 * This class:         https://github.com/rocketgeek/rocketgeek-email-verification/proofy/
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it 
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Proofy_Email_Verification_API
 * @version    1.0.0
 * @author     Chad Butler <https://butlerblog.com>
 * @author     RocketGeek <https://rocketgeek.com>
 * @copyright  Copyright (c) 2022 Chad Butler
 * @license    Apache-2.0
 *
 * Copyright [2022] Chad Butler, RocketGeek
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     https://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if ( ! class_exists( 'RocketGeek_Proofy_Email_Verification_API' ) ) :
class RocketGeek_Proofy_Email_Verification_API {

    /**
     * The Proofy user ID.
     * 
     * @access private
     * @var    string
     */
    private $aid;

	/**
	 * The Proofy API key.
	 *
	 * @access private
	 * @var    string
	 */
	private $api_key;

	/**
	 * Default Proofy API endpoint.
	 *
	 * @access private
	 * @var    string
	 */
	private $api_url = 'https://api.proofy.io/';

	/**
	 * SSL setting.
	 *
	 * @access public
	 * @var    boolean
	 */
	public $verify_ssl = true;

	/**
	 * Successful request indicator.
	 *
	 * @access private
	 * @var    boolean
	 */
	private $request_successful = false;

	/**
	 * Most recent error container.
	 *
	 * @access private
	 * @var    string
	 */
	private $last_error = '';

	/**
	 * Most recent response container.
	 *
	 * @access private
	 * @var    array
	 */
	private $last_response = array( 'headers' => null, 'body' => null );

	/**
	 * Most recent request container.
	 *
	 * @access private
	 * @var    array
	 */
	private $last_request = array();
	
	/**
	 * Create a new instance.
	 *
	 * @param  string  $api_key  Your Proofy API key.
	 */
	public function __construct( $aid, $api_key ) {
        $this->aid     = $aid;
		$this->api_key = $api_key;
	}
	
	/**
	 * Return the current endpoint.
	 *
	 * @return  string         The URL to the API endpoing.
	 */
	public function get_api_endpoint( $endpoint ) {
		return $this->api_url . $endpoint;
	}

	/**
	 * Was the last request successful?
	 *
	 * @return  bool  True for success, false for failure
	 */
	public function success() {
		return $this->request_successful;
	}

	/**
	 * Get the last error.
	 *
	 * Get last error returned by either the network transport, or by the API.
	 * If something didn't work, this should contain the string describing the problem.
	 *
	 * @return  array|false  describing the error
	 */
	public function get_last_error() {
		return $this->last_error = ( null != $this->last_error ) ? $this->last_error : false;
	}

	/**
	 * Get an array containing the HTTP headers and the body of the API response.
	 *
	 * @return  array  Assoc array with keys 'headers' and 'body'
	 */
	public function get_last_response() {
		return $this->last_response;
	}

	/**
	 * Get an array containing the HTTP headers and the body of the API request.
	 *
	 * @return  array  Assoc array
	 */
	public function get_last_request() {
		return $this->last_request;
	}

	/**
	 * Make an HTTP GET request.
	 *
	 * @param   array       $args          {
	 *     Assoc array of parameters to be passed.
	 * 
	 *     @type string $email
	 *     @type string $cid
	 * }
	 * @param   array       $request_args  Assoc array of arguments to override GET request defaults ('headers','body','timeout','sslverify','method') (optional).
	 * @param   int         $timeout       Timeout limit for request in seconds. (optional)
	 * @return  array|bool                 Assoc array of API response, decoded from JSON.
	 */
	public function get( $args = array(), $request_args = array(), $timeout = 10000 ) {
		return $this->make_request( 'get', $args, $request_args, $timeout );
	}

	/**
	 * Make an HTTP POST request.
	 *
	 * @param   array       $args  
	 * @param   array       $request_args  Assoc array of arguments to override GET request defaults ('headers','body','timeout','sslverify','method') (optional).
	 * @param   int         $timeout       Timeout limit for request in seconds. (optional)
	 * @return  array|bool                 Assoc array of API response, decoded from JSON.
	 */
	public function post( $args = array(), $request_args = array(), $timeout = 10000 ) {
		return $this->make_request( 'post', $args, $request_args, $timeout );
	}

	/**
	 * A public method to verify an email (2-step process).
	 * 
	 * @param  string  $email  The email address to verify
	 * @return array           Assoc array of API response, decoded from JSON.
	 */
	public function verify( $email ) {
		$args = array(
			'endpoint' => 'verifyaddr',
			'email' => $email,
		);
		
		$result = $this->get( $args );
		
		$args = array(
			'endpoint' => 'getresult',
			'cid'      => $result['cid']
		);
		
		return $this->get( $args );
	}

	/**
	 * Performs the underlying HTTP request.
	 *
	 * @param  string      $http_verb The HTTP verb to use: get|post.
	 * @param  array       $args          Assoc array of parameters to be passed.
	 * @param  array       $request_args  Assoc array of arguments to override HTTP request defaults.
	 * @param  integer     $timeout       Timeout limit for request in seconds.
	 * @return array|bool                 Assoc array of decoded result.
	 */
	private function make_request( $http_verb, $args, $request_args, $timeout ) {

		// Get enpoint out of $args
        $endpoint = $args['endpoint'];
        unset( $args['endpoint'] );

        // If this is a post action, get post data out of args.
        $formatted_post_data = false;
        if ( 'post' == $http_verb ) {
            $post_data = $args['post_data'];
            unset( $args['post_data'] );
            $formatted_post_data = json_encode( $post_data );
        }

		$args['aid'] = ( ! isset( $args['aid'] ) ) ? $this->aid     : $args['aid'];
		$args['key'] = ( ! isset( $args['key'] ) ) ? $this->api_key : $args['key'];

		/**
		 * Filter the default HTTP request args.
		 * 
		 * @param  array
		 */
	    $default_request_args = apply_filters( 'rktgk_proofy_default_request_args', array(
	    	'headers' => array(
			    'Accept'       => 'application/json',
			    'Content-Type' => 'application/json',
				'User-Agent'   => 'RocketGeek/Proofy-API/1.0 (github.com/rocketgeek/rocketgeek-email-verification/proofy/)',
		    ),
			'body'       => '',
		    'timeout'    => $timeout,
		    'sslverify'  => $this->verify_ssl,
		    'method'     => strtoupper( $http_verb )
	    ), $args );

		// Merge request_args with defaults.
		$request_args = $this->parse_args( $request_args, $default_request_args );

		// Assemble query endpoint URL.
		$url = add_query_arg( $args, $this->get_api_endpoint( $endpoint ) );

		// Set up last_request var container.
		$this->last_request = array(
			'url'     => $url,
			'headers' => $request_args['headers'],
			'body'    => $request_args['body'],
			'timeout' => $timeout,
		);

		// Do HTTP request.
        if ( 'get' == $http_verb ) {
            $wp_response = wp_remote_get( $url, $request_args );
        } else {
            $wp_response = wp_remote_post( $url, $formatted_post_data );
        }

	    if ( is_wp_error( $wp_response ) ) {
		    $this->last_error = $wp_response->get_error_code() . ': ' . $wp_response->get_error_message();
	    }

        $this->last_response = array(
            'body'    => wp_remote_retrieve_body( $wp_response ),
            'headers' => wp_remote_retrieve_headers( $wp_response )
        );

        $formatted_response = $this->format_response( $wp_response );

        $this->deterine_success( $wp_response, $formatted_response );

        return $formatted_response;
		
	}
	
    /**
     * Decode the response and format any error messages for debugging.
	 * 
     * @param  array        $response The response from the curl request
     * @return array|false            The JSON decoded into an array
     */
    private function format_response( $response ) {
		return ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) ? json_decode( $response['body'], true ) : false;
	}

    /**
     * Check if the response was successful or a failure. If it failed, store the error.
	 * 
     * @param  array        $response            The response from the curl request
     * @param  array|false  $formatted_response  The response body payload from the curl request
     * @return bool                             If the request was successful
     */
    private function deterine_success( $response, $formatted_response ) {
		$status = $this->find_http_status( $response, $formatted_response );

		if ( $status >= 200 && $status <= 299 ) {
			$this->request_successful = true;
			return true;
		}

		if ( isset( $formatted_response['detail'] ) ) {
			$this->last_error = sprintf( '%d: %s', $formatted_response['status'], $formatted_response['detail'] );
			return false;
		}

		$this->last_error = 'Unknown error, call get_last_response() to find out what happened.';
		return false;
	}

    /**
     * Find the HTTP status code from the headers or API response body.
	 * 
     * @param   array        $response            The response from the curl request
     * @param   array|false  $formatted_response  The response body payload from the curl request
     * @return  int                              HTTP status code
     */
    private function find_http_status( $response, $formatted_response ) {
		$status = wp_remote_retrieve_response_code( $response );
		if ( is_wp_error( $response ) ) {
			if ( empty( $status ) ) {
				return 418;
			}
			return $status;
		}

		if ( ! empty( $status ) ) {
			return  $status;
		}
		elseif ( ! empty( $response['body'] ) && isset( $formatted_response['status'] ) ) {
			return (int)$formatted_response['status'];
		}

		return 418;
	}

	/**
	 * Utility to merge arrays because wp_parse_args() does not work
	 * recursively and cannot be used on multi-dimensional arrays. This
	 * function will operate in the same manner, but works recursively.
	 * 
	 * @link: https://mekshq.com/recursive-wp-parse-args-wordpress-function/
	 * 
	 * @param  $a  string|array|object  Value to merge with $defaults.
	 * @param  $b  array                Serves as $default values.
	 */
	function parse_args( &$a, $b ) {
		$a = (array) $a;
		$b = (array) $b;
		$result = $b;
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $result[ $k ] ) ) {
				$result[ $k ] = $this->parse_args( $v, $result[ $k ] );
			} else {
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
}
endif;
