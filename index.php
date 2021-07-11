<?php

if ($_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
	die('HTTPS ONLY');
}

require_once('Database.php');

$authed = false;

if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER)) {
	$user = $_SERVER['PHP_AUTH_USER'];
	$pass = $_SERVER['PHP_AUTH_PW'];
	$authed = Database::isDatabaseAvailable($user, $pass);
}

if (!$authed) {
	header('WWW-Authenticate: Basic realm="Shopping List"');
	header('HTTP/1.0 401 Unauthorized');
	die('Need to authenticate');
} else {
	try {
		$db = new Database($user, $pass);
	} catch (Exception $e) {
		die($e->getMessage());
	}
}

$service = '/' . $_REQUEST['path'];
$method = $_SERVER['REQUEST_METHOD'];

function jsonOut($foo) {
	header('Content-Type: application/json');
	echo json_encode($foo);
}

if ($service === '/v1/shoppingLists') {
	if ($method === 'GET') {
		jsonOut($db->getShoppingLists());
	}
} elseif ($service === '/v1/shoppingList') {
	if ($method === 'GET') {
		$id = $_REQUEST['shoppingListId'];
		jsonOut($db->getShoppingList($id));
	} elseif ($method === 'PUT') {
		$data = file_get_contents('php://input');
		$obj = json_decode($data);
		$obj->id = $db->addShoppingList($obj);
		jsonOut($obj);
	} elseif ($method === 'DELETE') {
		$id = $_REQUEST['shoppingListId'];
		$db->removeShoppingList($id);
	}
} elseif ($service === '/v1/shoppingItems') {
	if ($method === 'GET') {
		$id = $_REQUEST['shoppingListId'];
		$bySortKey = (strtoupper($_REQUEST['bySortKey']) === strtoupper('true'));
		jsonOut($db->getShoppingItems($id, $bySortKey));
	}
} elseif ($service === '/v1/shoppingItem') {
	if ($method === 'PUT') {
		$id = $_REQUEST['shoppingListId'];
		$data = file_get_contents('php://input');
		$obj = json_decode($data);
		$obj->id = $db->addShoppingItem($id, $obj);
		jsonOut($obj);
	} elseif ($method === 'DELETE') {
		$id = $_REQUEST['shoppingItemId'];
		$db->removeShoppingItem($id);

	}
} elseif ($service === '/v1/storeItems') {
	if ($method === 'GET') {
		$text = isset($_REQUEST['text'])?$_REQUEST['text']:null;
		$limit = isset($_REQUEST['limit'])?$_REQUEST['limit']:null;
		jsonOut($db->getStoreitems($text, $limit));
	}
} elseif ($service === '/v1/storeItems/recalculateSortKey') {
	if ($method === 'GET') {
		$db->recalculateStoreItemSort();
	}
} elseif ($service === '/v1/storeItem') {
	if ($method === 'PUT') {
		$data = file_get_contents('php://input');
		$obj = json_decode($data);
		$obj->id = $db->addStoreItem($obj);
		jsonOut($obj);
	} elseif ($method === 'POST') {
		$data = file_get_contents('php://input');
		$obj = json_decode($data);
		$db->updateStoreItem($obj);
	}
} else {
?>
<html>
<body>
Hi <?= $user ?>
</body>
</html>
<?php
}
