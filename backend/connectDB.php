<?php
	
class connectDB {

	public function getConnection($database) {
		$connection = new MongoClient();
		$db = $connection->$database;

		$collection = $connection->database->$database;

		return $collection;
	}
}
?>