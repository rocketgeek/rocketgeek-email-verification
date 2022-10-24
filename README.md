# Email verification API

Super-simple, minimum abstraction email verification API wrapper, in PHP, for WordPress applications.

# How to use the object class

This simple wrapper allows you to query the Proofy.io API to receive a checked result.

In your application, set up the object as a global so you can use it anywhere in your plugin/theme/app.

The only things you need when you initialize the object class are your user ID and API key from Proofy.io.

```php
add_action( 'init', function() {
	// Your user ID from Proofy
	$aid = 12345;

	// Your API key from Proofy
	$api_key = 'some_random_api_key';

	// Include the api wrapper object class.
	require 'proofy/class-rocketgeek-proofy-email-validation.php';

	// Initiate the object class with your api key.
	global $proofy;
	$proofy = new RocketGeek_Proofy_Email_Verification_API( $aid, $api_key );
});
```

Now you can verify an email anywhere:

```php
global $proofy;

$result = $proofy->veryify( "email@example.com" );
```

The results will be an array.

```php
Array(
	[cid] => 7230099
	[checked] => 1
	[result] => Array (
		[0] => Array (
			[email] => email@example.com
			[status] => 1
			[statusName] => deliverable
			[syntax] => 1
			[mx] => 1
			[role] => 0
			[free] => 0
			[disposable] => 0
		)
	)
)
```

From the result, you can check the status.  Proofy's status codes are:
* 1 – deliverable
* 2 – risky
* 3 – undeliverable
* 4 – unknown

So extending from the example above, you can do something like:

```php
if ( 1 == $result['result'][0]['status'] ) {

	echo $result['result'][0]['email'] . " is deliverable";
	
}
```
