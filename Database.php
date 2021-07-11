<?php

define('DATA', '../data');

class Database {

	private $sqlite;

	public function __construct($user, $pass) {
		#unlink(DATA . '/' . $user . '.db');
		$filename = self::getFilename($user, $pass);
		if (file_exists($filename)) {
			$this->sqlite = new SQLite3($filename, SQLITE3_OPEN_READWRITE);
		} else {
			throw new Exception("Database not found");
		}
		
	}

	public function __destruct() {
		$this->sqlite->close();
	}

	private static function getFilename($user, $pass) {
		return DATA . '/' . hash('sha512', $user . $pass) . '.db';
	}

	public static function initDB($user, $pass) {
		$filename = self::getFilename($user, $pass);

		if (!file_exists($filename)) {
			$foo = new SQLite3($filename);
			$foo->exec('CREATE TABLE shoppingList (id integer primary key autoincrement not null, text varchar)');
			$foo->exec('CREATE TABLE shoppingItem (id integer primary key autoincrement not null, shoppingListId integer, storeItemId integer, amount integer, unit varchar)');
			$foo->exec('CREATE TABLE storeItem (id integer primary key autoincrement not null, text varchar, unit varchar, sortKey integer)');
			$foo->exec('CREATE INDEX ShoppingItem_StoreItemId on shoppingItem(storeItemId)');
			$foo->exec('CREATE INDEX ShoppingItem_ShoppingListId on shoppingItem(shoppingListId)');
			$foo->exec('CREATE INDEX StoreItem_SortKey on storeItem(sortKey)');
			$foo->exec('CREATE INDEX StoreItem_Text on storeItem(text)');

			$foo->close();
		} else {
			throw new Exception('Datebase already exists');
		}
		
	}

	public static function isDatabaseAvailable($user, $pass) {
		$filename = self::getFilename($user, $pass);

		return file_exists($filename);
	}

	public function getShoppingLists() {
		$res = $this->sqlite->query('SELECT * FROM shoppingList');
		return $this->getAll($res);
	}

	public function getShoppingList($id) {
		$sl = $this->sqlite->querySingle('SELECT * FROM shoppingList WHERE id = ' . SQLite3::escapeString($id), true);
		$sl['items'] = $this->getShoppingItems($id, false);
		return $sl;
	}

	public function getShoppingItems($id, $bySortKey) {
		$sis = $this->getAll($this->sqlite->query('SELECT * FROM shoppingItem WHERE shoppingListId = ' . SQLite3::escapeString($id)));
		foreach ($sis as &$shoppingItem) {
			$shoppingItem['storeItem'] = $this->sqlite->querySingle('SELECT * FROM storeItem WHERE id = ' . SQLite3::escapeString($shoppingItem['storeItemId']), true);
		}
		if ($bySortKey) {
			usort($sis, function($si1, $si2) {
				if ($si1['storeItem']['sortKey'] < $si2['storeItem']['sortKey']) {
					return -1;
				} else {
					return 1;
				}
			});
		}
		return $sis;
	}

	public function getStoreItems($text, $limit) {
		$sql = 'SELECT * FROM storeItem';

		if ($text !== null) {
			$sql .= " WHERE text LIKE '%" . SQLite3::escapeString($text) . "%'";
		}

		if ($limit !== null) {
			$sql .= " LIMIT " . SQLite3::escapeString($limit);
		}

		return $this->getAll($this->sqlite->query($sql));
	}

	public function addShoppingList($shoppingList) {
		$this->sqlite->exec("INSERT INTO shoppingList (text) VALUES ('" . SQLite3::escapeString($shoppingList->text) . "')");
		return $this->sqlite->lastInsertRowID();
	}

	public function addShoppingItem($id, $shoppingItem) {
		$this->sqlite->exec("INSERT INTO shoppingItem (shoppingListId, storeItemId, amount, unit) VALUES (" . SQLite3::escapeString($id) . ", " . SQLite3::escapeString($shoppingItem->storeItemId) . ", " . SQLite3::escapeString($shoppingItem->amount) . ", '" . SQLite3::escapeString($shoppingItem->unit) . "')");
		return $this->sqlite->lastInsertRowID();
	}

	public function addStoreItem($storeItem) {
		$this->sqlite->exec("INSERT INTO storeItem (text, unit) VALUES ('" . SQLite3::escapeString($storeItem->text) . "', '" . SQLite3::escapeString($storeItem->unit) . "')");
		return $this->sqlite->lastInsertRowID();
	}

	public function removeShoppingList($id) {
		$this->sqlite->exec("DELETE FROM shoppingItem WHERE shoppingListId = " . SQLite3::escapeString($id));
		$this->sqlite->exec("DELETE FROM shoppingList WHERE id = " . SQLite3::escapeString($id));
	}

	public function removeShoppingItem($id) {
		$this->sqlite->exec("DELETE FROM shoppingItem WHERE id = " . SQLite3::escapeString($id));
	}

	public function updateStoreItem($obj) {
		$this->sqlite->exec("UPDATE storeItem SET text = '" . SQLite3::escapeString($obj->text) . "', unit = '" . SQLite3::escapeString($obj->unit) . "', sortKey = " . SQLite3::escapeString($obj->sortKey) . " WHERE id = " . SQLite3::escapeString($obj->id));
	}

	public function recalculateStoreItemSort() {
		$sis = $this->getAll($this->sqlite->query("SELECT * FROM storeItem ORDER BY sortKey, text"), SQLITE3_NUM);
		for ($i = 0; $i < count($sis); $i++) {
			$si = $sis[$i];
			$si['sortKey'] = $i + 1;
			$this->sqlite->exec("UPDATE storeItem SET sortKey = " . $si['sortKey'] . " WHERE id = " . $si['id']);
		}
	}

	private function getAll(SQLite3Result $res, $type = SQLITE3_ASSOC) {
		$ret = [];
		while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
			$ret[] = $row;
		}

		return $ret;
	}
}
