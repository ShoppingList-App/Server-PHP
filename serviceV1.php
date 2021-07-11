<?php

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
		$barcode = isset($_REQUEST['barcode'])?$_REQUEST['barcode']:null;
		$limit = isset($_REQUEST['limit'])?$_REQUEST['limit']:null;
		jsonOut($db->getStoreitems($text, $barcode, $limit));
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
	die('unknown service request');
}
