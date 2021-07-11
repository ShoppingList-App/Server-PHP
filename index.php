<?php

require_once('config.php');

function jsonOut($foo) {
	header('Content-Type: application/json');
	echo json_encode($foo);
}

function startsWith( $haystack, $needle ) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}

function httpAuthenticateRequest() {
	header('WWW-Authenticate: Basic realm="Shopping List"');
	header('HTTP/1.0 401 Unauthorized');
	die('Need to authenticate');
}

function gotoBaseURL() {
	header('Location: ' . BASEURL);
	die();
}

if ($_SERVER['REQUEST_SCHEME'] !== 'https' && $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
	die('PLEASE USE HTTPS');
}

require_once('Database.php');

$authed = false;

if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	$user = $_SERVER['PHP_AUTH_USER'];
	$pass = $_SERVER['PHP_AUTH_PW'];
	$authed = Database::isDatabaseAvailable($user, $pass);
}

$service = '/' . $_REQUEST['path'];
$method = $_SERVER['REQUEST_METHOD'];

if (startsWith($service, '/v1/')) {
	// authenticated only area
	// ... except for lovely CORS OPTIONS requests ...
	if ($method === 'OPTIONS') {
		die('I BREAK FOR CORS');
	}

	if ($authed) {
		try {
			$db = new Database($user, $pass);
			require_once('serviceV1.php');
		} catch (Exception $e) {
			die($e->getMessage());
		}
	} else {
		httpAuthenticateRequest();
	}
} else {
	// maybe unauthenticated area
	if ($service === '/logout') {
		if ($authed) {
			httpAuthenticateRequest();
		} else {
			gotoBaseURL();
		}
	} elseif ($service === '/login') {
		if (!$authed) {
			httpAuthenticateRequest();
		} else {
			gotoBaseURL();
		}
	}

	die("https://swagger.devloop.de/ui/?url=https://raw.githubusercontent.com/ShoppingList-App/ShoppingList/master/ShoppingListApp/ShoppingListApp/Services/REST/openapi.yaml");
}
