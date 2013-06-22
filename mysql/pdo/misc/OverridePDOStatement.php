<?php

include_once __DIR__ . '/../shard_connection.php';
include_once __DIR__ . '/../ExampleStatementClass.php';

try
{
	$conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("ExampleStatementClass"));

	for ($i = 0; $i < $shards_count; ++$i)
	{
		$sql = "SELECT * FROM tbl_posts /*+ shard_id(" . $i . ") */";

		echo "Executing: " . $sql . PHP_EOL;

		$req  = $conn->query($sql);

		foreach ($req->fetchAll() as $row)
		{
			var_dump($row);
		}
	}
}
catch(PDOException $E)
{
	exit('Failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$conn = null;

echo "Connection is closed." . PHP_EOL;