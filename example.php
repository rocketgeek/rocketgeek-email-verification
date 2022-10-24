<?php
/**
 * An example of adding Proofy.io's email validation to WordPress
 */

add_action( 'init', function() {
	
	$aid = 12345;                 // Your Proofy user ID
	$api_key = "ASDF896lllkj436"; // Your Proofy API key
	
	// Include the api wrapper class.
	require 'proofy/rocketgeek-proofy-email-validation-api.php';
	
	global $proofy;
	
	$proofy = new RocketGeek_Proofy_Email_Verification_API( $aid, $api_key );
	
	// Not a real world example - just a test run to show checking an email and the results:
	
	$email_to_check = "email@example.com";
	
	$result = $proofy->verify( $email_to_check );
	
	echo '<pre>'; print_r( $result ); echo '</pre>';
	exit();
});
